<?php

namespace BNETDocs\Tests\Exceptions;

use \BNETDocs\Exceptions\CommentNotFoundException;
use \BNETDocs\Exceptions\ControllerNotFoundException;
use \BNETDocs\Exceptions\DocumentNotFoundException;
use \BNETDocs\Exceptions\EventNotFoundException;
use \BNETDocs\Exceptions\NewsCategoryNotFoundException;
use \BNETDocs\Exceptions\NewsPostNotFoundException;
use \BNETDocs\Exceptions\PacketNotFoundException;
use \BNETDocs\Exceptions\ProductNotFoundException;
use \BNETDocs\Exceptions\ServerNotFoundException;
use \BNETDocs\Exceptions\TemplateNotFoundException;
use \BNETDocs\Exceptions\UserNotFoundException;
use \BNETDocs\Exceptions\UserProfileNotFoundException;
use \Exception;
use \PHPUnit\Framework\TestCase;
use \UnexpectedValueException;

/**
 * Regression coverage for a production incident (2026-09-23): every one of these classes
 * declared its optional `$previous` parameter as `\Throwable $previous = null` instead of
 * `?\Throwable $previous = null`. Implicitly marking a parameter nullable this way has been
 * deprecated since PHP 8.4, and production (PHP 8.5) raises E_DEPRECATED the moment any of
 * these constructors runs — which is on every "not found" (404) code path. This application's
 * global error handler treats any raised error, including deprecations, as fatal (500 response),
 * so e.g. GET /document/999999 500'd instead of showing a 404. PHPUnit only reports deprecations
 * at the end of a run rather than failing on them, so these tests assert directly that no error
 * is raised by installing a temporary error handler around the constructor call.
 */
class NotFoundExceptionsTest extends TestCase
{
    public static function intValuedClassesProvider(): array
    {
        return [
            CommentNotFoundException::class      => [CommentNotFoundException::class, 1],
            DocumentNotFoundException::class     => [DocumentNotFoundException::class, 1],
            EventNotFoundException::class        => [EventNotFoundException::class, 1],
            NewsCategoryNotFoundException::class => [NewsCategoryNotFoundException::class, 1],
            NewsPostNotFoundException::class     => [NewsPostNotFoundException::class, 1],
            PacketNotFoundException::class       => [PacketNotFoundException::class, 1],
            ProductNotFoundException::class      => [ProductNotFoundException::class, 1],
            ServerNotFoundException::class       => [ServerNotFoundException::class, 1],
            UserNotFoundException::class         => [UserNotFoundException::class, 1],
            UserProfileNotFoundException::class  => [UserProfileNotFoundException::class, 1],
        ];
    }

    public static function stringValuedClassesProvider(): array
    {
        return [
            ControllerNotFoundException::class => [ControllerNotFoundException::class, 'Foo\\Bar'],
            TemplateNotFoundException::class   => [TemplateNotFoundException::class, 'Foo/Bar'],
        ];
    }

    /**
     * @dataProvider intValuedClassesProvider
     */
    public function testConstructWithOnlyValueRaisesNoError(string $class, int $value): void
    {
        $raised = null;
        set_error_handler(function (int $errno, string $errstr) use (&$raised): bool {
            $raised = $errstr;
            return true;
        });
        try
        {
            new $class($value);
        }
        finally
        {
            restore_error_handler();
        }
        $this->assertNull($raised, "$class raised a PHP error/deprecation: $raised");
    }

    /**
     * @dataProvider stringValuedClassesProvider
     */
    public function testConstructWithOnlyStringValueRaisesNoError(string $class, string $value): void
    {
        $raised = null;
        set_error_handler(function (int $errno, string $errstr) use (&$raised): bool {
            $raised = $errstr;
            return true;
        });
        try
        {
            new $class($value);
        }
        finally
        {
            restore_error_handler();
        }
        $this->assertNull($raised, "$class raised a PHP error/deprecation: $raised");
    }

    /**
     * @dataProvider intValuedClassesProvider
     */
    public function testConstructWithIntValueIsThrowable(string $class, int $value): void
    {
        $this->expectException(UnexpectedValueException::class);
        throw new $class($value);
    }

    /**
     * @dataProvider stringValuedClassesProvider
     */
    public function testConstructWithStringValueIsThrowable(string $class, string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);
        throw new $class($value);
    }

    /**
     * @dataProvider intValuedClassesProvider
     */
    public function testPreviousIsPreserved(string $class, int $value): void
    {
        $previous = new Exception('cause');
        $e = new $class($value, $previous);
        $this->assertSame($previous, $e->getPrevious());
    }
}
