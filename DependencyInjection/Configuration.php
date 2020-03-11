<?php

namespace FrequenceWeb\Bundle\ContactBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 *
 * @author Yohan Giarelli <yohan@giarel.li>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('frequence_web_contact');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('send_mails')->defaultTrue()->end()
                ->scalarNode('to')->defaultValue(null)->end()
                ->scalarNode('from')->defaultValue('no-reply@example.com')->end()
                ->scalarNode('subject')->defaultValue('contact.message.new')
            ->end()
        ;

        return $treeBuilder;
    }
}
