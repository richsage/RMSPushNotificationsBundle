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
                        scalarNode("username")->isRequired()->end()->
                        scalarNode("password")->isRequired()->end()->
                        scalarNode("source")->defaultValue("")->end()->
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
        $this->root->
            children()->
                arrayNode("ios")->
                    children()->
                        booleanNode("sandbox")->defaultFalse()->end()->
                        scalarNode("pem")->isRequired()->cannotBeEmpty()->end()->
                        scalarNode("passphrase")->defaultValue("")->end()->
                    end()->
                end()->
            end()
        ;
    }
}
