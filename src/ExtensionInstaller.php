<?php

namespace Phing\PhingComposerConfigurator;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

/**
 * Class TaskInstaller
 * @package Phing\PhingConfigurator
 */
class ExtensionInstaller extends LibraryInstaller
{
    private const CUSTOM_DEFS = [
        'phing-custom-taskdefs' => 'task',
        'phing-custom-typedefs' => 'type'
    ];

    /**
     * @inheritDoc
     */
    public function supports($packageType)
    {
        return 'phing-extension' === $packageType;
    }

    /**
     * @inheritDoc
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);
        $this->installInternalComponents($package->getExtra());
    }

    /**
     * @inheritDoc
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::uninstall($repo, $package);
        $this->uninstallInternalComponents($package->getExtra());
    }

    private function installInternalComponents(array $extra): void
    {
        foreach (self::CUSTOM_DEFS as $type => $file) {
            foreach ($extra[$type] ?? [] as $name => $class) {
                $this->io->write("  - Installing custom phing ${file} <${name}>.");
                file_put_contents("custom.${file}.properties", sprintf('%s=%s%s', $name, $class, PHP_EOL), FILE_APPEND);
            }
        }
    }

    private function uninstallInternalComponents(array $extra): void
    {
        foreach (self::CUSTOM_DEFS as $type => $file) {
            foreach ($extra[$type] ?? [] as $name => $class) {
                $this->io->write("  - Removing custom phing ${file} <${name}>.");
                $content = file_get_contents("custom.${file}.properties");
                $content = str_replace(sprintf('%s=%s%s', $name, $class, PHP_EOL), '', $content);
                file_put_contents("custom.${file}.properties", $content);
            }
        }
    }
}
