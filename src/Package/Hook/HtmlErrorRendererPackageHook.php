<?php

namespace Base\Composer\Package\Hook;

use Base\Composer\Package\AbstractHook;
use Composer\Installer\PackageEvent;
use Base\Composer\CodeModifier;

/**
 *
 */
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
        $this->print('Utilize base FileLinkFormatter in HtmlErrorRenderer.');
        $codeModifier = new CodeModifier($this->getBundleDir() . '/ErrorRenderer/FileLinkFormatter.php', $this->getAuthor());
        $codeModifier->replace("base-application", "new FileLinkFormatter", "new \Base\Inspector\FileLinkFormatter");
    }

    public function onPackageRemove(PackageEvent $event)
    {
        $codeModifier = new CodeModifier($this->getBundleDir() . '/ErrorRenderer/HtmlErrorRenderer.php', $this->getAuthor());
        $codeModifier->restore();
    }
}
