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

    public function getPackageRequirements(): string
    {
        return "*";
    }

    public function onPackageChange(PackageEvent $event)
    {
        $this->Print('Updating "bootstrap.php" file. Removing string restricted implementation of `mb_[u|l]cfirst()` due to conflict.');
        $codeModifier = new \CodeModifier($this->getBundleDir() . '/bootstrap.php', $this->getAuthor());
        $codeModifier->erase("no-mbfirst", ['mb_ucfirst($string', 'mb_lcfirst($string']);
        $codeModifier->erase("no-array-any", ['array_any(array']);
    }
}
