<?php

declare(strict_types=1);

namespace Phing\PhingComposerConfigurator;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

/**
 * Class ExtensionInstaller
 * @package Phing\PhingConfigurator
 */
final class ExtensionInstaller extends LibraryInstaller
{
    private const CUSTOM_DEFS = [
        'phing-custom-taskdefs' => 'task',
        'phing-custom-typedefs' => 'type'
    ];

    /**
     * @inheritDoc
     */
    public function supports($packageType): bool
    {
        return 'phing-extension' === $packageType;
    }

    /**
     * @inheritDoc
     * @throws \RuntimeException
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package): void
    {
        parent::install($repo, $package);

        $this->installInternalComponents($package->getExtra());
    }

    /**
     * {@inheritDoc}
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target): void
    {
        $this->uninstallInternalComponents($initial->getExtra());

        parent::update($repo, $initial, $target);

        $this->installInternalComponents($target->getExtra());
    }

    /**
     * @inheritDoc
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package): void
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

            $lines = file($file);

            // @codeCoverageIgnoreStart
            if (false === $lines) {
                $this->io->writeError(sprintf("  - Error while reading custom phing %s.", $file));

                continue;
            }
            // @codeCoverageIgnoreEnd

            foreach ($extra[$type] as $name => $class) {
                $line = sprintf('%s=%s%s', $name, $class, PHP_EOL);

                if (in_array($line, $lines, true)) {
                    $this->io->write(sprintf("  - <warning>custom phing %s <%s> was already installed.</warning>", $file, $name));
                    // already added
                    continue;
                }

                $this->io->write(sprintf("  - Installing new custom phing %s <%s>.", $file, $name));
                file_put_contents($file, $line, FILE_APPEND);
            }
        }
    }

    private function uninstallInternalComponents(array $extra): void
    {
        foreach (self::CUSTOM_DEFS as $type => $file) {
            if (!array_key_exists($type, $extra)) {
                continue;
            }

            $lines = file($file);

            // @codeCoverageIgnoreStart
            if (false === $lines) {
                $this->io->writeError(sprintf("  - Error while reading custom phing %s.", $file));

                continue;
            }
            // @codeCoverageIgnoreEnd

            $content = file_get_contents($file);

            // @codeCoverageIgnoreStart
            if (false === $content) {
                $this->io->writeError(sprintf("  - Error while reading custom phing config %s.", $file));

                continue;
            }
            // @codeCoverageIgnoreEnd

            foreach ($extra[$type] as $name => $class) {
                $line = sprintf('%s=%s%s', $name, $class, PHP_EOL);

                if (!in_array($line, $lines, true)) {
                    $this->io->write(sprintf("  - <warning>custom phing %s <%s> is not installed.</warning>", $file, $name));
                    // not found
                    continue;
                }

                $this->io->write(sprintf("  - Removing custom phing %s <%s>.", $file, $name));

                $content = str_replace($line, '', $content);
            }

            file_put_contents($file, $content);
        }
    }
}
