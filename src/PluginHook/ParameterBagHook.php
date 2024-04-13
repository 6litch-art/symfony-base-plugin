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
        return 'symfony/symfony';
    }

    public function onPackageInstall(PackageEvent $event)
    {
        $this->Print('Updated "FrozenBagContainer.php" file. Turn returned values less restricting using (void)');

        file_replace(': never', ': void ', $this->getBundleDir() . '/src/Component/DependencyInjection/ParameterBag/FrozenParameterBag.php');
    }

    public function onPackageUpdate(PackageEvent $event)
    {
        $this->Print('Updated "FrozenBagContainer.php" file. Turn returned values less restricting using (void)');

        file_replace(': never', ': void ', $this->getBundleDir() . '/src/Component/DependencyInjection/ParameterBag/FrozenParameterBag.php');
    }
}
