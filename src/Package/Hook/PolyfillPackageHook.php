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

    /**
     * polyfill-mbstring defines mb_ucfirst/mb_lcfirst in bootstrap80.php
     * (PHP >= 8.0) and bootstrap72.php (older); bootstrap.php is only the
     * version dispatcher. Both are erased so base-bundle's own array-capable
     * mb_[u|l]cfirst globals can win the `function_exists` race on PHP < 8.4.
     */
    private const MBSTRING_BOOTSTRAPS = ['bootstrap80.php', 'bootstrap72.php'];

    public function onPackageChange(PackageEvent $event)
    {
        // strict: false — genuinely best-effort, so a no-match must NOT abort
        // composer install. The targets are legitimately absent in two cases:
        //  (a) PHP >= 8.4 provides mb_ucfirst/mb_lcfirst NATIVELY, so the
        //      polyfill's own `if (!function_exists(...))` guards skip its
        //      definitions and base's array-capable version is unavailable
        //      regardless of this erase (that's a native-function limitation
        //      this hook cannot fix); and
        //  (b) a future polyfill that renames/moves the definitions again.
        // The search now matches the polyfill's actual `?string $string`
        // signature (the old `mb_ucfirst($string` predated it) and targets the
        // real definition files instead of the dispatcher — validated to apply
        // cleanly and keep both files valid PHP.
        $this->print('Removing string restricted implementation of `mb_[u|l]cfirst()` in polyfill-mbstring.');
        foreach (self::MBSTRING_BOOTSTRAPS as $bootstrap) {
            $path = $this->getBundleDir() . '/' . $bootstrap;
            if (!is_file($path)) {
                continue;
            }
            $codeModifier = new CodeModifier($path, $this->getAuthor(), strict: false);
            $codeModifier->erase("no-mbfirst", ['mb_ucfirst(?string $string', 'mb_lcfirst(?string $string']);

            // array_any() is NOT provided by polyfill-mbstring (it's a
            // polyfill-php84 function) — this erase never matches here and is
            // kept only best-effort. It arguably belongs to a dedicated
            // symfony/polyfill-php84 hook if base-bundle's array_any global
            // ever needs to win over that polyfill on PHP < 8.4.
            $codeModifier->erase("no-array-any", ['array_any(array']);
        }
    }

    public function onPackageRemove(PackageEvent $event)
    {
        foreach (self::MBSTRING_BOOTSTRAPS as $bootstrap) {
            $path = $this->getBundleDir() . '/' . $bootstrap;
            if (!is_file($path)) {
                continue;
            }
            (new CodeModifier($path, $this->getAuthor()))->restore();
        }
    }
}
