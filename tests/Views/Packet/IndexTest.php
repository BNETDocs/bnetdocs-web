<?php

namespace BNETDocs\Tests\Views\Packet;

use \BNETDocs\Libraries\Core\VersionInfo;
use \BNETDocs\Models\Packet\Index as IndexModel;
use \PHPUnit\Framework\Attributes\DataProvider;
use \PHPUnit\Framework\TestCase;
use \ReflectionClass;

class IndexTest extends TestCase
{
    protected function setUp(): void
    {
        // Pin the version so the copyright year is independent of git and config.
        (new ReflectionClass(VersionInfo::class))->getProperty('version')->setValue(null, [
            'asset' => '',
            'bnetdocs' => ['v1', str_repeat('a', 40), '2019-06-10T03:57:56-05:00', null],
            'php' => phpversion(),
        ]);
    }

    protected function tearDown(): void
    {
        (new ReflectionClass(VersionInfo::class))->getProperty('version')->setValue(null, null);
    }

    public static function viewProvider(): array
    {
        return [
            'Cpp'  => [\BNETDocs\Views\Packet\IndexCpp::class],
            'Go'   => [\BNETDocs\Views\Packet\IndexGo::class],
            'Java' => [\BNETDocs\Views\Packet\IndexJava::class],
            'Php'  => [\BNETDocs\Views\Packet\IndexPhp::class],
            'Vb'   => [\BNETDocs\Views\Packet\IndexVb::class],
        ];
    }

    #[DataProvider('viewProvider')]
    public function testCopyrightHeaderUsesVersionInfoYears(string $view): void
    {
        $model = new IndexModel();
        $model->packets = [];
        $model->timestamp = new \DateTimeImmutable('2019-06-10T03:57:56-05:00');

        ob_start();
        try
        {
            $view::invoke($model);
        }
        finally
        {
            $output = ob_get_clean();
        }

        $this->assertStringContainsString('Copyright (C) 2003-2019 "Arta"', $output);
    }
}
