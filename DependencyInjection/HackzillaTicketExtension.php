<?php

/*
 * This file is part of HackzillaTicketBundle package.
 *
 * (c) Daniel Platt <github@ofdan.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hackzilla\Bundle\TicketBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @final since hackzilla/ticket-bundle 3.x.
 */
class HackzillaTicketExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(self::bundleDirectory().'/Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('hackzilla_ticket.model.user.class', $config['user_class']);
        $container->setParameter('hackzilla_ticket.model.ticket.class', $config['ticket_class']);
        $container->setParameter('hackzilla_ticket.model.message.class', $config['message_class']);

        $container->setParameter('hackzilla_ticket.features', $config['features']);
        $container->setParameter('hackzilla_ticket.templates', $config['templates']);

        $bundles = $container->getParameter('kernel.bundles');
        // Remove "file_upload_subscriber" definition if VichUploaderBundle is not registered
        if (!isset($bundles['VichUploaderBundle'])) {
            $container->removeDefinition('hackzilla_ticket.file_upload_subscriber');
        }

        $this->setTranslationDomain($config, $container);

        if (isset($bundles['FOSUserBundle'])) {
            $this->createDoctrineCommonBackwardCompatibilityAliases();
        }
    }

    public static function bundleDirectory()
    {
        return realpath(__DIR__.'/..');
    }

    private function setTranslationDomain(array $config, ContainerBuilder $container)
    {
        $translationDomain = $config['translation_domain'];

        if ('HackzillaTicketBundle' !== $translationDomain) {
            @trigger_error(
                'Omitting the option "hackzilla_ticket.translation_domain" or using other value than "HackzillaTicketBundle" is deprecated since hackzilla/ticket-bundle 3.3.'
                .' This option will be removed in version 4.0 and the only supported translation domain will be "HackzillaTicketBundle".',
                E_USER_DEPRECATED
            );
        }

        $container->setParameter('hackzilla_ticket.translation_domain', $translationDomain);

        $definition = $container->getDefinition('hackzilla_ticket.ticket_manager');
        $definition->addMethodCall('setTranslationDomain', [$translationDomain]);
    }

    /**
     * We MUST remove this method when support for "friendsofsymfony/user-bundle" is dropped
     * or adapted to work with "doctrine/common:^3".
     */
    private function createDoctrineCommonBackwardCompatibilityAliases(): void
    {
        if (!interface_exists(\Doctrine\Common\Persistence\ObjectManager::class)) {
            class_alias(\Doctrine\Persistence\ObjectManager::class, \Doctrine\Common\Persistence\ObjectManager::class);
        }

        if (!class_exists(\Doctrine\Common\Persistence\Event\LifecycleEventArgs::class)) {
            class_alias(\Doctrine\Persistence\Event\LifecycleEventArgs::class, \Doctrine\Common\Persistence\Event\LifecycleEventArgs::class);
        }
    }
}
