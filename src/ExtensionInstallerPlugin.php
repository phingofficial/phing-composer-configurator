<?php

namespace Phing\PhingComposerConfigurator;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * Class TaskInstallerPlugin
 * @package Phing\PhingConfigurator
 */
class ExtensionInstallerPlugin implements PluginInterface
{
    /**
     * @inheritDoc
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $installer = new ExtensionInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }
}
