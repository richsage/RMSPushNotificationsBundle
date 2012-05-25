<?php

namespace RMS\PushNotificationsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use RMS\PushNotificationsBundle\DependencyInjection\Compiler\AddHandlerPass;

class RMSPushNotificationsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddHandlerPass());
    }
}
