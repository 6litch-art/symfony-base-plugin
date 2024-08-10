<?php

namespace Base\Composer\PluginHook;

use Composer\Installer\PackageEvent;

/**
 *
 */
final class PolyfillPluginHook extends Common\AbstractPluginHook
{
    public function getPackageName(): string
    {
        return 'symfony/polyfill-mbstring';
    }

    public function onPackageChange(PackageEvent $event)
    {
        $this->Print('Updating "bootstrap80.php" file. Removing string restricted implementation of `mb_[u|l]cfirst()` due to conflict.');
        file_line_remove('mb_ucfirst($string', $this->getBundleDir() . '/bootstrap80.php');
        file_line_remove('mb_lcfirst($string', $this->getBundleDir() . '/bootstrap80.php');
    }
}
