<?php

namespace Base\Composer\PluginHook;

use Composer\Installer\PackageEvent;

/**
 *
 */
final class PaypalPluginHook extends Common\AbstractPluginHook
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
        $this->Print('Updating "./lib/PayPal/Common/PayPalModel.php" file. `Check is_array($v)` first');
        $codeModifier = new \CodeModifier($this->getBundleDir() . '/lib/PayPal/Common/PayPalModel.php', $this->getAuthor());
        $codeModifier->replace("fix-size", 'sizeof($v) <= 0 && is_array($v)', 'is_array($v) && sizeof($v) <= 0');

        $this->Print('Updating "./lib/PayPal/Transport/PayPalRestCall.php" file. `Optional parameter $handlers declaration` removed');
        $codeModifier = new \CodeModifier($this->getBundleDir() . '/lib/PayPal/Transport/PayPalRestCall.php', $this->getAuthor());
        $codeModifier->replace("no-optional", '$handlers = array()', '$handlers');
    }
}
