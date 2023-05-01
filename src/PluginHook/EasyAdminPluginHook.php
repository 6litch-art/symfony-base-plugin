<?php

namespace Base\Composer\PluginHook;

use Composer\Installer\PackageEvent;

final class EasyAdminPluginHook extends AbstractPluginHook
{
    public function getPackageName(): string
    {
        return 'easycorp/easyadmin-bundle';
    }

    public function onPackageInstall(PackageEvent $event)
    {
        $this->removeFinalFromAllClasses();
        $this->removeSelfFromAllClasses();
        $this->changePrivateToProtectedPropertiesFromAllClasses();
        $this->changeNewSelfToNewStaticFromAllClasses();
        $this->enableMultiWordSearch();
    }

    public function onPackageUpdate(PackageEvent $event)
    {
        $this->removeFinalFromAllClasses();
        $this->removeSelfFromAllClasses();
        $this->changePrivateToProtectedPropertiesFromAllClasses();
        $this->changeNewSelfToNewStaticFromAllClasses();
        $this->enableMultiWordSearch();
    }

    public function removeFinalFromAllClasses()
    {
        foreach ($this->getBundlePHPFiles() as $phpFile) {
            file_replace('final class ', 'class ', $phpFile);
        }

        $this->Print('Updated all PHP files. Make classes `non-final`');
    }

    public function removeSelfFromAllClasses()
    {
        foreach ($this->getBundlePHPFiles() as $phpFile) {
            file_replace(['): self', '):self', ') :self'], ')', $phpFile);
        }

        $this->Print('Updated all PHP files. Remove all `self` requirements in class method returns');
    }

    public function changeNewSelfToNewStaticFromAllClasses()
    {
        foreach ($this->getBundlePHPFiles() as $phpFile) {
            file_replace('new self', 'new static', $phpFile);
        }

        $this->Print('Updated all PHP files. Change all `self` declarations to `static` declarations');
    }

    public function changePrivateToProtectedPropertiesFromAllClasses()
    {
        foreach ($this->getBundlePHPFiles() as $phpFile) {
            file_replace('private ', 'protected ', $phpFile);
        }

        $this->Print('Updated all PHP files. Turn `private` properties into `protected` properties');
    }

    public function enableMultiWordSearch()
    {
        file_replace("'%'.\$lowercaseQuery.'%'", "'%'.str_replace(' ', '%', \$lowercaseQuery).'%'", $this->getBundleDir().'/src/Orm/EntityRepository.php');
        $this->Print('Updated `./Orm/EntityRepository.php` file. Allow multi word search in CRUD controllers.');
    }
}
