<?php

namespace Base\Composer\PluginHook;

use Composer\Installer\PackageEvent;

/**
 *
 */
final class FlysystemPluginHook extends AbstractPluginHook
{
    public function getPackageName(): string
    {
        return 'league/flysystem-bundle';
    }

    public function onPackageInstall(PackageEvent $event)
    {
        file_replace('@internal', '', $this->getBundleDir() . '/src/Lazy/LazyFactory.php');
        $this->Print('Updated "./Lazy/LazyFactory.php" file. Remove `@internal` flag');
        file_replace('private $', 'protected $', $this->getBundleDir() . '/src/Lazy/LazyFactory.php');
        $this->Print('Updated "./Lazy/LazyFactory.php" file. Turn `private` properties into `protected` properties');
    }

    public function onPackageUpdate(PackageEvent $event)
    {
        file_replace('@internal', '', $this->getBundleDir() . '/src/Lazy/LazyFactory.php');
        $this->Print('Updated "./Lazy/LazyFactory.php" file. Remove `@internal` flag');
        file_replace('private $', 'protected $', $this->getBundleDir() . '/src/Lazy/LazyFactory.php');
        $this->Print('Updated "./Lazy/LazyFactory.php" file. Turn `private` properties into `protected` properties');
    }
}
