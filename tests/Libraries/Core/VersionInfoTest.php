<?php

namespace BNETDocs\Tests\Libraries\Core;

use \BNETDocs\Libraries\Core\Config;
use \BNETDocs\Libraries\Core\VersionInfo;
use \PHPUnit\Framework\Attributes\DataProvider;
use \PHPUnit\Framework\TestCase;
use \ReflectionClass;

class VersionInfoTest extends TestCase
{
    private const HASH = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

    private mixed $savedConfig = null;
    private ?string $savedVersionFile = null;

    protected function setUp(): void
    {
        // Provide just enough JSON config for VersionInfo::get() to avoid the database.
        $this->savedConfig = $this->getStatic(Config::class, 'json_config');
        $this->setStatic(Config::class, 'json_config', ['bnetdocs' => ['asset_versioning' => false]]);

        $this->setStatic(VersionInfo::class, 'version', null);

        // Preserve any real deployment version file the tests overwrite.
        $this->savedVersionFile = file_exists(VersionInfo::VERSION_INFO_FILE)
            ? file_get_contents(VersionInfo::VERSION_INFO_FILE) : null;
    }

    protected function tearDown(): void
    {
        if (is_null($this->savedVersionFile))
        {
            if (file_exists(VersionInfo::VERSION_INFO_FILE)) unlink(VersionInfo::VERSION_INFO_FILE);
        }
        else
        {
            file_put_contents(VersionInfo::VERSION_INFO_FILE, $this->savedVersionFile);
        }

        $this->setStatic(VersionInfo::class, 'version', null);
        $this->setStatic(Config::class, 'json_config', $this->savedConfig);
    }

    private function getStatic(string $class, string $name): mixed
    {
        return (new ReflectionClass($class))->getProperty($name)->getValue();
    }

    private function setStatic(string $class, string $name, mixed $value): void
    {
        (new ReflectionClass($class))->getProperty($name)->setValue(null, $value);
    }

    private function callPrivate(string $name, mixed ...$args): mixed
    {
        return (new ReflectionClass(VersionInfo::class))->getMethod($name)->invoke(null, ...$args);
    }

    private function setCachedVersion(?array $bnetdocs): void
    {
        $this->setStatic(VersionInfo::class, 'version', ['asset' => '', 'bnetdocs' => $bnetdocs, 'php' => phpversion()]);
    }

    private function git(string $args): string
    {
        return trim((string) shell_exec('git -C ' . escapeshellarg(VersionInfo::PROJECT_ROOT) . ' ' . $args));
    }

    // Copyright years

    public function testCopyrightStartYear(): void
    {
        $this->assertSame(2003, VersionInfo::COPYRIGHT_START_YEAR);
    }

    public function testCopyrightYearUsesCommitTimestamp(): void
    {
        $this->setCachedVersion(['v1', self::HASH, '2019-06-10T03:57:56-05:00', null]);
        $this->assertSame('2019', VersionInfo::copyrightYear());
    }

    public function testCopyrightYearUsesLocalYearOfTimestamp(): void
    {
        // The year as written in the timestamp, not converted to the server's time zone.
        $this->setCachedVersion(['v1', self::HASH, '2019-12-31T23:30:00-05:00', null]);
        $this->assertSame('2019', VersionInfo::copyrightYear());
    }

    public function testCopyrightYearFallsBackWithoutVersion(): void
    {
        $this->setCachedVersion(null);
        $this->assertSame(date('Y'), VersionInfo::copyrightYear());
    }

    public function testCopyrightYearFallsBackOnMalformedTimestamp(): void
    {
        $this->setCachedVersion(['v1', self::HASH, 'next week', null]);
        $this->assertSame(date('Y'), VersionInfo::copyrightYear());
    }

    public function testCopyrightYearFallsBackOnMissingTimestamp(): void
    {
        $this->setCachedVersion(['v1', self::HASH]);
        $this->assertSame(date('Y'), VersionInfo::copyrightYear());
    }

    public function testCopyrightYears(): void
    {
        $this->setCachedVersion(['v1', self::HASH, '2019-06-10T03:57:56-05:00', null]);
        $this->assertSame('2003-2019', VersionInfo::copyrightYears());
    }

    public function testCopyrightYearsFallsBackWithoutVersion(): void
    {
        $this->setCachedVersion(null);
        $this->assertSame('2003-' . date('Y'), VersionInfo::copyrightYears());
    }

