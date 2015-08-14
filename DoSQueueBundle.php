<?php

namespace DoS\QueueBundle;

use DoS\CernelBundle\Config\AbstractBundle;
use DoS\QueueBundle\DependencyInjection\Compiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoSQueueBundle extends AbstractBundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $builder)
    {
        parent::build($builder);

        $builder->addCompilerPass(new Compiler\CustomProviderPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getModelInterfaces()
    {
        return array(
            'DoS\QueueBundle\Model\QueueMessageInterface' => 'dos.model.queue_message.class'
        );
    }
}
