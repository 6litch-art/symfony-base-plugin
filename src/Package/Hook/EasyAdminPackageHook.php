<?php

namespace Base\Composer\Package\Hook;

use Base\Composer\Package\AbstractHook;
use Composer\Installer\PackageEvent;
use Base\Composer\CodeModifier;

/**
 *
 */
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
        $this->print('Updating all PHP files. Make classes `non-final`');
        foreach ($phpFiles as $phpFile) {
            $codeModifier = new CodeModifier($phpFile, $this->getAuthor());
            $codeModifier->replace("non-final", 'final class ', 'class ');
        }

    }

    public function removeSelfFromAllClasses($phpFiles)
    {
        $this->print('Updating all PHP files. Remove all `self` requirements in class method returns');
        foreach ($phpFiles as $phpFile) {
            $codeModifier = new CodeModifier($phpFile, $this->getAuthor());
            $codeModifier->replace("no-self", ['): self', '):self', ') :self'], ')');
        }

    }

    public function changeNewSelfToNewStaticFromAllClasses($phpFiles)
    {
        $this->print('Updating all PHP files. Change all `self` declarations to `static` declarations');
        foreach ($phpFiles as $phpFile) {
            $codeModifier = new CodeModifier($phpFile, $this->getAuthor());           
            $codeModifier->replace("self-static", 'new self', 'new static');
        }

    }

    public function changePrivateToProtectedPropertiesFromAllClasses($phpFiles)
    {
        $this->print('Updating all PHP files. Turn `private` properties into `protected` properties');
        foreach ($phpFiles as $phpFile) {
            $codeModifier = new CodeModifier($phpFile, $this->getAuthor());
            $codeModifier->replace("private-protected", 'private ', 'protected ');
        }

    }

    public function enableMultiWordSearch($phpFiles)
    {
        $this->print('Updating `./Orm/EntityRepository.php` file. Allow multi word search in CRUD controllers.');
        $codeModifier = new CodeModifier($this->getBundleDir() . '/src/Orm/EntityRepository.php', $this->getAuthor());
        $codeModifier->replace("multiword-fix", "'%'.\$lowercaseQuery.'%'", "'%'.str_replace(' ', '%', \$lowercaseQuery).'%'");
    }
}
