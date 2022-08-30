<?php

namespace Base\Composer\PluginHook;

use Composer\Installer\PackageEvent;

final class DoctrinePluginHook extends AbstractPluginHook
{
    public function getPackageName(): string { return 'doctrine/persistence'; }

    public function onPackageInstall(PackageEvent $event)
    {
        file_replace('private function', 'protected function', $this->getBundleDir().'/src/Persistence/Mapping/AbstractClassMetadataFactory.php');
        $this->Print('Updated "./Persistence/Mapping/AbstractClassMetadataFactory.php" file. Turn private methods into protected methods');
    }
    public function onPackageUpdate(PackageEvent $event)
    {
        file_replace('private $', 'protected $', $this->getBundleDir().'/src/Persistence/Mapping/AbstractClassMetadataFactory.php');
        $this->Print('Updated "./Persistence/Mapping/AbstractClassMetadataFactory.php" file. Turn private methods into protected methods');
    }
}
