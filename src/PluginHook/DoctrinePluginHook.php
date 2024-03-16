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
        return 'doctrine/orm';
    }

    public function onPackageInstall(PackageEvent $event)
    {
        file_replace(
            '$this->_metadataCache[$relation[\'targetEntity\']]',
            '$this->_metadataCache[$relation[\'targetEntity\']] ?? $this->_metadataCache[str_replace("App\\\\", "Base\\\\", $relation[\'targetEntity\'])]',
            $this->getBundleDir() . '/lib/Doctrine/ORM/Internal/Hydration/ObjectHydrator.php'
        );
        $this->Print('Updated "ObjectHydrator.php" file. Add metadata cache fallback for base component');

        file_replace('private ', 'protected ', $this->getBundleDir() . '/lib/Doctrine/ORM/Query/SqlWalker.php');
        $this->Print('Updated "SqlWalker.php" file. Turn `private` elements into `protected` elements');

        file_replace('private ', 'protected ', $this->getBundleDir() . '/lib/Doctrine/ORM/Mapping/ClassMetadataFactory.php');
        $this->Print('Updated "ClassMetadataFactory.php" file. Turn `private` elements into `protected` elements');
    }

    public function onPackageUpdate(PackageEvent $event)
    {
        #file_replace(
        #    '$this->_metadataCache[$relation[\'targetEntity\']]',
        #    '$this->_metadataCache[$relation[\'targetEntity\']] ?? $this->_metadataCache[str_replace("App\\\\", "Base\\\\", $relation[\'targetEntity\'])]',
        #    $this->getBundleDir() . '/lib/Doctrine/ORM/Internal/Hydration/ObjectHydrator.php'
        #);
        #$this->Print('Updated "ObjectHydrator.php" file. Add metadata cache fallback for base component');

        file_replace('private ', 'protected ', $this->getBundleDir() . '/lib/Doctrine/ORM/Query/SqlWalker.php');
        $this->Print('Updated "SqlWalker.php" file. Turn `private` elements into `protected` elements');

        file_replace('private ', 'protected ', $this->getBundleDir() . '/lib/Doctrine/ORM/Mapping/ClassMetadataFactory.php');
        $this->Print('Updated "ClassMetadataFactory.php" file. Turn `private` elements into `protected` elements');
    }
}
