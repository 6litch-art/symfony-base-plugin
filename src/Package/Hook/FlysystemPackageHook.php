<?php

namespace Base\Composer\Package\Hook;

use Base\Composer\Package\AbstractHook;
use Composer\Installer\PackageEvent;
use Base\Composer\CodeModifier;

/**
 *
 */
final class FlysystemPackageHook extends AbstractHook
{
    public function getPackageName(): string
    {
        return 'league/flysystem-bundle';
    }

    public function getPackageRequirements(): string
    {
        return "*";
    }

    public function onPackageChange(PackageEvent $event)
    {
        $codeModifier = new CodeModifier($this->getBundleDir() . '/src/Lazy/LazyFactory.php', $this->getAuthor());

        $this->print('Remove `@internal` flag in `./Lazy/LazyFactory.php`.');
        $codeModifier->replaceInComments("no-internal", '@internal', '');

        $this->print('Remove `final` flag in `./Lazy/LazyFactory.php`.');
        $codeModifier->replace("no-final", 'final', '');

        $this->print('Turn `private` properties into `protected` properties in `./Lazy/LazyFactory.php`.');
        $codeModifier->replace("private-protected", 'private ', 'protected ');
    }

    public function onPackageRemove(PackageEvent $event)
    {
        $codeModifier = new CodeModifier($this->getBundleDir() . '/src/Lazy/LazyFactory.php', $this->getAuthor());
        $codeModifier->restore();
    }
}
