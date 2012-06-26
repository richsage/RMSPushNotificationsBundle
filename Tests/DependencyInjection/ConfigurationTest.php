<?php

namespace RMS\PushNotificationsBundle\Tests\DependencyInjection;

use RMS\PushNotificationsBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $config = $this->process(array());
        $this->assertEmpty($config);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testAddingAndroidKeyRequiresValues()
    {
        $arr = array(
            array("android" => "~"),
        );
        $config = $this->process($arr);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testAndroidRequiresUsername()
    {
        $arr = array(
            array(
                "android" => array("password" => "foo")
            ),
        );
        $config = $this->process($arr);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testAndroidRequiresPassword()
    {
        $arr = array(
            array(
                "android" => array("username" => "foo")
            ),
        );
        $config = $this->process($arr);
    }

    public function testFullAndroid()
    {
        $arr = array(
            array(
                "android" => array("username" => "foo", "password" => "bar", "source" => "123")
            ),
        );
        $config = $this->process($arr);
        $this->assertArrayHasKey("android", $config);
        $this->assertEquals("foo", $config["android"]["username"]);
        $this->assertEquals("bar", $config["android"]["password"]);
        $this->assertEquals("123", $config["android"]["source"]);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testAddingiOsKeyRequiresValues()
    {
        $arr = array(
            array("ios" => "~"),
        );
        $config = $this->process($arr);
    }

    /**
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testiOSRequiresPEM()
    {
        $arr = array(
            array(
                "ios" => array("pem" => "")
            ),
        );
        $config = $this->process($arr);
    }

    public function testFulliOS()
    {
        $arr = array(
            array(
                "ios" => array("sandbox" => false, "pem" => "foo/bar.pem", "passphrase" => "foo")
            ),
        );
        $config = $this->process($arr);
        $this->assertArrayHasKey("ios", $config);
        $this->assertEquals(false, $config["ios"]["sandbox"]);
        $this->assertEquals("foo/bar.pem", $config["ios"]["pem"]);
        $this->assertEquals("foo", $config["ios"]["passphrase"]);
    }

    /**
     * Takes in an array of configuration values and returns the processed version
     *
     * @param array $config
     * @return array
     */
    protected function process($config)
    {
        $processor = new Processor();
        return $processor->processConfiguration(new Configuration(), $config);
    }
}
