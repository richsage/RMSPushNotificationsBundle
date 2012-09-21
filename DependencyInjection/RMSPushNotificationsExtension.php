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
        if (isset($config["blackberry"])) {
            $this->setBlackberryConfig($config);
            $loader->load('blackberry.xml');
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
        $this->container->setParameter("rms_push_notifications.android.c2dm.enabled", true);

        // C2DM
        $username = $config["android"]["username"];
        $password = $config["android"]["password"];
        $source = $config["android"]["source"];
        if (isset($config["android"]["c2dm"])) {
            $username = $config["android"]["c2dm"]["username"];
            $password = $config["android"]["c2dm"]["password"];
            $source = $config["android"]["c2dm"]["source"];
        }
        $this->container->setParameter("rms_push_notifications.android.c2dm.username", $username);
        $this->container->setParameter("rms_push_notifications.android.c2dm.password", $password);
        $this->container->setParameter("rms_push_notifications.android.c2dm.source", $source);

        // GCM
        $this->container->setParameter("rms_push_notifications.android.gcm.enabled", isset($config["android"]["gcm"]));
        if (isset($config["android"]["gcm"])) {
            $this->container->setParameter("rms_push_notifications.android.gcm.api_key", $config["android"]["gcm"]["api_key"]);
        }
    }

    /**
     * Sets iOS config into container
     *
     * @param array $config
     */
    protected function setiOSConfig(array $config)
    {
        $this->container->setParameter("rms_push_notifications.ios.enabled", true);
        $this->container->setParameter("rms_push_notifications.ios.sandbox", $config["ios"]["sandbox"]);
        $this->container->setParameter("rms_push_notifications.ios.pem", $config["ios"]["pem"]);
        $this->container->setParameter("rms_push_notifications.ios.passphrase", $config["ios"]["passphrase"]);
    }

    /**
     * Sets Blackberry config into container
     *
     * @param array $config
     */
    protected function setBlackberryConfig(array $config)
    {
        $this->container->setParameter("rms_push_notifications.blackberry.enabled", true);
        $this->container->setParameter("rms_push_notifications.blackberry.evaluation", $config["blackberry"]["evaluation"]);
        $this->container->setParameter("rms_push_notifications.blackberry.app_id", $config["blackberry"]["app_id"]);
        $this->container->setParameter("rms_push_notifications.blackberry.password", $config["blackberry"]["password"]);
    }
}
