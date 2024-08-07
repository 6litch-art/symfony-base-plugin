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

    public function onPackageChange(PackageEvent $event)
    {
        file_line_replace('sizeof($v) <= 0 && is_array($v)', 'is_array($v) && sizeof($v) <= 0', $this->getBundleDir() . '/lib/PayPal/Common/PayPalModel.php', $this->getPackageName());
        $this->Print('Updated "./lib/PayPal/Common/PayPalModel.php" file. `Check is_array($v)` first');

        file_line_replace('$handlers = array()', '$handlers', $this->getBundleDir() . '/lib/PayPal/Transport/PayPalRestCall.php', $this->getPackageName());
        $this->Print('Updated "./lib/PayPal/Transport/PayPalRestCall.php" file. `Optional parameter $handlers declaration` removed');
    }
}
