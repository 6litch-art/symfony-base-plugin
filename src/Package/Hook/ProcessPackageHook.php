<?php

namespace Base\Composer\Package\Hook;

use Base\Composer\Package\AbstractHook;
use Composer\Installer\PackageEvent;
use Base\Composer\CodeModifier;

/**
 *
 */
final class ProcessPackageHook extends AbstractHook
{
    public function getPackageName(): string
    {
        return 'symfony/process';
    }

    public function getPackageRequirements(): string
    {
        return "*";
    }

    public function onPackageChange(PackageEvent $event)
    {
        $codeModifier = new CodeModifier($this->getBundleDir() . '/Process.php', $this->getAuthor());

        $this->print('Remove `final` flag in `./Process.php`.');
        $codeModifier->replace("timeout-nullify", '?float $timeout = 60', '?float $timeout = null');

        $this->print('Turn `private` properties into `protected` properties in `./Process.php`.');
        $codeModifier->prepend("timeout-injection", '$this->setTimeout($timeout);', '        $timeout ??= $_ENV[\'PROCESS_TIMEOUT\'] ?? $_SERVER[\'PROCESS_TIMEOUT\'] ?? 60;');
    }

    public function onPackageRemove(PackageEvent $event)
    {
        $codeModifier = new CodeModifier($this->getBundleDir() . '/Process.php', $this->getAuthor());
        $codeModifier->restore();
    }
}
