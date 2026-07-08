<?php

namespace Base\Composer\Package\Hook;

use Base\Composer\Package\AbstractHook;
use Composer\Installer\PackageEvent;
use Base\Composer\CodeModifier;

final class PolyfillPackageHook extends AbstractHook
{
    public function getPackageName(): string
    {
        return 'symfony/polyfill-mbstring';
    }

    public function getPackageRequirements(): string
    {
        return "*";
    }

    public function onPackageChange(PackageEvent $event)
    {
        // strict: false — this erase is genuinely best-effort, so a no-match
        // must NOT abort composer install. The targets may legitimately be
        // absent: recent polyfill-mbstring defines mb_ucfirst/mb_lcfirst in
        // bootstrap80.php / bootstrap72.php (not bootstrap.php, which is only
        // the version dispatcher), and PHP >= 8.4 provides mb_ucfirst/mb_lcfirst
        // natively — the polyfill's own `if (!function_exists(...))` guards then
        // skip its definitions and there is nothing to erase.
        // NB: the search strings ("mb_ucfirst($string") also predate the
        // polyfill's "?string $string" signature and its file split, so this is
        // currently a no-op on this app (PHP 8.4). Revisit if base-bundle's
        // array-capable mb_[u|l]cfirst globals ever need to win over a polyfill
        // definition again on PHP < 8.4.
        $this->print('Removing string restricted implementation of `mb_[u|l]cfirst()` in `bootstrap.php`.');
        $codeModifier = new CodeModifier($this->getBundleDir() . '/bootstrap.php', $this->getAuthor(), strict: false);
        $codeModifier->erase("no-mbfirst", ['mb_ucfirst($string', 'mb_lcfirst($string']);
        $codeModifier->erase("no-array-any", ['array_any(array']);
    }

    public function onPackageRemove(PackageEvent $event)
    {
        $codeModifier = new CodeModifier($this->getBundleDir() . '/bootstrap.php', $this->getAuthor());
        $codeModifier->restore();
    }
}
