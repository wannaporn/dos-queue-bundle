<?php

namespace DoS\QueueBundle\Provider;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use DoS\QueueBundle\Model\QueueMessageInterface;
use DoS\ResourceBundle\Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Uecode\Bundle\QPushBundle\Event\Events;
use Uecode\Bundle\QPushBundle\Event\MessageEvent;
use Uecode\Bundle\QPushBundle\Message\Message;
use Uecode\Bundle\QPushBundle\Provider\AbstractProvider;

class DoctrineProvider extends AbstractProvider
{
    /**
     * @var array
     */
    protected $queues = array();

    /**
     * @var EntityManager
     */
    protected $dispatcher;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var EntityRepository
     */
    protected $repisotory;

    /**
     * @var string
     */
    protected $dataClass;

    /**
     * @var array
     */
    protected $queueBuffers = [];

    /**
     * @var array
     */
    protected $queueOptions = array();

    public function __construct($name, array $options, $client, Cache $cache, Logger $logger)
    {
        $options = array_merge(array(
            'messages_to_receive' => 1,
            'fifo_receive' => true,
            'logging_enabled' => true,
        ), $options);

        $this->name = $name;
        $this->options = $options;
        $this->dispatcher = $client;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * @param string $dataClass
     */
    public function setRepositoryForClass($dataClass)
    {
        $this->dataClass = $dataClass;
        $this->repisotory = $this->dispatcher->getRepository($this->dataClass);
    }

    /**
     * @return QueueMessageInterface
     */
    public function create()
    {
        $this->log(200, "Queue has been created.");
        return $this->repisotory->createNew();
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getProvider()
    {
        return 'DoctrineORM';
    }

    /**
     * {@inheritdoc}
     */
    public function publish(array $message, array $options = [])
    {
        $publishStart = microtime(true);
        $queue = $this->create();
        $queue->setBody($message);
        $queue->setName($this->getNameWithPrefix());
        $this->dispatcher->persist($queue);
        $this->dispatcher->flush($queue);

        $this->queueBuffers[$queue->getId()] = $queue;

        $context = array(
            'message_id' => $queue->getId(),
            'publish_time' => microtime(true) - $publishStart
        );

        $this->log(200, "Message has been published.", $context);
    }

    /**
     * {@inheritdoc}
     */
    public function receive(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
        $results = $this->repisotory->createQueryBuilder('o')
            ->orderBy('o.id', $this->options['fifo_receive'] ? 'ASC' : 'DESC')
            ->where('o.name = :name')->setParameter('name', $this->getNameWithPrefix())
            ->setMaxResults($this->options['messages_to_receive'])
            ->getQuery()->getResult()
        ;

        if (!count($results)) {
            $this->log(200, "No messages found in queue.");

            return array();
        }

        $messages = array();

        /** @var QueueMessageInterface $message */
        foreach($results as $message)
        {
            $message->setReceivedAt(new \DateTime());

            $messages[] = new Message($message->getId(), $message->getBody(), array());

            $this->log(200, "Message has been received.", ['message_id' => $message->getId()]);

            $this->eventDispatcher->dispatch(
                Events::Message($this->name),
                new MessageEvent($this->name, new Message($message->getId(), $message->getBody(), array()))
            );

            // recived then delete.
            $message->setDeletedAt(new \DateTime());
            $this->dispatcher->persist($message);
        }

        $this->dispatcher->flush();

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        if ($message = $this->repisotory->find($id)) {
            $this->dispatcher->remove($message);
            $this->dispatcher->flush($message);
            $this->log(200, "Message deleted.", ['message_id' => $id]);
        } else {
            $this->log(400, "Queue did not exist", ['message_id' => $id]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function destroy()
    {
        // Catch `queue not found` exceptions, throw the rest.
        try {
            $this->repisotory->createQueryBuilder('o')
                ->select(null)
                ->delete('o')
                ->where('o.name = :name')
                ->setParameter('name', $this->getNameWithPrefix())
            ;
        } catch ( \Exception $e) {
            if (false !== strpos($e->getMessage(), "Queue not found")) {
                $this->log(400, "Queue did not exist");
            } else {
                throw $e;
            }
        }

        $key = $this->getNameWithPrefix();
        $this->cache->delete($key);

        $this->log(200, "Queue has been destroyed.");

        return true;
    }

    /**
     * Receive message.
     */
    public function onKernelTerminate()
    {
        foreach($this->queueBuffers as $queue) {
            $this->receive();
        }
    }
}
