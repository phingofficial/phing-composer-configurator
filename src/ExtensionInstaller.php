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
    private const EXTENSTION_NAME = 'phing-extension';
    private const CUSTOM_DEFS = [
        'phing-custom-taskdefs' => 'task',
        'phing-custom-typedefs' => 'type'
    ];

    /**
     * @inheritDoc
     */
    public function supports($packageType)
    {
        return self::EXTENSTION_NAME === $packageType;
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
     * {@inheritDoc}
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        $this->uninstallInternalComponents($initial->getExtra());

        parent::update($repo, $initial, $target);

        $this->installInternalComponents($target->getExtra());
    }

    /**
     * @inheritDoc
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->uninstallInternalComponents($package->getExtra());

        parent::uninstall($repo, $package);
    }

    private function installInternalComponents(array $extra): void
    {
        foreach (self::CUSTOM_DEFS as $type => $file) {
            if (!array_key_exists($type, $extra)) {
                continue;
            }

            $filename = sprintf('custom.%s.properties', $file);

            foreach ($extra[$type] as $name => $class) {
                $this->io->write("  - Installing custom phing ${file} <${name}>.");
                file_put_contents($filename, sprintf('%s=%s%s', $name, $class, PHP_EOL), FILE_APPEND);
            }
        }
    }

    private function uninstallInternalComponents(array $extra): void
    {
        foreach (self::CUSTOM_DEFS as $type => $file) {
            if (!array_key_exists($type, $extra)) {
                continue;
            }

            $filename = sprintf('custom.%s.properties', $file);

            foreach ($extra[$type] as $name => $class) {
                $this->io->write("  - Removing custom phing ${file} <${name}>.");
                $content = file_get_contents($filename);
                $content = str_replace(sprintf('%s=%s%s', $name, $class, PHP_EOL), '', $content);
                file_put_contents($filename, $content);
            }
        }
    }
}
