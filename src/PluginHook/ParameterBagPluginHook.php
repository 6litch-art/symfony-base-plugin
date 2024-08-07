<?php

namespace Base\Composer\PluginHook;

use Composer\Installer\PackageEvent;

/**
 *
 */
final class ParameterBagPluginHook extends AbstractPluginHook
{
    public function getPackageName(): string
    {
        return 'symfony/dependency-injection';
    }

    public function onPackageEvent(PackageEvent $event)
    {
        $this->Print('Updated "FrozenBagContainer.php" file. Turn returned values less restrictive using (void)');
        file_replace(': never', ': void ', $this->getBundleDir() . '/ParameterBag/FrozenParameterBag.php');
    }
}
