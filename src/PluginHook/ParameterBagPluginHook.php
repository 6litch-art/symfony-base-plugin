<?php

namespace Base\Composer\PluginHook;

use Composer\Installer\PackageEvent;

/**
 *
 */
final class ParameterBagPluginHook extends Common\AbstractPluginHook
{
    public function getPackageName(): string
    {
        return 'symfony/dependency-injection';
    }

    public function onPackageChange(PackageEvent $event)
    {
        $this->Print('Updating "FrozenBagContainer.php" file. Turn returned values less restrictive using (void)');
        file_line_replace(': never', ': void ', $this->getBundleDir() . '/ParameterBag/FrozenParameterBag.php');
    }
}
