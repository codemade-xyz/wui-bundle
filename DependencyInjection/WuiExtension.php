<?php
namespace CodeMade\WuiBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class WuiExtension extends Extension
{

    /**
     * Loads the wui configuration.
     *
     * @param array $configs An array of configuration settings
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
        $loader->load('wui.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('liquid');
        $definition->setPublic(true);
        $definition->setAutoconfigured(true);
        $definition->setAutowired(true);
        $definition->replaceArgument(1, $config['liquid']);

        $definition = $container->getDefinition('database');
        $definition->setPublic(true);
        $definition->setAutoconfigured(true);
        $definition->setAutowired(true);
        $definition->replaceArgument(1, $config['pdo']);

        //$definition = $container->getDefinition('app.database');
        //$definition->setPublic(true);

    }


}