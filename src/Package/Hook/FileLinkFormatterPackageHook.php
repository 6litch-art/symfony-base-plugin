<?php

namespace Base\Composer\Package\Hook;

use Base\Composer\Package\AbstractHook;
use Composer\Installer\PackageEvent;
use Base\Composer\CodeModifier;

final class FileLinkFormatterPackageHook  extends AbstractHook
{
    public function getPackageName(): string
    {
        return 'symfony/error-handler';
    }

    public function getPackageRequirements(): string
    {
        return "*";
    }

    public function onPackageChange(PackageEvent $event)
    {
        $this->print('Remove @final in `FileLinkFormatter.php`.');
        $codeModifier = new CodeModifier($this->getBundleDir() . '/ErrorRenderer/FileLinkFormatter.php', $this->getAuthor());
        $codeModifier->replaceInComments("final", '@final', '');

        $this->print('Remove @internal in `FileLinkFormatter.php`.');
        $codeModifier = new CodeModifier($this->getBundleDir() . '/ErrorRenderer/FileLinkFormatter.php', $this->getAuthor());
        $codeModifier->replaceInComments("internal", '@internal', '');

        $this->print('Turn private into protected in `FileLinkFormatter.php`.');
        $codeModifier = new CodeModifier($this->getBundleDir() . '/ErrorRenderer/FileLinkFormatter.php', $this->getAuthor());
        $codeModifier->replace("private-protected", 'private', 'protected');
    }

    public function onPackageRemove(PackageEvent $event)
    {
        $codeModifier = new CodeModifier($this->getBundleDir() . '/ErrorRenderer/FileLinkFormatter.php', $this->getAuthor());
        $codeModifier->restore();
    }
}
