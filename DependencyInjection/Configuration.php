<?php
namespace CodeMade\WuiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('wui');
        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('wui');
        }

        $rootNode
            ->children()
                ->arrayNode('pdo')
                    ->beforeNormalization()
                        ->ifTrue(static function ($v) {
                            return is_array($v) && ! array_key_exists('connections', $v) && ! array_key_exists('connection', $v);
                        })
                        ->then(static function ($v) {
                            // Key that should not be rewritten to the connection config
                            $excludedKeys = ['connections' => true, 'default_connection' => true, 'types' => true, 'type' => true];
                            $connection   = [];
                            foreach ($v as $key => $value) {
                                if (isset($excludedKeys[$key])) {
                                    continue;
                                }
                                $connection[$key] = $v[$key];
                                unset($v[$key]);
                            }
                            $v['default_connection'] = isset($v['default_connection']) ? (string) $v['default_connection'] : 'default';
                            $v['connections']        = [$v['default_connection'] => $connection];
                            return $v;
                        })
                        ->end()
                        ->children()
                            ->scalarNode('default_connection')->end()
                            ->arrayNode('connections')
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                            ->beforeNormalization()
                            ->ifArray()
                            ->then(function ($connections) {
                                $normalized = [];
                                foreach ($connections as $name => $settings) {
                                    $normalized[$name] = $settings;
                                }
                                return $normalized;
                            })
                            ->end()
                            ->children()
                                ->scalarNode('driver')->defaultValue('pdo_mysql')->end()
                                ->scalarNode('dbname')->isRequired()->end()
                                ->scalarNode('host')->defaultValue('localhost')->end()
                                ->scalarNode('port')->defaultValue('3306')->end()
                                ->scalarNode('user')->isRequired()->end()
                                ->scalarNode('password')->isRequired()->end()
                                ->scalarNode('charset')->defaultValue('utf8mb4')->end()
                                ->scalarNode('collate')->defaultValue('utf8mb4_unicode_ci')->end()
                                ->booleanNode('debug')->defaultFalse()->end()
                                ->scalarNode('socket')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('liquid')
                ->children()
                    ->scalarNode('cache')->defaultValue('%kernel.cache_dir%/liquid')->end()
                    ->scalarNode('charset')->defaultValue('%kernel.charset%')->end()
                    ->booleanNode('debug')->defaultValue('%kernel.debug%')->end()
                    ->scalarNode('filter')->defaultFalse()->end()
                    ->scalarNode('include_suffix')->defaultValue('tpl')->end()
                    ->scalarNode('include_prefix')->defaultValue('')->end()
                    ->scalarNode('default_path')
                        ->info('The default path used to load templates')
                        ->defaultValue('%kernel.project_dir%/templates')
                    ->end()
                    ->arrayNode('tags')
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('tags')
                    ->beforeNormalization()
                    ->always()
                    ->then(function ($tags) {
                        $normalized = [];
                        foreach ($tags as $name => $namespace) {
                            if (\is_array($namespace)) {
                                // xml
                                $name = $namespace['value'];
                                $namespace = $namespace['namespace'];
                            }
                            // path within the default namespace
                            if (ctype_digit((string) $namespace)) {
                                $name = $namespace;
                                $namespace = null;
                            }
                            $normalized[$name] = $namespace;
                        }
                        return $normalized;
                    })
                    ->end()
                    ->prototype('variable')->end()
                    ->end()
                    ->arrayNode('paths')
                        ->normalizeKeys(false)
                        ->useAttributeAsKey('paths')
                        ->beforeNormalization()
                        ->always()
                        ->then(function ($paths) {
                            $normalized = [];
                            foreach ($paths as $namespace => $path) {
                                if (\is_array($namespace)) {
                                    // xml
                                    $path = $namespace['value'];
                                    $namespace = $namespace['namespace'];
                                }
                                // path within the default namespace
                                if (ctype_digit((string) $path)) {
                                    $path = $namespace;
                                    $namespace = null;
                                }
                                $normalized[$namespace] = $path;
                            }
                            return $normalized;
                        })
                        ->end()
                        ->prototype('variable')->end()
                    ->end()
                ->end()
        ;
        return $treeBuilder;
    }

}