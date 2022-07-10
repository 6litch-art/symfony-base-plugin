<?php

namespace Base\Composer;

use Base\Composer\PluginHook\AbstractPluginHook;
use Composer\Composer;
use Composer\Factory;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Finder;

use Composer\Autoload\ClassMapGenerator;
use UnexpectedValueException;

include_once(dirname(__FILE__)."/../bootstrap.php");

final class Plugin implements PluginInterface, EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => 'onPackageInstall',
            PackageEvents::POST_PACKAGE_UPDATE  => 'onPackageUpdate',
        ];
    }

    public function activate  (Composer $composer, IOInterface $io) { AbstractPluginHook::$io = $io; }
    public function deactivate(Composer $composer, IOInterface $io) {}
    public function uninstall (Composer $composer, IOInterface $io) {}

    protected function getPluginName(): string
    {
        $composerFile = dirname(__FILE__)."/../composer.json";
        $composerJson = json_decode(file_get_contents($composerFile), associative: true, flags: JSON_THROW_ON_ERROR);

        if(array_key_exists("name", $composerJson))
            return $composerJson['name'];

        throw new UnexpectedValueException("No plugin name found in ".__CLASS__.". This is odd.");
    }

    protected function getInstalledPackageNames(PackageEvent $event): array
    {
        $packages = [];
        foreach ($event->getOperations() as $operation) {

            if($operation->getOperationType() == 'install')
                $packages[] = $operation->getPackage()?->getName();
        }

        return $packages;
    }

    public function onPackageInstall(PackageEvent $event)
    {
        foreach(ClassMapGenerator::createMap(__DIR__) as $className => $_) {

            if(!in_array(PluginHookInterface::class, class_implements($className))) continue;

            try { $class = new $className(); }
            catch (\Error $e) { continue; }

            if(in_array($class->getPackageName(), $this->getInstalledPackageNames($event)))
                $class->onPackageInstall($event);
        }
    }

    protected function getUpdatedPackageNames(PackageEvent $event): array
    {
        $packages = [];
        foreach ($event->getOperations() as $operation) {

            if($operation->getOperationType() == 'update')
                $packages[] = $operation->getInitialPackage()?->getName();
        }

        return $packages;
    }

    public function onPackageUpdate(PackageEvent $event)
    {
        foreach(ClassMapGenerator::createMap(__DIR__) as $className => $_) {

            if(!in_array(PluginHookInterface::class, class_implements($className))) continue;

            try { $class = new $className(); }
            catch (\Error $e) { continue; }

            if(in_array($class->getPackageName(), $this->getUpdatedPackageNames($event)))
                $class->onPackageUpdate($event);
        }
    }
}
