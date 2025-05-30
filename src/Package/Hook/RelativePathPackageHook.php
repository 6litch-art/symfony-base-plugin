<?php

namespace Base\Composer\Package\Hook;

use Base\Composer\Package\AbstractHook;
use Composer\Installer\PackageEvent;
use Base\Composer\CodeModifier;

/**
 *
 */
final class RelativePathPackageHook extends AbstractHook
{
    public function getPackageName(): string
    {
        return 'glitchr/base-bundle';
    }

    public function getPackageRequirements(): string
    {
        return "*";
    }

    public function onPackageRemove(PackageEvent $event)
    {
        $phpFiles = iterator_to_array($this->getBundlePHPFiles());
        foreach ($phpFiles as $phpFile) {
            $codeModifier = new CodeModifier($phpFile, $this->getAuthor());
            $codeModifier->restore();
        }
    }

    public function onPackageChange(PackageEvent $event)
    {
        $this->print('Modify console script in `./bin/console`.');
        $codeModifier = new CodeModifier($this->getProjectDir() . '/bin/console', $this->getAuthor());
        $codeModifier->replace("base-kernel", "use App\\Kernel", "use Base\\Kernel");
        $codeModifier->replace("base-application", "use Symfony\\Bundle\\FrameworkBundle\\Console\\Application", "use Base\\Console\\Application");
    }
}
