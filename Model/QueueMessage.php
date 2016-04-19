<?php

namespace DoS\QueueBundle\Model;

use DoS\ResourceBundle\Model\TimestampableTrait;

class QueueMessage implements QueueMessageInterface
{
    use TimestampableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $body = array();

    /**
     * @var \DateTime
     */
    protected $receivedAt;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function setBody(array $body)
    {
        $this->body = $body;
    }

    /**
     * {@inheritdoc}
     */
    public function getReceivedAt()
    {
        return $this->receivedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setReceivedAt(\DateTime $receivedAt)
    {
        $this->receivedAt = $receivedAt;
    }
}
