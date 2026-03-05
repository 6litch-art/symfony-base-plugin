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
        $codeModifier = new CodeModifier($this->getBundleDir() . '/src/DependencyInjection/HWIOAuthExtension.php', $this->getAuthor());

        $this->print('Fix DI Extension in `./HWIOAuthExtension.php`.');
        $codeModifier->replace("di-extension", 'Symfony\Component\HttpKernel\DependencyInjection\Extension', 'Symfony\Component\DependencyInjection\Extension\Extension');
    }

    public function onPackageRemove(PackageEvent $event)
    {
        $codeModifier = new CodeModifier($this->getBundleDir() . '/src/DependencyInjection/HWIOAuthExtension.php', $this->getAuthor());
        $codeModifier->restore();
    }
}
