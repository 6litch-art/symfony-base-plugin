<?php

namespace Base\Composer\Package\Hook;

use Base\Composer\Package\AbstractHook;
use Composer\Installer\PackageEvent;
use Base\Composer\CodeModifier;

final class EasyAdminPackageHook extends AbstractHook
{
    public function getPackageName(): string
    {
        return 'easycorp/easyadmin-bundle';
    }

    public function getPackageRequirements(): string
    {
        return "*";
    }

    public function onPackageRemove(PackageEvent $event)
    {
        $phpFiles = iterator_to_array($this->getBundlePHPFiles());
        foreach ($phpFiles as $phpFile) {
            $codeModifier = new CodeModifier($phpFile, $this->getAuthor());
            $codeModifier->restore();
        }
    }

    public function onPackageChange(PackageEvent $event)
    {
        $phpFiles = iterator_to_array($this->getBundlePHPFiles());
        $this->removeFinalFromAllClasses($phpFiles);
        $this->removeSelfFromAllClasses($phpFiles);
        $this->changePrivateToProtectedPropertiesFromAllClasses($phpFiles);
        $this->changeNewSelfToNewStaticFromAllClasses($phpFiles);
        $this->enableMultiWordSearch($phpFiles);
    }

    public function removeFinalFromAllClasses($phpFiles)
    {
        $this->print('Make classes `non-final` in all PHP files.');
        // strict: false — this is a best-effort blanket pass over EVERY file
        // in the bundle; most files legitimately contain no `final`, so a
        // no-match here is normal, not a drifted-anchor failure.
        foreach ($phpFiles as $phpFile) {
            $codeModifier = new CodeModifier($phpFile, $this->getAuthor(), strict: false);
            $codeModifier->replace("non-final", 'final readonly class ', 'readonly class ');
            $codeModifier->replace("non-final", 'final class ', 'class ');
        }

    }

    public function removeSelfFromAllClasses($phpFiles)
    {
        $this->print('Remove all `self` requirements in class method returns in all PHP files.');
        foreach ($phpFiles as $phpFile) {
            $codeModifier = new CodeModifier($phpFile, $this->getAuthor(), strict: false);
            $codeModifier->replace("no-self", ['): self', '):self', ') :self'], ')');
        }

    }

    public function changeNewSelfToNewStaticFromAllClasses($phpFiles)
    {
        $this->print('Change all `self` declarations to `static` declarations in all PHP files.');
        foreach ($phpFiles as $phpFile) {
            $codeModifier = new CodeModifier($phpFile, $this->getAuthor(), strict: false);
            $codeModifier->replace("self-static", 'new self', 'new static');
        }

    }

    public function changePrivateToProtectedPropertiesFromAllClasses($phpFiles)
    {
        $this->print('Turn `private` properties into `protected` properties in all PHP files.');
        foreach ($phpFiles as $phpFile) {
            $codeModifier = new CodeModifier($phpFile, $this->getAuthor(), strict: false);
            $codeModifier->replace("private-protected", 'private ', 'protected ');
        }

    }

    public function enableMultiWordSearch($phpFiles)
    {
        $this->print('Allow multi word search in CRUD controllers in `./Orm/EntityRepository.php`.');
        $codeModifier = new CodeModifier($this->getBundleDir() . '/src/Orm/EntityRepository.php', $this->getAuthor());
        $codeModifier->replace("multiword-fix", "'%'.\$lowercaseQuery.'%'", "'%'.str_replace(' ', '%', \$lowercaseQuery).'%'");
    }
}
