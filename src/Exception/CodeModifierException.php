<?php

namespace Base\Composer\Exception;

/**
 * Thrown when a STRICT patch (one that targets a specific anchor in a specific
 * file) is not already applied yet fails to match anything — i.e. the upstream
 * source drifted. This used to be a silent no-op (CodeModifier::callback() just
 * returned false while the hook still printed "success"), which is how a patch
 * can quietly stop applying after an upstream refactor and only surface as a
 * runtime break much later. Best-effort blanket passes (e.g. de-finalising
 * every file in a bundle) opt out via `strict: false`.
 */
class CodeModifierException extends \RuntimeException
{
    public static function targetNotFound(string $filePath, string $tag, string $search): self
    {
        return new self(sprintf(
            "CodeModifier: strict patch \"%s\" could not be applied to \"%s\" — its search anchor was not found:\n    %s\n"
            . "The targeted upstream code has most likely changed. This patch is NOT silently skipped; "
            . "update the hook's search string (or mark the patch best-effort with strict: false).",
            $tag,
            $filePath,
            trim(str_replace("\n", "\n    ", $search))
        ));
    }
}
