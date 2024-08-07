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
        foreach ($this->getBundlePHPFiles() as $phpFile) {
            file_line_replace('final class ', 'class ', $phpFile, $this->getPackageName());
        }

        $this->Print('Updated all PHP files. Make classes `non-final`');
    }

    public function removeSelfFromAllClasses()
    {
        foreach ($this->getBundlePHPFiles() as $phpFile) {
            file_line_replace(['): self', '):self', ') :self'], ')', $phpFile, $this->getPackageName());
        }

        $this->Print('Updated all PHP files. Remove all `self` requirements in class method returns');
    }

    public function changeNewSelfToNewStaticFromAllClasses()
    {
        foreach ($this->getBundlePHPFiles() as $phpFile) {
            file_line_replace('new self', 'new static', $phpFile, $this->getPackageName());
        }

        $this->Print('Updated all PHP files. Change all `self` declarations to `static` declarations');
    }

    public function changePrivateToProtectedPropertiesFromAllClasses()
    {
        foreach ($this->getBundlePHPFiles() as $phpFile) {
            file_line_replace('private ', 'protected ', $phpFile, $this->getPackageName());
        }

        $this->Print('Updated all PHP files. Turn `private` properties into `protected` properties');
    }

    public function enableMultiWordSearch()
    {
        file_line_replace("'%'.\$lowercaseQuery.'%'", "'%'.str_replace(' ', '%', \$lowercaseQuery).'%'", $this->getBundleDir() . '/src/Orm/EntityRepository.php', $this->getPackageName());
        $this->Print('Updated `./Orm/EntityRepository.php` file. Allow multi word search in CRUD controllers.');
    }
}
