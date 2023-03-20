<?php

namespace Base\Composer\PluginHook;

use Composer\Installer\PackageEvent;

final class BackupManagerPluginHook extends AbstractPluginHook
{
    public function getPackageName(): string
    {
        return 'backup-manager/backup-manager';
    }

    public function onPackageInstall(PackageEvent $event)
    {
        file_replace('private $', 'protected $', $this->getBundleDir()."/src/Manager.php");
        $this->Print('Updated "Manager.php" file. Turn `private` properties into `protected` properties');
    }

    public function onPackageUpdate(PackageEvent $event)
    {
        file_replace('private $', 'protected $', $this->getBundleDir()."/src/Manager.php");
        $this->Print('Updated "Manager.php" file. Turn `private` properties into `protected` properties');
    }
}
