<?php

namespace DoS\QueueBundle\DependencyInjection;

use DoS\CernelBundle\Config\AbstractExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class DoSQueueExtension extends AbstractExtension implements PrependExtensionInterface
{
    protected $applicationName = 'dos';

    /**
     * {@inheritdoc}
     */
    protected function getBundleConfiguration()
    {
        return new Configuration();
    }

    /**
     * @inheritDoc
     */
    public function prepend(ContainerBuilder $container)
    {
        $config = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration($this->getBundleConfiguration(), $config);

        // TODO: duplicate new connection.
        $container->prependExtensionConfig('doctrine', array(
            'orm' => array(
                'entity_managers' => array(
                    $config['object_manager'] => array(
                        'connection' => $config['connection'],
                        'mappings' => array(
                            'DoSQueueBundle' => array(
                                'type' => 'yml',
                                'prefix' => 'DoS\QueueBundle\Model',
                                'dir' => '%kernel.root_dir%/../vendor/liverbool/dos-queue-bundle/Resources/config/doctrine/model',
                            )
                        ),
                        'filters' => array(
                            'softdeleteable' => array(
                                'class' => 'Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter',
                                'enabled' => true,
                            ),
                        ),
                    )
                )
            ),
        ));
    }
}
