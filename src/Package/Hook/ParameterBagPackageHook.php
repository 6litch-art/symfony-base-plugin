<?php

namespace Base\Composer\Package\Hook;

use Base\Composer\Package\AbstractHook;
use Composer\Installer\PackageEvent;
use Base\Composer\CodeModifier;

/**
 *
 */
final class ParameterBagPackageHook  extends AbstractHook
{
    public function getPackageName(): string
    {
        return 'symfony/dependency-injection';
    }

    public function getPackageRequirements(): string
    {
        return "*";
    }

    public function onPackageChange(PackageEvent $event)
    {
        $this->Print('Updating "FrozenBagContainer.php" file. Turn returned values less restrictive using (void)');
        $codeModifier = new CodeModifier($this->getBundleDir() . '/ParameterBag/FrozenParameterBag.php', $this->getAuthor());
        $codeModifier->replace("never-void", ': never', ': void ');
    }

    public function onPackageRemove(PackageEvent $event)
    {
        $codeModifier = new CodeModifier($this->getBundleDir() . '/ParameterBag/FrozenParameterBag.php', $this->getAuthor());
        $codeModifier->restore();
    }
}
