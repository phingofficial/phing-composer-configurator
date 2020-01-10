<?php

declare(strict_types=1);

namespace Phing\PhingComposerConfigurator;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * Class ExtensionInstallerPlugin
 * @package Phing\PhingConfigurator
 */
final class ExtensionInstallerPlugin implements PluginInterface
{
    /**
     * @inheritDoc
     */
    public function activate(Composer $composer, IOInterface $io): void
    {
        $installer = new ExtensionInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }
}
