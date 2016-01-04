<?php

namespace DoS\QueueBundle\DependencyInjection;

use DoS\ResourceBundle\DependencyInjection\AbstractResourceConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration extends AbstractResourceConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('dos_queue');

        $this->setDefaults($rootNode, array(
            'object_manager' => 'dos_queue',
            'resources' => array(
                'queue_message' => array(
                    'classes' => array(
                        'model' => 'DoS\QueueBundle\Model\QueueMessage',
                        'interface' => 'DoS\QueueBundle\Model\QueueMessageInterface',
                    ),
                ),
            ),
        ));

        $this->createConnectionNode($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $node
     *
     * @return ArrayNodeDefinition
     */
    protected function createConnectionNode(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('connection')
                ->cannotBeEmpty()
                ->defaultValue('default')
                ->end()
            ->end()
        ;

        return $node;
    }
}
