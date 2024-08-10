<?php

namespace Base\Composer\PluginHook;

use Composer\Installer\PackageEvent;

/**
 *
 */
final class FlysystemPluginHook extends Common\AbstractPluginHook
{
    public function getPackageName(): string
    {
        return 'league/flysystem-bundle';
    }

    public function onPackageChange(PackageEvent $event)
    {
        $this->Print('Updating "./Lazy/LazyFactory.php" file. Remove `@internal` flag');
        file_line_replace('@internal', '', $this->getBundleDir() . '/src/Lazy/LazyFactory.php');

        $this->Print('Updating "./Lazy/LazyFactory.php" file. Turn `private` properties into `protected` properties');
        file_line_replace('private ', 'protected ', $this->getBundleDir() . '/src/Lazy/LazyFactory.php');
    }
}
