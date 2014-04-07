<?php

namespace RMS\PushNotificationsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Definition,
    Symfony\Component\DependencyInjection\Reference,
    RMS\PushNotificationsBundle\Device\Types,
    Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;

class AddHandlerPass implements CompilerPassInterface
{
    /**
     * Processes any handlers tagged accordingly
     *
     * @param  ContainerBuilder $container
     * @return void
     */
    public function process(ContainerBuilder $container)
    {
        $service = $container->getDefinition("rms_push_notifications");

        foreach ($container->findTaggedServiceIds("rms_push_notifications.handler") as $id => $attributes) {
            if (!isset($attributes[0]["osType"])) {
                throw new \LogicException("Handler {$id} requires an osType attribute");
            }

            $definition = $container->getDefinition($id);

            // Get reflection class for validate handler
            try {
                $class = $definition->getClass();

                // Class is parameter
                if (strpos($class, '%') === 0) {
                    $class = $container->getParameter(trim($class, '%'));
                }

                $refClass = new \ReflectionClass($class);
            } catch (\ReflectionClass $ref) {
                // Class not found or other reflection error
                throw new \RuntimeException(sprintf(
                    'Can\'t compile notification handler by service id "%s".',
                    $id
                ), 0, $ref);
            } catch (ParameterNotFoundException $paramNotFound) {
                // Parameter not found in service container
                throw new \RuntimeException(sprintf(
                    'Can\'t compile notification handler by service id "%s".',
                    $id
                ), 0, $paramNotFound);
            }

            // Required interface
            $requiredInterface = 'RMS\\PushNotificationsBundle\\Service\\OS\\OSNotificationServiceInterface';
            if (!$refClass->implementsInterface($requiredInterface)) {
                throw new \UnexpectedValueException(sprintf(
                   'Notification service "%s" by id "%s" must be implements "%s" interface!' ,
                   $refClass->getName(), $id, $requiredInterface
                ));
            }

            // Add handler to service notifications storage
            $service->addMethodCall("addHandler", array($attributes[0]["osType"], new Reference($id)));
        }
    }
}
