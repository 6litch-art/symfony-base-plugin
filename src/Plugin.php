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

    protected function getInstalledPackages(PackageEvent $event): array
    {
        $packages = [];
        foreach ($event->getOperations() as $operation) {

            if($operation->getOperationType() == 'install')
                $packages[] = $operation->getPackage();
        }

        return $packages;
    }

    public function onPackageInstall(PackageEvent $event)
    {
        foreach($this->getAllClasses() as $className) {

            if(in_array(PluginHookInterface::class, class_implements($className))) {

                $class = new $className();
                if(in_array($class->getPackageName(), $this->getInstalledPackages($event)))
                    $class->onPackageInstall($event);
            }
        }
    }

    protected function getUpdatedPackages(PackageEvent $event): array
    {
        $packages = [];
        foreach ($event->getOperations() as $operation) {

            if($operation->getOperationType() == 'update')
                $packages[] = $operation->getInitialPackage();
        }

        return $packages;
    }

    public function onPackageUpdate(PackageEvent $event)
    {
        foreach($this->getAllClasses() as $className) {

            if(in_array(PluginHookInterface::class, class_implements($className))) {

                $class = new $className();
                if(in_array($class->getPackageName(), $this->getUpdatedPackages($event)))
                    $class->onPackageUpdate($event);
            }
        }
    }

    public static function getAllClasses(string $path = "", string $prefix = ""): array
    {
        $classes = [];

        $filenames = self::getFilePaths($path);
        foreach ($filenames as $filename) {

            if(filesize($filename) == 0) continue;
            if(str_ends_with($filename, "Interface")) continue;

            $classes[] = self::getFullNamespace($filename, $prefix) . self::getClassname($filename);
        }

        return $classes;
    }

    public static function getClassname($filename)
    {
        $directoriesAndFilename = explode('/', $filename);
        $filename = array_pop($directoriesAndFilename);
        $nameAndExtension = explode('.', $filename);
        $className = array_shift($nameAndExtension);
        return $className;
    }

    public static function getFullNamespace($filename, $prefix = "")
    {
        $lines = file($filename);
        $array = preg_grep('/^namespace /', $lines);
        $namespace = array_shift($array);

        $match = [];
        if( preg_match('/^namespace (\\\\?)'. addslashes($prefix).'(\\\\?)(.*);$/', $namespace, $match) ) {

            $array = array_pop($match);
            if(!empty($array)) return $array."\\";
        }

        return "";
    }

    public static function getFilePaths($path)
    {
        if(!file_exists($path)) return [];

        $finderFiles = Finder::create()->files()->in($path)->name('*.php');
        $filenames = [];
        foreach ($finderFiles as $finderFile)
            $filenames[] = $finderFile->getRealpath();

        return $filenames;
    }
}
