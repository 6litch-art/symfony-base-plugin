<?php

namespace Base\Composer\Package\Hook;

use Base\Composer\Package\AbstractHook;
use Composer\Installer\PackageEvent;
use Base\Composer\CodeModifier;

final class HWIOPackageHook extends AbstractHook
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
        // strict: false — hwi/oauth-bundle 2.x already extends the DI-component Extension
        // upstream, so the class this patch used to rewrite is gone. It has been a no-op on
        // beta and production for months; a missing anchor here means "upstream fixed it",
        // not "abort the install".
        $codeModifier = new CodeModifier($this->getBundleDir() . '/src/DependencyInjection/HWIOAuthExtension.php', $this->getAuthor(), strict: false);

        $this->print('Fix DI Extension in `./HWIOAuthExtension.php`.');
        $codeModifier->replace("di-extension", 'Symfony\Component\HttpKernel\DependencyInjection\Extension', 'Symfony\Component\DependencyInjection\Extension\Extension');
    }

    public function onPackageRemove(PackageEvent $event)
    {
        $codeModifier = new CodeModifier($this->getBundleDir() . '/src/DependencyInjection/HWIOAuthExtension.php', $this->getAuthor());
        $codeModifier->restore();
    }
}
