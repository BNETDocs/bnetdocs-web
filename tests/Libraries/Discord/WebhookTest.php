<?php

namespace BNETDocs\Tests\Libraries\Discord;

use \BNETDocs\Libraries\Discord\Embed;
use \BNETDocs\Libraries\Discord\Webhook;
use \LengthException;
use \LogicException;
use \OverflowException;
use \PHPUnit\Framework\TestCase;

class WebhookTest extends TestCase
{
    private Webhook $webhook;

    protected function setUp(): void
    {
        $this->webhook = new Webhook('https://discord.example/webhook');
    }

    // Defaults

    public function testDefaultEmbedCountIsZero(): void
    {
        $this->assertSame(0, $this->webhook->embedCount());
    }

    // addEmbed / embedCount / hasEmbed / removeEmbed / removeAllEmbeds

    public function testAddEmbedIncrementsCount(): void
    {
        $this->webhook->addEmbed(new Embed());
        $this->assertSame(1, $this->webhook->embedCount());
    }

    public function testHasEmbedReturnsTrueAfterAdd(): void
    {
        $e = new Embed();
        $this->webhook->addEmbed($e);
        $this->assertTrue($this->webhook->hasEmbed($e));
    }

    public function testHasEmbedReturnsFalseForUnaddedEmbed(): void
    {
        $this->assertFalse($this->webhook->hasEmbed(new Embed()));
    }

    public function testRemoveEmbedDecrementsCount(): void
    {
        $e = new Embed();
        $this->webhook->addEmbed($e);
        $this->webhook->removeEmbed($e);
        $this->assertSame(0, $this->webhook->embedCount());
    }

    public function testRemoveAllEmbedsClearsAll(): void
    {
        $this->webhook->addEmbed(new Embed());
        $this->webhook->addEmbed(new Embed());
        $this->webhook->removeAllEmbeds();
        $this->assertSame(0, $this->webhook->embedCount());
    }

    public function testAddEmbedBeyondMaxThrowsOverflowException(): void
    {
        $this->expectException(OverflowException::class);
        for ($i = 0; $i <= Webhook::MAX_EMBEDS; $i++)
        {
            $this->webhook->addEmbed(new Embed());
        }
    }

    public function testAddEmbedWithContentSetThrowsLogicException(): void
    {
        $this->webhook->setContent('hello');
        $this->expectException(LogicException::class);
        $this->webhook->addEmbed(new Embed());
    }

    public function testAddEmbedWithFileContentsSetThrowsLogicException(): void
    {
        $this->webhook->setFileContents('file data');
        $this->expectException(LogicException::class);
        $this->webhook->addEmbed(new Embed());
    }

    // setContent / setFileContents conflicts

    public function testSetContentWithEmbedsThrowsLogicException(): void
    {
        $this->webhook->addEmbed(new Embed());
        $this->expectException(LogicException::class);
        $this->webhook->setContent('hello');
    }

    public function testSetFileContentsWithEmbedsThrowsLogicException(): void
    {
        $this->webhook->addEmbed(new Embed());
        $this->expectException(LogicException::class);
        $this->webhook->setFileContents('file data');
    }

    // setWebhookUrl

    public function testSetWebhookUrlAcceptsNonEmptyValue(): void
    {
        // must not throw
        $this->webhook->setWebhookUrl('https://discord.example/other-webhook');
        $this->addToAssertionCount(1);
    }

    public function testSetWebhookUrlWithEmptyValueThrowsLengthException(): void
    {
        $this->expectException(LengthException::class);
        $this->webhook->setWebhookUrl('');
    }

    // jsonSerialize

    public function testJsonSerializeDefaultStateOmitsEmptyFields(): void
    {
        $data = $this->webhook->jsonSerialize();
        $this->assertArrayNotHasKey('content', $data);
        $this->assertArrayNotHasKey('embeds', $data);
        $this->assertArrayNotHasKey('file', $data);
    }

    public function testJsonSerializeEmbedsPopulated(): void
    {
        $this->webhook->addEmbed(new Embed());
        $data = $this->webhook->jsonSerialize();
        $this->assertArrayHasKey('embeds', $data);
        $this->assertCount(1, $data['embeds']);
    }

    public function testJsonSerializeContentPopulated(): void
    {
        $this->webhook->setContent('hello world');
        $data = $this->webhook->jsonSerialize();
        $this->assertSame('hello world', $data['content']);
    }

    // Constants

    public function testMaxEmbedsConstant(): void
    {
        $this->assertSame(10, Webhook::MAX_EMBEDS);
    }

    // Regression: addEmbed/hasEmbed/removeEmbed must not raise any PHP error.
    // SplObjectStorage::attach()/contains()/detach() were deprecated in PHP 8.5, and this
    // application's global error handler (ExceptionHandler::errorHandler) treats every raised
    // error, including deprecations, as fatal and aborts the request with a 500 response. This
    // is what silently broke document saving and Discord event-log delivery in production, and
    // a silently-reported PHPUnit deprecation is not enough to catch that in CI, so these assert
    // directly that no error is raised.

    public function testAddEmbedDoesNotRaiseError(): void
    {
        $raised = null;
        set_error_handler(function (int $errno, string $errstr) use (&$raised): bool {
            $raised = $errstr;
            return true;
        });
        try
        {
            $this->webhook->addEmbed(new Embed());
        }
        finally
        {
            restore_error_handler();
        }
        $this->assertNull($raised, "addEmbed() raised a PHP error/deprecation: $raised");
    }

    public function testHasEmbedDoesNotRaiseError(): void
    {
        $e = new Embed();
        $this->webhook->addEmbed($e);

        $raised = null;
        set_error_handler(function (int $errno, string $errstr) use (&$raised): bool {
            $raised = $errstr;
            return true;
        });
        try
        {
            $this->webhook->hasEmbed($e);
        }
        finally
        {
            restore_error_handler();
        }
        $this->assertNull($raised, "hasEmbed() raised a PHP error/deprecation: $raised");
    }

    public function testRemoveEmbedDoesNotRaiseError(): void
    {
        $e = new Embed();
        $this->webhook->addEmbed($e);

        $raised = null;
        set_error_handler(function (int $errno, string $errstr) use (&$raised): bool {
            $raised = $errstr;
            return true;
        });
        try
        {
            $this->webhook->removeEmbed($e);
        }
        finally
        {
            restore_error_handler();
        }
        $this->assertNull($raised, "removeEmbed() raised a PHP error/deprecation: $raised");
    }
}
