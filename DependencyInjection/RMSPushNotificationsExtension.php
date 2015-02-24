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
     * @var string
     */
    protected $kernelRootDir;

    /**
     * Loads any resources/services we need
     *
     * @param  array                                                   $configs
     * @param  \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->container = $container;
        $this->kernelRootDir = $container->getParameterBag()->get("kernel.root_dir");

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->setInitialParams();
        if (isset($config["android"])) {
            $this->setAndroidConfig($config);
            $loader->load('android.xml');
        }
        if (!empty($config["ios"])) {
            $this->setiOSConfig($config);
            $loader->load('ios.xml');
        }
        if (!empty($config["mac"])) {
            $this->setMacConfig($config);
            $loader->load('mac.xml');
        }
        if (!empty($config["blackberry"])) {
            $this->setBlackberryConfig($config);
            $loader->load('blackberry.xml');
        }
        if (!empty($config['windowsphone'])) {
            $this->setWindowsphoneConfig($config);
            $loader->load('windowsphone.xml');
        }
    }

    /**
     * Initial enabling
     */
    protected function setInitialParams()
    {
        $this->container->setParameter("rms_push_notifications.android.enabled", false);
        $this->container->setParameter("rms_push_notifications.ios.enabled", false);
        $this->container->setParameter("rms_push_notifications.mac.enabled", false);
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
            $this->container->setParameter("rms_push_notifications.android.gcm.use_multi_curl", $config["android"]["gcm"]["use_multi_curl"]);
        }
    }

    /**
     * Sets iOS config into container
     *
     * @param array $config
     */
    protected function setiOSConfig(array $config)
    {
        $this->setAppleConfig($config, "ios");
    }

    /**
     * Sets Mac config into container
     *
     * @param array $config
     */
    protected function setMacConfig(array $config)
    {
        $this->setAppleConfig($config, "mac");
    }

    /**
     * Sets Apple config into container
     *
     * @param  array             $config
     * @param $os
     * @throws \RuntimeException
     * @throws \LogicException
     */
    protected function setAppleConfig(array $config, $os)
    {
        $supportedAppleOS = array("mac", "ios");
        //Check if the OS is supported
        if (!in_array($os, $supportedAppleOS, true)) {
            throw new \RuntimeException(sprintf('This Apple OS "%s" is not supported', $os));
        }

        foreach($config[$os] as $conf) {
            $pemPath = $conf["pem"];

            // PEM file is required
            if (realpath($pemPath)) {
                // Absolute path
                $conf["pem"] = $pemPath;
            } elseif (realpath($this->kernelRootDir.DIRECTORY_SEPARATOR.$pemPath) ) {
                // Relative path
                $conf["pem"] = $this->kernelRootDir.DIRECTORY_SEPARATOR.$pemPath;
            } else {
                // path isn't valid
                throw new \RuntimeException(sprintf('Pem file "%s" not found.', $pemPath));
            }
            if ($conf['json_unescaped_unicode']) {
                // Not support JSON_UNESCAPED_UNICODE option
                if (!version_compare(PHP_VERSION, '5.4.0', '>=')) {
                    throw new \LogicException(sprintf(
                        'Can\'t use JSON_UNESCAPED_UNICODE option. This option can use only PHP Version >= 5.4.0. Your version: %s',
                        PHP_VERSION
                    ));
                }
            }
        }

        $this->container->setParameter(sprintf('rms_push_notifications.%s', $os), $config[$os]);
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

    protected function setWindowsphoneConfig(array $config)
    {
        $this->container->setParameter("rms_push_notifications.windowsphone.enabled", true);
    }
}
