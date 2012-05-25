<?php

namespace RMS\PushNotificationsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @var \Symfony\Component\Config\Definition\Builder\NodeDefinition
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
                children()->
                    scalarNode("username")->isRequired()->cannotBeEmpty()->end()->
                    scalarNode("password")->isRequired()->cannotBeEmpty()->end()->
                    scalarNode("source")->defaultValue("")->end()->
                end()->
            end()
        ;
    }
}
