<?php

namespace Base\Composer\Package\Hook;

use Base\Composer\Package\AbstractHook;
use Composer\Installer\PackageEvent;
use Base\Composer\CodeModifier;

/**
 *
 */
final class ErrorHandlerPackageHook extends AbstractHook
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
        $this->print('Utilize HtmlErrorRenderer in ErrorHandler.php.');
        $codeModifier = new CodeModifier($this->getBundleDir() . '/ErrorHandler.php', $this->getAuthor());
        $codeModifier->replace("base-application", "new HtmlErrorRenderer", "new \Base\Inspector\HtmlErrorRenderer");
    }

    public function onPackageRemove(PackageEvent $event)
    {
        $codeModifier = new CodeModifier($this->getBundleDir() . '/ErrorHandler.php', $this->getAuthor());
        $codeModifier->restore();
    }
}
