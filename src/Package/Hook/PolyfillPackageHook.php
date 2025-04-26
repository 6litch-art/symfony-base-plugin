<?php

namespace Base\Composer\Package\Hook;

use Base\Composer\Package\AbstractHook;
use Composer\Installer\PackageEvent;
use Base\Composer\CodeModifier;

/**
 *
 */
final class PolyfillPackageHook extends AbstractHook
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
        $this->print('Updating "bootstrap.php" file. Removing string restricted implementation of `mb_[u|l]cfirst()` due to conflict.');
        $codeModifier = new CodeModifier($this->getBundleDir() . '/bootstrap.php', $this->getAuthor());
        $codeModifier->erase("no-mbfirst", ['mb_ucfirst($string', 'mb_lcfirst($string']);
        $codeModifier->erase("no-array-any", ['array_any(array']);
    }

    public function onPackageRemove(PackageEvent $event)
    {
        $codeModifier = new CodeModifier($this->getBundleDir() . '/bootstrap.php', $this->getAuthor());
        $codeModifier->restore();
    }
}
