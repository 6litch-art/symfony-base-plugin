<?php

namespace Base\Composer\PluginHook;

use Composer\Installer\PackageEvent;

/**
 *
 */
final class DoctrinePluginHook extends AbstractPluginHook
{
    public function getPackageName(): string
    {
        return 'symfony/notifier';
    }

    public function onPackageInstall(PackageEvent $event)
    {
        file_replace('private ', 'protected ', $this->getBundleDir() . '/Notification/Notification.php');
        $this->Print('Updated "Notification.php" file. Turn `private` elements into `protected` elements');
    }

    public function onPackageUpdate(PackageEvent $event)
    {
        file_replace('private ', 'protected ', $this->getBundleDir() . '/Notification/Notification.php');
        $this->Print('Updated "Notification.php" file. Turn `private` elements into `protected` elements');
    }
}
