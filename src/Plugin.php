<?php

namespace Base\Composer;

use Base\Composer\Cloner\StubInterface;
use Base\Composer\Package\AbstractHook;
use Base\Composer\Package\HookInterface;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\InstalledVersions;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event as ScriptEvent;
use Composer\Script\ScriptEvents;

final class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @return string
     */
    public static function getPackageName()
    {
        return basename(dirname(__FILE__, 3))."/".basename(dirname(__FILE__, 2));
    }
    
    /**
     * @return string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL  => 'onPackageInstall',
            PackageEvents::POST_PACKAGE_UPDATE   => 'onPackageUpdate',
            PackageEvents::PRE_PACKAGE_UNINSTALL => 'onPackageRemove',
            ScriptEvents::PRE_AUTOLOAD_DUMP      => "onPreAutoloadDump"
        ];
    }

    protected IOInterface $io;
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        // @todo: restore .bak before removing plugins..
    }

    private function getPluginName(): string
    {
        $composerFile = dirname(__FILE__) . '/../composer.json';
        if (!file_exists($composerFile)) {
            throw new \RuntimeException('Composer file not found: ' . $composerFile);
        }
    
        $composerJson = json_decode(file_get_contents($composerFile), true, 512, JSON_THROW_ON_ERROR);
    
        if (!isset($composerJson['name'])) {
            throw new \UnexpectedValueException('No plugin name found in ' . __CLASS__ . '.');
        }
    
        return $composerJson['name'];
    }

    private array $installedPackageNames = [];
    public function onPackageInstall(PackageEvent $event)
    {
        $operation = $event->getOperation();
        $packageName = $operation->getPackage()?->getName();
        if (in_array($packageName, $this->installedPackageNames)) {
            return;
        }

        $this->installedPackageNames[] = $packageName;

        foreach (ClassMapGenerator::createMap(__DIR__) as $className => $_) {

            if (!in_array(HookInterface::class, class_implements($className))) {
                continue;
            }

            try {
                $class = new $className($this->io);
            } catch (\Error $e) {
                continue;
            }
 
            if (!InstalledVersions::isInstalled($class->getPackageName())) {
                continue;
            }

            if ($class->getPackageName() != $packageName && $this->getPackageName() != $packageName) {
                continue;
            }
            if (!$class->checkValidityVersion($event)) {
                continue;
            }

            $class->onPackageInstall($event);
        }
    }

    private array $updatedPackageNames = [];

    public function onPackageUpdate(PackageEvent $event)
    {
        $operation = $event->getOperation();
        $packageName = $operation->getInitialPackage()?->getName();
        if (in_array($packageName, $this->updatedPackageNames)) {
            return;
        }

        $this->updatedPackageNames[] = $packageName;
        foreach (ClassMapGenerator::createMap(__DIR__) as $className => $_) {

            if (!in_array(HookInterface::class, class_implements($className))) {
                continue;
            }

            try {
                $class = new $className($this->io);
            } catch (\Error $e) {
                continue;
            }

            if (!InstalledVersions::isInstalled($class->getPackageName())) {
                continue;
            }

            if ($class->getPackageName() != $packageName && $this->getPackageName() != $packageName) {
                continue;
            }

            if (!$class->checkValidityVersion($event)) {
                continue;
            }

            $class->onPackageUpdate($event);
        }
    }

    private array $removedPackageNames = [];

    public function onPackageRemove(PackageEvent $event)
    {
        $operation = $event->getOperation();
        $packageName = $operation->getPackage()?->getName();
        if (in_array($packageName, $this->removedPackageNames)) {
            return;
        }

        $this->removedPackageNames[] = $packageName;

        foreach (ClassMapGenerator::createMap(__DIR__) as $className => $_) {

            if (!in_array(HookInterface::class, class_implements($className))) {
                continue;
            }

            try {
                $class = new $className($this->io);
            } catch (\Error $e) {
                continue;
            }

            if (!InstalledVersions::isInstalled($class->getPackageName())) {
                continue;
            }
            if ($class->getPackageName() != $packageName && $this->getPackageName() != $packageName) {
                continue;
            }
            if (!$class->checkValidityVersion($event)) {
                continue;
            }

            $class->onPackageRemove($event);
        }
    }

    public function onPreAutoloadDump(ScriptEvent $event)
    {
        $io = $event->getIO();
        foreach (ClassMapGenerator::createMap(__DIR__) as $className => $_) {

            if (!in_array(StubInterface::class, class_implements($className))) {
                continue;
            }

            try {
                $class = new $className($io);
            } catch (\Error $e) {
                continue;
            }

            $class->generate();
        }

        $io->write("\033[32mStubbing generation finished.\033[0m");
    }
}
