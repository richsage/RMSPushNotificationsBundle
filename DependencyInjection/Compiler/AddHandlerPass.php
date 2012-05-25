<?php

namespace RMS\PushNotificationsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddHandlerPass implements CompilerPassInterface
{
    /**
     * Processes any handlers tagged accordingly
     *
     * @param ContainerBuilder $container
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $service = $container->getDefinition("rms_push_notifications");

        foreach ($container->findTaggedServiceIds("rms_push_notifications.handler") as $id => $attributes) {
            if (!isset($attributes[0]["osType"])) {
                throw new \LogicException("Handler {$id} requires an osType attribute");
            }
            $service->addMethodCall("addHandler", array($attributes[0]["osType"], new Reference($id)));
        }
    }
}
