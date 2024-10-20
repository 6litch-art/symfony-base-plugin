<?php

namespace Base\Composer\PluginHook;

use Composer\Installer\PackageEvent;

/**
 *
 */
final class DoctrinePluginHook extends Common\AbstractPluginHook
{
    public function getPackageName(): string
    {
        return 'doctrine/orm';
    }

    public function onPackageChange(PackageEvent $event)
    {
        $codeModifier = new \CodeModifier($this->getBundleDir() . '/src/Internal/Hydration/ObjectHydrator.php', $this->getAuthor());
 
        $this->Print('Updating "ObjectHydrator.php" file. Add metadata cache fallback for base component');
        $codeModifier->replace("cache-defaulting",
            '$this->_metadataCache[$relation[\'targetEntity\']]',
            '$this->_metadataCache[$relation[\'targetEntity\']] ?? $this->_metadataCache[str_replace("App\\\\", "Base\\\\", $relation[\'targetEntity\'])]'
        );

        $this->Print('Updating "SqlWalker.php" file. Turn `private` elements into `protected` elements', $this->getAuthor());
        $codeModifier = new \CodeModifier($this->getBundleDir() . '/src/Query/SqlWalker.php');
        $codeModifier->replace("private-protected", 'private ', 'protected ');

        $this->Print('Updating "ClassMetadataFactory.php" file. Turn `private` elements into `protected` elements');
        $codeModifier = new \CodeModifier($this->getBundleDir() . '/src/Mapping/ClassMetadataFactory.php', $this->getAuthor());
        $codeModifier->replace("private-protected", 'private ', 'protected ');
    }
}
