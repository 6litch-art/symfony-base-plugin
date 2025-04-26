<?php

namespace Base\Composer\Package\Hook;

use Base\Composer\Package\AbstractHook;
use Composer\Installer\PackageEvent;
use Base\Composer\CodeModifier;

/**
 *
 */
final class DoctrinePackageHook extends AbstractHook
{
    public function getPackageName(): string
    {
        return 'doctrine/orm';
    }

    public function getPackageRequirements(): string
    {
        return "*";
    }

    public function onPackageChange(PackageEvent $event)
    {
        $this->print('Updating "ObjectHydrator.php" file. Add metadata cache fallback for base component');
        $codeModifier = new CodeModifier($this->getBundleDir() . '/src/Internal/Hydration/ObjectHydrator.php', $this->getAuthor());
        $codeModifier->replace("cache-defaulting",
            '$this->_metadataCache[$relation[\'targetEntity\']]',
            '$this->_metadataCache[$relation[\'targetEntity\']] ?? $this->_metadataCache[str_replace("App\\\\", "Base\\\\", $relation[\'targetEntity\'])]'
        );

        $this->print('Updating "SqlWalker.php" file. Turn `private` elements into `protected` elements', $this->getAuthor());
        $codeModifier = new CodeModifier($this->getBundleDir() . '/src/Query/SqlWalker.php');
        $codeModifier->replace("private-protected", 'private ', 'protected ');

        $this->print('Updating "ClassMetadataFactory.php" file. Turn `private` elements into `protected` elements');
        $codeModifier = new CodeModifier($this->getBundleDir() . '/src/Mapping/ClassMetadataFactory.php', $this->getAuthor());
        $codeModifier->replace("private-protected", 'private ', 'protected ');
    }

    public function onPackageRemove(PackageEvent $event)
    {
        $codeModifier = new CodeModifier($this->getBundleDir() . '/src/Internal/Hydration/ObjectHydrator.php', $this->getAuthor());
        $codeModifier->restore();
        $codeModifier = new CodeModifier($this->getBundleDir() . '/src/Query/SqlWalker.php', $this->getAuthor());
        $codeModifier->restore();
        $codeModifier = new CodeModifier($this->getBundleDir() . '/src/Mapping/ClassMetadataFactory.php', $this->getAuthor());
        $codeModifier->restore();
    }
}
