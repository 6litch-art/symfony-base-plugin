<?php

namespace Base\Composer\PluginHook;

use Composer\Installer\PackageEvent;

/**
 *
 */
final class EasyAdminPluginHook extends Common\AbstractPluginHook
{
    public function getPackageName(): string
    {
        return 'easycorp/easyadmin-bundle';
    }

    public function onPackageChange(PackageEvent $event)
    {
        $this->removeFinalFromAllClasses();
        $this->removeSelfFromAllClasses();
        $this->changePrivateToProtectedPropertiesFromAllClasses();
        $this->changeNewSelfToNewStaticFromAllClasses();
        $this->enableMultiWordSearch();
    }

    public function removeFinalFromAllClasses()
    {
        $this->Print('Updating all PHP files. Make classes `non-final`');
        foreach ($this->getBundlePHPFiles() as $phpFile) {
            $codeModifier = new \CodeModifier($phpFile, $this->getAuthor());
            $codeModifier->modify("non-final", 'final class ', 'class ');
        }

    }

    public function removeSelfFromAllClasses()
    {
        $this->Print('Updating all PHP files. Remove all `self` requirements in class method returns');
        foreach ($this->getBundlePHPFiles() as $phpFile) {
            $codeModifier = new \CodeModifier($phpFile, $this->getAuthor());
            $codeModifier->modify("no-self", ['): self', '):self', ') :self'], ')');
        }

    }

    public function changeNewSelfToNewStaticFromAllClasses()
    {
        $this->Print('Updating all PHP files. Change all `self` declarations to `static` declarations');
        foreach ($this->getBundlePHPFiles() as $phpFile) {
            $codeModifier = new \CodeModifier($phpFile, $this->getAuthor());           
            $codeModifier->modify("self-static", 'new self', 'new static');
        }

    }

    public function changePrivateToProtectedPropertiesFromAllClasses()
    {
        $this->Print('Updating all PHP files. Turn `private` properties into `protected` properties');
        foreach ($this->getBundlePHPFiles() as $phpFile) {
            $codeModifier = new \CodeModifier($phpFile, $this->getAuthor());
            $codeModifier->modify("private-protected", 'private ', 'protected ');
        }

    }

    public function enableMultiWordSearch()
    {
        $this->Print('Updating `./Orm/EntityRepository.php` file. Allow multi word search in CRUD controllers.');
        $codeModifier = new \CodeModifier($this->getBundleDir() . '/src/Orm/EntityRepository.php', $this->getAuthor());
        $codeModifier->modify("multiword-fix", "'%'.\$lowercaseQuery.'%'", "'%'.str_replace(' ', '%', \$lowercaseQuery).'%'");
    }
}
