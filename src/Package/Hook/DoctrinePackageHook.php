<?php

namespace Base\Composer\Package\Hook;

use Base\Composer\Package\AbstractHook;
use Composer\Installer\PackageEvent;
use Base\Composer\CodeModifier;

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
        $this->print('Add metadata cache fallback for base component in `ObjectHydrator.php`.');
        // strict: false — doctrine/orm 3.x renamed `_metadataCache` to `metadataCache` and
        // turned `$relation['targetEntity']` into `$relation->targetEntity`, so this anchor
        // no longer exists upstream. The patch has been inactive on both beta and production
        // since doctrine/orm 3.6.7 landed (2026-06-05), with no ill effect, so a no-match
        // here must not abort the whole install. Reviving the fallback against the new
        // upstream shape is a separate, deliberate change — not something a deploy should
        // switch on silently.
        $codeModifier = new CodeModifier($this->getBundleDir() . '/src/Internal/Hydration/ObjectHydrator.php', $this->getAuthor(), strict: false);
        $codeModifier->replace("cache-defaulting",
            '$this->_metadataCache[$relation[\'targetEntity\']]',
            '$this->_metadataCache[$relation[\'targetEntity\']] ?? $this->_metadataCache[str_replace("App\\\\", "Base\\\\", $relation[\'targetEntity\'])]'
        );

        $this->print('Turn `private` elements into `protected` elements in `SqlWalker.php`.', $this->getAuthor());
        $codeModifier = new CodeModifier($this->getBundleDir() . '/src/Query/SqlWalker.php');
        $codeModifier->replace("private-protected", 'private ', 'protected ');

        $this->print('Turn `private` elements into `protected` elements in `ClassMetadataFactory.php`.');
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