    // isTimestamp

    public static function timestampProvider(): array
    {
        return [
            'negative offset'  => ['2026-09-23T21:56:30-05:00', true],
            'positive offset'  => ['2026-09-23T21:56:30+09:30', true],
            'utc designator'   => ['2026-09-23T21:56:30Z', true],
            'no offset'        => ['2026-09-23T21:56:30', false],
            'date only'        => ['2026-09-23', false],
            'space separator'  => ['2026-09-23 21:56:30-05:00', false],
            'trailing newline' => ["2026-09-23T21:56:30-05:00\n", false],
            'relative'         => ['next week', false],
            'empty'            => ['', false],
            'null'             => [null, false],
            'integer'          => [2026, false],
        ];
    }

    #[DataProvider('timestampProvider')]
    public function testIsTimestamp(mixed $value, bool $expected): void
    {
        $this->assertSame($expected, $this->callPrivate('isTimestamp', $value));
    }

    // deploymentVersion

    public function testDeploymentVersionMissingFile(): void
    {
        if (file_exists(VersionInfo::VERSION_INFO_FILE)) unlink(VersionInfo::VERSION_INFO_FILE);
        $this->assertNull($this->callPrivate('deploymentVersion'));
    }

    public function testDeploymentVersionEmptyFile(): void
    {
        file_put_contents(VersionInfo::VERSION_INFO_FILE, '');
        $this->assertNull($this->callPrivate('deploymentVersion'));
    }

    public function testDeploymentVersionValidFile(): void
    {
        // Same layout the deploy job writes, with no trailing newline.
        $lines = ['v1.2.3', self::HASH, '2026-09-23T21:56:30-05:00', '64ec662 2016-09-12T08:25:01Z'];
        file_put_contents(VersionInfo::VERSION_INFO_FILE, implode(VersionInfo::EOL, $lines));
        $this->assertSame($lines, $this->callPrivate('deploymentVersion'));
    }

    public static function malformedVersionFileProvider(): array
    {
        return [
            'too few lines'  => ["v1\n" . self::HASH],
            'bad hash'       => ["v1\nnot a hash\n2026-09-23T21:56:30-05:00\nx"],
            'short hash'     => ["v1\naaaaaaa\n2026-09-23T21:56:30-05:00\nx"],
            'windows eol'    => ["v1\r\n" . self::HASH . "\r\n2026-09-23T21:56:30-05:00\r\nx"],
            'bad timestamp'  => ["v1\n" . self::HASH . "\nnext week\nx"],
            'garbage'        => ["\x00\xff\xfe"],
        ];
    }

    #[DataProvider('malformedVersionFileProvider')]
    public function testDeploymentVersionRejectsMalformedFile(string $contents): void
    {
        file_put_contents(VersionInfo::VERSION_INFO_FILE, $contents);
        $this->assertNull($this->callPrivate('deploymentVersion'));
    }

    // reflectVersion

    public function testReflectVersionMatchesGit(): void
    {
        $values = $this->callPrivate('reflectVersion');
        $this->assertSame($this->git('rev-parse HEAD'), $values[1]);
        $this->assertSame($this->git("log -n 1 --pretty='%aI' HEAD"), $values[2]);
    }

    public function testReflectVersionIndependentOfWorkingDirectory(): void
    {
        $cwd = getcwd();
        chdir(sys_get_temp_dir());
        try
        {
            $values = $this->callPrivate('reflectVersion');
        }
        finally
        {
            chdir($cwd);
        }
        $this->assertSame($this->git('rev-parse HEAD'), $values[1]);
        // The LICENSE.txt lookup used a path relative to the working directory.
        $this->assertNotNull($values[3]);
    }

    // get

    public function testGetPrefersGitOverStaleVersionFile(): void
    {
        $this->assertFileExists(VersionInfo::PROJECT_ROOT . '/.git');
        file_put_contents(VersionInfo::VERSION_INFO_FILE,
            implode(VersionInfo::EOL, ['stale', self::HASH, '2019-01-01T00:00:00+00:00', 'x']));

        $version = VersionInfo::get()['bnetdocs'];
        $this->assertSame($this->git('rev-parse HEAD'), $version[1]);
        $this->assertSame(substr($this->git("log -n 1 --pretty='%aI' HEAD"), 0, 4), VersionInfo::copyrightYear());
    }
}
