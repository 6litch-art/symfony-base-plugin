<?php

namespace Base\Composer\PluginHook;

use Composer\Installer\PackageEvent;

final class AliasPluginHook extends AbstractPluginHook
{
    public function getPackageName():string { return 'doctrine/orm'; }

    public function onPackageInstall(PackageEvent $event)
    {
        file_replace(
            '$this->_metadataCache[$relation[\'targetEntity\']]',
            '$this->_metadataCache[$relation[\'targetEntity\']] ?? $this->_metadataCache[str_replace("App\\\\", "Base\\\\", $relation[\'targetEntity\'])]',
            $this->getBundleDir()."/lib/Doctrine/ORM/Internal/Hydration/ObjectHydrator.php"
        );

        $this->Print('Updated "Manager.php" file. Turn private properties into protected properties');
    }

    public function onPackageUpdate(PackageEvent $event)
    {
        file_replace(
            '$this->_metadataCache[$relation[\'targetEntity\']]',
            '$this->_metadataCache[$relation[\'targetEntity\']] ?? $this->_metadataCache[str_replace("App\\\\", "Base\\\\", $relation[\'targetEntity\'])]',
            $this->getBundleDir()."/lib/Doctrine/ORM/Internal/Hydration/ObjectHydrator.php"
        );

        $this->Print('Updated "Manager.php" file. Turn private properties into protected properties');
    }
}
