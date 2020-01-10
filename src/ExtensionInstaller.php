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
    public const EXTENSTION_NAME = 'phing-extension';

    private const TASK_FILE = 'custom.task.properties';
    private const TYPE_FILE = 'custom.type.properties';
    private const TASK_TYPE = 'phing-custom-taskdefs';
    private const TYPE_TYPE = 'phing-custom-typedefs';

    /**
     * @var string
     */
    private $taskFile = self::TASK_FILE;

    /**
     * @var string
     */
    private $typeFile = self::TYPE_FILE;

    /**
     * @param string $taskFile
     */
    public function setTaskFile(string $taskFile): void
    {
        $this->taskFile = $taskFile;
    }

    /**
     * @param string $typeFile
     */
    public function setTypeFile(string $typeFile): void
    {
        $this->typeFile = $typeFile;
    }

    /**
     * @inheritDoc
     */
    public function supports($packageType): bool
    {
        return self::EXTENSTION_NAME === $packageType;
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
        foreach ($this->getCustomDefs() as $type => $file) {
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
        foreach ($this->getCustomDefs() as $type => $file) {
            if (!array_key_exists($type, $extra)) {
                continue;
            }

            $filename = realpath($file);

            if (false === $filename) {
                $this->io->writeError(sprintf("  - Could not find custom phing %s.", $file));

                continue;
            }

            $lines = file($filename);

            if (false === $lines) {
                $this->io->writeError(sprintf("  - Error while reading custom phing %s.", $file));

                continue;
            }

            foreach ($extra[$type] as $name => $class) {
                $line = sprintf('%s=%s%s', $name, $class, PHP_EOL);

                if (!in_array($line, $lines, true)) {
                    // not found
                    continue;
                }

                $this->io->write(sprintf("  - Removing custom phing %s <%s>.", $file, $name));
                $content = file_get_contents($filename);

                if (false === $content) {
                    $this->io->writeError(sprintf("  - Error while reading custom phing config %s.", $filename));

                    continue;
                }

                $content = str_replace($line, '', $content);
                file_put_contents($filename, $content);
            }
        }
    }

    private function getCustomDefs(): array
    {
        return [
            self::TASK_TYPE => $this->taskFile,
            self::TYPE_TYPE => $this->typeFile,
        ];
    }
}
