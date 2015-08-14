<?php

namespace DoS\QueueBundle\DependencyInjection;

use DoS\CernelBundle\Config\AbstractConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration extends AbstractConfiguration
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
            'classes' => array(
                'queue_message' => array(
                    'model' => 'DoS\QueueBundle\Model\QueueMessage',
                    'interface' => 'DoS\QueueBundle\Model\QueueMessageInterface',
                ),
            ),
            'validation_groups' => array(
                'queue_message' => array()
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