<?php

namespace RMS\PushNotificationsBundle\Tests\DependencyInjection;

use RMS\PushNotificationsBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $config = $this->process(array());
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testAddingAndroidKeyRequiresValues()
    {
        $arr = array(
            array("android" => "~"),
        );
        $config = $this->process($arr);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testAndroidRequiresUsername()
    {
        $arr = array(
            array(
                "android" => array("c2dm" => array("password" => "foo"))
            ),
        );
        $config = $this->process($arr);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testAndroidRequiresPassword()
    {
        $arr = array(
            array(
                "android" => array("c2dm" => array("username" => "foo"))
            ),
        );
        $config = $this->process($arr);
    }

    public function testOldFullAndroid()
    {
        // NB - this is the deprecated version
        $arr = array(
            array(
                "android" => array("username" => "foo", "password" => "bar", "source" => "123")
            ),
        );
        $config = $this->process($arr);
        $this->assertArrayHasKey("android", $config);
        $this->assertEquals(5, $config["android"]["timeout"]);
        $this->assertEquals("foo", $config["android"]["username"]);
        $this->assertEquals("bar", $config["android"]["password"]);
        $this->assertEquals("123", $config["android"]["source"]);
    }

    public function testNewC2DMIsAllowedWithoutOldBits()
    {
        $arr = array(
            array(
                "android" => array(
                    "c2dm" => array(
                        "username" => "foo",
                        "password" => "bar",
                        "source" => "123"
                    )
                )
            ),
        );
        $config = $this->process($arr);
        $this->assertArrayHasKey("android", $config);
        $this->assertEquals(5, $config["android"]["timeout"]);
        $this->assertArrayHasKey("c2dm", $config["android"]);
        $this->assertEquals("foo", $config["android"]["c2dm"]["username"]);
        $this->assertEquals("bar", $config["android"]["c2dm"]["password"]);
        $this->assertEquals("123", $config["android"]["c2dm"]["source"]);
    }

    public function testGCMEmptyAPIKey()
    {
        $arr = array(
            array(
                "android" => array(
                    "gcm" => array(
                    )
                )
            ),
        );
        $config = $this->process($arr);
        $this->assertEmpty($config["android"]["gcm"]["api_key"]);
        $this->assertFalse($config["android"]["gcm"]["dry_run"]);
    }

    public function testGCMIsOK()
    {
        $arr = array(
            array(
                "android" => array(
                    "gcm" => array(
                        "api_key" => "foo",
                        "use_multi_curl" => true,
                        "dry_run" => false,
                    )
                )
            ),
        );
        $config = $this->process($arr);
        $this->assertEquals("foo", $config["android"]["gcm"]["api_key"]);
        $this->assertFalse($config["android"]["gcm"]["dry_run"]);

        $arr[0]["android"]["gcm"]["dry_run"] = true;
        $config = $this->process($arr);
        $this->assertTrue($config["android"]["gcm"]["dry_run"]);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testAddingiOsKeyRequiresValues()
    {
        $arr = array(
            array("ios" => "~"),
        );
        $config = $this->process($arr);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
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
        $this->assertEquals(60, $config["ios"]["timeout"]);
        $this->assertEquals(false, $config["ios"]["sandbox"]);
        $this->assertEquals("foo/bar.pem", $config["ios"]["pem"]);
        $this->assertEquals("foo", $config["ios"]["passphrase"]);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testAddingMacKeyRequiresValues()
    {
        $arr = array(
            array("mac" => "~"),
        );
        $config = $this->process($arr);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testMacRequiresPEM()
    {
        $arr = array(
            array(
                "mac" => array("pem" => "")
            ),
        );
        $config = $this->process($arr);
    }

    public function testFullMac()
    {
        $arr = array(
            array(
                "mac" => array("sandbox" => false, "pem" => "foo/bar.pem", "passphrase" => "foo")
            ),
        );
        $config = $this->process($arr);
        $this->assertArrayHasKey("mac", $config);
        $this->assertEquals(60, $config["mac"]["timeout"]);
        $this->assertEquals(false, $config["mac"]["sandbox"]);
        $this->assertEquals("foo/bar.pem", $config["mac"]["pem"]);
        $this->assertEquals("foo", $config["mac"]["passphrase"]);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testBlackberryRequiresAppID()
    {
        $arr = array(
            array(
                "blackberry" => array("password" => "foo")
            ),
        );
        $config = $this->process($arr);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testBlackberryRequiresPassword()
    {
        $arr = array(
            array(
                "blackberry" => array("app_id" => "foo")
            ),
        );
        $config = $this->process($arr);
    }

    public function testFullBlackberry()
    {
        $arr = array(
            array(
                "blackberry" => array("evaluation" => false, "app_id" => "foo", "password" => "bar")
            ),
        );
        $config = $this->process($arr);
        $this->assertArrayHasKey("blackberry", $config);
        $this->assertFalse($config["blackberry"]["evaluation"]);
        $this->assertEquals(5, $config["blackberry"]["timeout"]);
        $this->assertEquals("foo", $config["blackberry"]["app_id"]);
        $this->assertEquals("bar", $config["blackberry"]["password"]);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testAddingWindowsKeyRequiresValues()
    {
        $arr = array(
            array(
                "windowsphone" => "~"
            ),
        );
        $config = $this->process($arr);
    }

    public function testFullWindows()
    {
        $arr = array(
            array(
                "windowsphone" => array("timeout" => 5)
            ),
        );
        $config = $this->process($arr);
        $this->assertArrayHasKey("windowsphone", $config);
        $this->assertEquals(5, $config["windowsphone"]["timeout"]);
    }

    /**
     * Takes in an array of configuration values and returns the processed version
     *
     * @param  array $config
     * @return array
     */
    protected function process($config)
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $config);
    }
}
