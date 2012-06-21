<?php

namespace RMS\PushNotificationsBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader,
    Symfony\Component\Config\FileLocator;

class RMSPushNotificationsExtension extends Extension
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * Loads any resources/services we need
     *
     * @param array $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->container = $container;
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->setInitialParams();
        if (isset($config["android"])) {
            $this->setAndroidConfig($config);
            $loader->load('android.xml');
        }
        if (isset($config["ios"])) {
            $this->setiOSConfig($config);
            $loader->load('ios.xml');
        }
    }

    /**
     * Initial enabling
     */
    protected function setInitialParams()
    {
        $this->container->setParameter("rms_push_notifications.android.enabled", false);
        $this->container->setParameter("rms_push_notifications.ios.enabled", false);
    }

    /**
     * Sets Android config into container
     *
     * @param array $config
     */
    protected function setAndroidConfig(array $config)
    {
        $this->container->setParameter("rms_push_notifications.android.enabled", true);
        $this->container->setParameter("rms_push_notifications.android.username", $config["android"]["username"]);
        $this->container->setParameter("rms_push_notifications.android.password", $config["android"]["password"]);
        $this->container->setParameter("rms_push_notifications.android.source", $config["android"]["source"]);
    }

    /**
     * Sets iOS config into container
     *
     * @param array $config
     */
    protected function setiOSConfig(array $config)
    {
        $this->container->setParameter("rms_push_notifications.ios.enabled", true);
        $this->container->setParameter("rms_push_notifications.ios.pem", $config["ios"]["pem"]);
        $this->container->setParameter("rms_push_notifications.ios.passphrase", $config["ios"]["passphrase"]);
    }
}
