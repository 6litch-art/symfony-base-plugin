<?php

namespace Base\Composer;

use Composer\Installer\PackageEvent;

interface PluginHookInterface
{
    public function getPackageName(): string;
    public function getPackagePath(): string;
    public function onPackageInstall(PackageEvent $event);
    public function onPackageUpdate (PackageEvent $event);
}