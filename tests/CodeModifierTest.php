<?php

declare(strict_types=1);

namespace Base\Composer\Tests;

use Base\Composer\CodeModifier;
use Base\Composer\Exception\CodeModifierException;
use PHPUnit\Framework\TestCase;

/**
 * base-plugin rewrites third-party vendor source in place. These pin the
 * behaviours that matter: a STRICT patch (specific anchor, specific file) must
 * fail loudly when the anchor drifts rather than silently no-op'ing, while a
 * best-effort blanket pass (strict: false — de-finalise every file in a bundle,
 * most of which legitimately contain no match) stays quiet.
 */
class CodeModifierTest extends TestCase
{
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $f) {
            @unlink($f);
            @unlink($f . '.bak');
        }
        $this->tempFiles = [];
    }

    private function tempPhp(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'cm') . '.php';
        file_put_contents($path, $contents);
        $this->tempFiles[] = $path;

        return $path;
    }

    public function testReplaceRewritesTheMatchAndWrapsTheOriginalInMarkers(): void
    {
        $path = $this->tempPhp("<?php\nfinal class Foo {}\n");

        $applied = (new CodeModifier($path, 'test'))->replace('non-final', 'final class ', 'class ');

        $this->assertTrue($applied);
        $out = file_get_contents($path);
        $this->assertStringContainsString('class Foo', $out);
        $this->assertStringContainsString('[bootstrap:non-final@', $out);
        $this->assertSame('', trim((string) shell_exec('php -l ' . escapeshellarg($path) . ' 2>&1 >/dev/null')));
    }

    public function testStrictReplaceThrowsWhenTheAnchorIsMissing(): void
    {
        $path = $this->tempPhp("<?php\nclass Plain {}\n");

        $this->expectException(CodeModifierException::class);
        $this->expectExceptionMessageMatches('/could not be applied/');

        (new CodeModifier($path, 'test'))->replace('non-final', 'final class ', 'class ');
    }

    /**
     * The blanket-pass escape hatch: over a whole bundle, most files contain no
     * `final`, so strict: false must return false quietly, never throw.
     */
    public function testNonStrictReplaceIsAQuietNoOpWhenNothingMatches(): void
    {
        $path = $this->tempPhp("<?php\nclass Plain {}\n");

        $result = (new CodeModifier($path, 'test', false, null, false))->replace('non-final', 'final class ', 'class ');

        $this->assertFalse($result);
        $this->assertSame("<?php\nclass Plain {}\n", file_get_contents($path));
    }

    public function testReplaceIsIdempotent(): void
    {
        $path = $this->tempPhp("<?php\nfinal class Foo {}\n");

        $modifier = new CodeModifier($path, 'test');
        $this->assertTrue($modifier->replace('non-final', 'final class ', 'class '));
        $afterFirst = file_get_contents($path);

        $this->assertFalse($modifier->replace('non-final', 'final class ', 'class '));
        $this->assertSame($afterFirst, file_get_contents($path));
    }

    public function testRestoreReturnsTheFileToItsPristineState(): void
    {
        $original = "<?php\nfinal class Foo { private \$x = 1; }\n";
        $path = $this->tempPhp($original);

        $modifier = new CodeModifier($path, 'test');
        $modifier->replace('non-final', 'final class ', 'class ');
        $this->assertNotSame($original, file_get_contents($path));

        $modifier->restore();
        $this->assertSame($original, file_get_contents($path));
    }

    public function testAppendToThrowsInStrictModeWhenTheMethodIsMissing(): void
    {
        $path = $this->tempPhp("<?php\nnamespace App;\nclass Svc { public function other() {} }\n");

        $this->expectException(CodeModifierException::class);

        (new CodeModifier($path, 'test'))->appendTo('x', '\\App\\Svc::missingMethod', 'public function injected() {}');
    }
}
