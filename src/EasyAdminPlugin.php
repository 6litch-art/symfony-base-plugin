<?php

namespace Base\Composer\Plugin;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Factory;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;

final class EasyAdminPlugin implements PluginInterface, EventSubscriberInterface
{
    private IOInterface $io;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->io = $io;
    }

    public static function getSubscribedEvents()
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => 'onPackageInstall',
            PackageEvents::POST_PACKAGE_UPDATE => 'onPackageUpdate',
        ];
    }

    static $pluginName = "\033[0;35m@EasyAdminPlugin \033[0m";
    public function deactivate(Composer $composer, IOInterface $io) {}
    public function uninstall(Composer $composer, IOInterface $io) {}

    public function onPackageInstall(PackageEvent $event)
    {
        if (!$this->isComposerWorkingOn('easycorp/easyadmin-bundle', $event) && !$this->isComposerWorkingOn('xkzl/base-plugin', $event))
            return;

        $this->removeFinalFromAllEasyAdminClasses();
        $this->removeSelfFromAllEasyAdminClasses();
        $this->changePrivateToProtectedPropertiesFromAllEasyAdminClasses();
    }

    public function onPackageUpdate(PackageEvent $event)
    {
        if (!$this->isComposerWorkingOn('easycorp/easyadmin-bundle', $event) && !$this->isComposerWorkingOn('xkzl/base-plugin', $event))
            return;

        $this->removeFinalFromAllEasyAdminClasses();
        $this->removeSelfFromAllEasyAdminClasses();
        $this->changePrivateToProtectedPropertiesFromAllEasyAdminClasses();
    }

    public function removeFinalFromAllEasyAdminClasses()
    {
        $vendorDirPath = $this->getVendorDirPath();
        $easyAdminDirPath = $vendorDirPath.'/easycorp/easyadmin-bundle';
        foreach ($this->getFilePathsOfAllEasyAdminClasses($easyAdminDirPath) as $filePath) {
            file_put_contents(
                $filePath,
                str_replace('final class ', 'class ', file_get_contents($filePath)),
                flags: \LOCK_EX
            );
        }

        $this->io->write('    '.self::$pluginName.' Updated all EasyAdmin PHP files to make classes non-final');
    }

    public function removeSelfFromAllEasyAdminClasses()
    {
        $vendorDirPath = $this->getVendorDirPath();
        $easyAdminDirPath = $vendorDirPath.'/easycorp/easyadmin-bundle';
        foreach ($this->getFilePathsOfAllEasyAdminClasses($easyAdminDirPath) as $filePath) {
            file_put_contents(
                $filePath,
                str_replace(['): self', '):self', ') :self'], ')', file_get_contents($filePath)),
                flags: \LOCK_EX
            );
        }

        $this->io->write('    '.self::$pluginName.' Updated all EasyAdmin PHP files to remove all self constraint after class methods');
    }

    public function changePrivateToProtectedPropertiesFromAllEasyAdminClasses()
    {
        $vendorDirPath = $this->getVendorDirPath();
        $easyAdminDirPath = $vendorDirPath.'/easycorp/easyadmin-bundle';
        foreach ($this->getFilePathsOfAllEasyAdminClasses($easyAdminDirPath) as $filePath) {
            file_put_contents(
                $filePath,
                str_replace('private ', 'protected ', file_get_contents($filePath)),
                flags: \LOCK_EX
            );
        }

        $this->io->write('    '.self::$pluginName.' Updated all EasyAdmin PHP files to turn private properties into protected properties');
    }

    private function isComposerWorkingOn(string $packageName, PackageEvent $event): bool
    {
        /** @var PackageInterface|null $package */
        $package = null;

        foreach ($event->getOperations() as $operation) {
            if ('install' === $operation->getOperationType()) {
                /** @var InstallOperation $operation */
                $package = $operation->getPackage();
            } elseif ('update' === $operation->getOperationType()) {
                /** @var UpdateOperation $operation */
                $package = $operation->getInitialPackage();
            }
        }

        return $packageName === $package?->getName();
    }

    private function getVendorDirPath(): string
    {
        $composerJsonFilePath = Factory::getComposerFile();
        $composerJsonContents = json_decode(file_get_contents($composerJsonFilePath), associative: true, flags: JSON_THROW_ON_ERROR);
        $projectDir = dirname(realpath($composerJsonFilePath));

        return $composerJsonContents['config']['vendor-dir'] ?? $projectDir.'/vendor';
    }

    /**
     * @return iterable Returns the file paths of all PHP files that contain EasyAdmin classes
     */
    private function getFilePathsOfAllEasyAdminClasses(string $easyAdminDirPath): iterable
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($easyAdminDirPath, \FilesystemIterator::SKIP_DOTS)) as $filePath) {
            if (is_dir($filePath) || !str_ends_with($filePath, '.php')) {
                continue;
            }

            yield $filePath;
        }
    }
}
