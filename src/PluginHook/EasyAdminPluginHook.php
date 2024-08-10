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
            file_line_replace('final class ', 'class ', $phpFile);
        }

    }

    public function removeSelfFromAllClasses()
    {
        $this->Print('Updating all PHP files. Remove all `self` requirements in class method returns');
        foreach ($this->getBundlePHPFiles() as $phpFile) {
            file_line_replace(['): self', '):self', ') :self'], ')', $phpFile);
        }

    }

    public function changeNewSelfToNewStaticFromAllClasses()
    {
        $this->Print('Updating all PHP files. Change all `self` declarations to `static` declarations');
        foreach ($this->getBundlePHPFiles() as $phpFile) {
            file_line_replace('new self', 'new static', $phpFile);
        }

    }

    public function changePrivateToProtectedPropertiesFromAllClasses()
    {
        $this->Print('Updating all PHP files. Turn `private` properties into `protected` properties');
        foreach ($this->getBundlePHPFiles() as $phpFile) {
            file_line_replace('private ', 'protected ', $phpFile);
        }

    }

    public function enableMultiWordSearch()
    {
        $this->Print('Updating `./Orm/EntityRepository.php` file. Allow multi word search in CRUD controllers.');
        file_line_replace("'%'.\$lowercaseQuery.'%'", "'%'.str_replace(' ', '%', \$lowercaseQuery).'%'", $this->getBundleDir() . '/src/Orm/EntityRepository.php');
    }
}
