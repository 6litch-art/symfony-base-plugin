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
        $codeModifier = new \CodeModifier($this->getBundleDir() . '/src/Lazy/LazyFactory.php', $this->getAuthor());

        $this->Print('Updating "./Lazy/LazyFactory.php" file. Remove `@internal` flag');
        $codeModifier->replaceInComments("no-internal", '@internal', '');

        $this->Print('Updating "./Lazy/LazyFactory.php" file. Turn `private` properties into `protected` properties');
        $codeModifier->replace("private-protected", 'private ', 'protected ');
    }
}
