<?php

namespace Prokl\ArgumentResolversBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class ArgumentResolversExtension
 * @package Prokl\ArgumentResolvers\DependencyInjection
 *
 * @since 29.04.2021
 */
class ArgumentResolversExtension extends Extension
{
    private const DIR_CONFIG = '/../Resources/config';

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (!$config['defaults']['enabled']) {
            return;
        }

        $container->setParameter('argument_resolvers.resolvers', $config['resolvers']);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . self::DIR_CONFIG)
        );

        $loader->load('services.yaml');
        $loader->load('resolvers.yaml');

        // Обработать конфиг на предмет запрещенных ресолверов.
        foreach ($config['resolvers'] as $resolver => $enableStatus) {
            if ($enableStatus === false && $container->hasDefinition($resolver)) {
                $container->removeDefinition($resolver);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getAlias() : string
    {
        return 'argument_resolvers';
    }
}
