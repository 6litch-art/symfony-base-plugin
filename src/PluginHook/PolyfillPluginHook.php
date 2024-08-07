<?php

namespace Base\Composer\PluginHook;

use Composer\Installer\PackageEvent;

/**
 *
 */
final class PolyfillPluginHook extends AbstractPluginHook
{
    public function getPackageName(): string
    {
        return 'symfony/polyfill-mbstring';
    }

    public function onPackageEvent(PackageEvent $event)
    {
        $this->Print('Updated "bootstrap80.php" file. Removing string restricted implementation of `mb_[u|l]cfirst()` due to conflict.');
        file_remove_line('mb_ucfirst($string', $this->getBundleDir() . '/bootstrap80.php');
        file_remove_line('mb_lcfirst($string', $this->getBundleDir() . '/bootstrap80.php');
    }
}
