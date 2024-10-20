<?php

namespace Base\Composer\PluginHook;

use Composer\Installer\PackageEvent;

/**
 *
 */
final class HWIOPluginHook extends Common\AbstractPluginHook
{
    public function getPackageName(): string
    {
        return 'hwi/oauth-bundle';
    }

    public function getPackageRequirements(): string
    {
        return "*";
    }
    
    public function onPackageChange(PackageEvent $event)
    {
        $codeModifier = new \CodeModifier($this->getBundleDir() . '/src/DependencyInjection/HWIOAuthExtension.php', $this->getAuthor());

        $this->Print('Updating "./HWIOAuthExtension.php" file. Fix DI Extension.');
        $codeModifier->replace("di-extension", 'Symfony\Component\HttpKernel\DependencyInjection\Extension', 'Symfony\Component\DependencyInjection\Extension\Extension');
    }
}
