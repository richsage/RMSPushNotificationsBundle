<?php

namespace RMS\PushNotificationsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    protected $root;

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $this->root = $treeBuilder->root("rms_push_notifications");

        $this->addAndroid();
        $this->addiOS();
        $this->addMac();
        $this->addBlackberry();
        $this->addWindowsphone();

        return $treeBuilder;
    }

    /**
     * Android configuration
     */
    protected function addAndroid()
    {
        $this->root->
            children()->
                arrayNode("android")->
                    canBeUnset()->
                    children()->

                        // WARNING: These 3 fields as they are, outside of the c2dm array
                        // are deprecrated in favour of using the c2dm array configuration
                        // At present these will be overriden by anything supplied
                        // in the c2dm array
                        scalarNode("username")->defaultValue("")->end()->
                        scalarNode("password")->defaultValue("")->end()->
                        scalarNode("source")->defaultValue("")->end()->

                        arrayNode("c2dm")->
                            canBeUnset()->
                            children()->
                                scalarNode("username")->isRequired()->end()->
                                scalarNode("password")->isRequired()->end()->
                                scalarNode("source")->defaultValue("")->end()->
                            end()->
                        end()->
                        arrayNode("gcm")->
                            canBeUnset()->
                            children()->
                                scalarNode("api_key")->isRequired()->cannotBeEmpty()->end()->
                                booleanNode("use_multi_curl")->defaultValue(true)->end()->
                            end()->
                        end()->
                    end()->
                end()->
            end()
        ;
    }

    /**
     * iOS configuration
     */
    protected function addiOS()
    {
        $this->addApple("ios");
    }

    /**
     * Mac configuration
     */
    protected function addMac()
    {
        $this->addApple("mac");
    }

    /**
     * Generic Apple Configuration
     */
    private function addApple($os)
    {
        $config = $this->root->
            children()->
                arrayNode($os)->
                    prototype('array')->
                    children()->
                        booleanNode("sandbox")->defaultFalse()->end()->
                        scalarNode("pem")->isRequired()->cannotBeEmpty()->end()->
                        scalarNode("passphrase")->defaultValue("")->end()->
                        scalarNode('json_unescaped_unicode')->defaultFalse();
                        if (method_exists($config,'info')) {
                            $config = $config->info('PHP >= 5.4.0 and each messaged must be UTF-8 encoding');
                        }
                        $config->end()->
                    end()->
                end()->
            end()
        ;
    }

    /**
     * Blackberry configuration
     */
    protected function addBlackberry()
    {
        $this->root->
            children()->
                arrayNode("blackberry")->
                    children()->
                        booleanNode("evaluation")->defaultFalse()->end()->
                        scalarNode("app_id")->isRequired()->cannotBeEmpty()->end()->
                        scalarNode("password")->isRequired()->cannotBeEmpty()->end()->
                    end()->
                end()->
            end()
        ;
    }

    protected function addWindowsphone()
    {
        $this->root->
            children()->
                booleanNode('windowsphone')->defaultFalse()->end()->
            end()
        ;
    }
}
