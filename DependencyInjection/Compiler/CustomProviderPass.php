<?php

namespace DoS\QueueBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CustomProviderPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasParameter('uecode_qpush.provider.custom')) {
            return;
        }

        $configs = $container->getExtensionConfig('uecode_qpush');

        foreach($configs[0]['queues'] as $queue => $value) {
            $definition = clone $container->getDefinition('dos.queue.provider.doctrine_orm');
            $definition->replaceArgument(0, $queue);
            $definition->replaceArgument(1, $value['options']);

            if ($container->hasDefinition('event_dispatcher')) {
                $definition->addMethodCall('setEventDispatcher', array(new Reference('event_dispatcher')));
            } else {
                $definition->addMethodCall('setEventDispatcher', array(new Reference('debug.event_dispatcher')));
            }

            $container->getDefinition('uecode_qpush.'. $queue)->replaceArgument(2, $definition);
            $container->setDefinition(sprintf('uecode_qpush.%s', $queue), $definition)
                ->addTag('monolog.logger', ['channel' => 'qpush'])
                ->addTag('kernel.event_listener', array(
                    'event' => "kernel.terminate",
                    'method' => "onKernelTerminate",
                    'priority' => 255
                ))
            ;
        }
    }
}
