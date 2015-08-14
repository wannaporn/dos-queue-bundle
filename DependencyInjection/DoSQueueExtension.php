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

        $container->prependExtensionConfig('doctrine', array(
            'orm' => array(
                'entity_managers' => array(
                    $config['object_manager'] => array(
                        'connection' => $config['connection'],
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
