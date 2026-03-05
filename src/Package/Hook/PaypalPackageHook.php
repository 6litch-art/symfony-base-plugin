<?php

namespace Base\Composer\Package\Hook;

use Base\Composer\Package\AbstractHook;
use Composer\Installer\PackageEvent;
use Base\Composer\CodeModifier;

final class PaypalPackageHook extends AbstractHook
{
    public function getPackageName(): string
    {
        return 'paypal/rest-api-sdk-php';
    }

    public function getPackageRequirements(): string
    {
        return "*";
    }

    public function onPackageChange(PackageEvent $event)
    {
        $this->print('Check type `is_array($v)` first in `lib/PayPal/Common/PayPalModel.php`.');
        $codeModifier = new CodeModifier($this->getBundleDir() . '/lib/PayPal/Common/PayPalModel.php', $this->getAuthor());
        $codeModifier->replace("fix-size", 'sizeof($v) <= 0 && is_array($v)', 'is_array($v) && sizeof($v) <= 0');

        $this->print('Optional parameter $handlers declaration` removed in `lib/PayPal/Transport/PayPalRestCall.php`.');
        $codeModifier = new CodeModifier($this->getBundleDir() . '/lib/PayPal/Transport/PayPalRestCall.php', $this->getAuthor());
        $codeModifier->replace("no-optional", '$handlers = array()', '$handlers');
    }

    public function onPackageRemove(PackageEvent $event)
    {
        $codeModifier = new CodeModifier($this->getBundleDir() . '/lib/PayPal/Transport/PayPalRestCall.php', $this->getAuthor());
        $codeModifier->restore();
        $codeModifier = new CodeModifier($this->getBundleDir() . '/lib/PayPal/Common/PayPalModel.php', $this->getAuthor());
        $codeModifier->restore();
    }
}
