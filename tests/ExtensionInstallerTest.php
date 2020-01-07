<?php

declare(strict_types=1);

namespace Phing\PhingComposerConfigurator;

use Composer\Composer;
use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Util\Filesystem;
use PHPUnit\Framework\TestCase;

final class ExtensionInstallerTest extends TestCase
{
    /** @var ExtensionInstaller */
    private $object;

    /** @var \Composer\IO\IOInterface */
    private $io;

    /** @var \Composer\Composer */
    private $composer;

    /** @var \Composer\Util\Filesystem */
    private $filesystem;

    /*
    protected function setUp(): void
    {
        $this->composer = $this->createMock(Composer::class);
        $this->io = $this->createMock(IOInterface::class);
        $this->filesystem = $this->createMock(Filesystem::class);

        $this->object = new ExtensionInstaller(
            $this->io,
            $this->composer,
            ExtensionInstaller::EXTENSTION_NAME,
            $this->filesystem
        );
    }
    /**/

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     */
    public function testSupports(): void
    {
        $config = $this->createMock(Config::class);
        $config->expects($this->exactly(3))
            ->method('get')
            ->willReturn(null);

        $composer = $this->createMock(Composer::class);
        $composer->expects($this->exactly(3))
            ->method('getConfig')
            ->willReturn($config);

        $io = $this->createMock(IOInterface::class);

        /** @var \Composer\Composer $composer */
        /** @var \Composer\IO\IOInterface $io */
        $object = new ExtensionInstaller(
            $io,
            $composer
        );

        $this->assertTrue($object->supports(ExtensionInstaller::EXTENSTION_NAME));
        $this->assertFalse($object->supports('library'));
    }

    /**
     * @throws \PHPUnit\Framework\ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws \PHPUnit\Framework\MockObject\RuntimeException
     */
    public function testInstall(): void
    {
        $config = $this->createMock(Config::class);
        $config->expects($this->exactly(3))
            ->method('get')
            ->willReturn(null);

        $composer = $this->createMock(Composer::class);
        $composer->expects($this->exactly(3))
            ->method('getConfig')
            ->willReturn($config);

        $io = $this->createMock(IOInterface::class);

        /** @var \Composer\Composer $composer */
        /** @var \Composer\IO\IOInterface $io */
        $object = new ExtensionInstaller(
            $io,
            $composer
        );

        $repo = $this->createMock(InstalledRepositoryInterface::class);
        $package = $this->createMock(PackageInterface::class);

        try {
            $object->install($repo, $package);
        } catch (\RuntimeException $e) {
            echo $e;exit;
        }

        $this->assertTrue(true);
    }
}
