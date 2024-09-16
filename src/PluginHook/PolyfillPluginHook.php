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
        $codeModifier = new \CodeModifier($this->getBundleDir() . '/bootstrap80.php', $this->getAuthor());
        $codeModifier->erase("no-mbfirst", ['mb_ucfirst($string', 'mb_lcfirst($string']);
    }
}
