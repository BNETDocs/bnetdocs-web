<?php

namespace BNETDocs\Tests\Libraries\Discord;

use \BNETDocs\Libraries\Discord\Embed;
use \BNETDocs\Libraries\Discord\EmbedAuthor;
use \BNETDocs\Libraries\Discord\EmbedField;
use \BNETDocs\Libraries\Discord\EmbedFooter;
use \DateTimeImmutable;
use \DateTimeInterface;
use \DateTimeZone;
use \LengthException;
use \OverflowException;
use \PHPUnit\Framework\TestCase;
use \UnexpectedValueException;

class EmbedTest extends TestCase
{
    private Embed $embed;

    protected function setUp(): void
    {
        $this->embed = new Embed();
    }

    // Defaults

    public function testDefaultTypeIsRich(): void
    {
        $this->assertSame('rich', $this->embed->getType());
    }

    public function testDefaultColorIsNegativeOne(): void
    {
        $this->assertSame(-1, $this->embed->getColor());
    }

    public function testDefaultFieldCountIsZero(): void
    {
        $this->assertSame(0, $this->embed->fieldCount());
    }

    public function testDefaultAuthorIsNull(): void
    {
        $this->assertNull($this->embed->getAuthor());
    }

    public function testDefaultFooterIsNull(): void
    {
        $this->assertNull($this->embed->getFooter());
    }

    public function testDefaultTimestampIsNull(): void
    {
        $this->assertNull($this->embed->getTimestamp());
    }

    // setTitle / setDescription limits

    public function testSetTitleTooLongThrowsLengthException(): void
    {
        $this->expectException(LengthException::class);
        $this->embed->setTitle(str_repeat('x', Embed::MAX_TITLE + 1));
    }

    public function testSetTitleAtMaxLengthIsAllowed(): void
    {
        $this->embed->setTitle(str_repeat('x', Embed::MAX_TITLE));
        $this->assertSame(Embed::MAX_TITLE, strlen($this->embed->getTitle()));
    }

    public function testSetDescriptionTooLongThrowsLengthException(): void
    {
        $this->expectException(LengthException::class);
        $this->embed->setDescription(str_repeat('x', Embed::MAX_DESCRIPTION + 1));
    }

    public function testSetDescriptionAtMaxLengthIsAllowed(): void
    {
        $this->embed->setDescription(str_repeat('x', Embed::MAX_DESCRIPTION));
        $this->assertSame(Embed::MAX_DESCRIPTION, strlen($this->embed->getDescription()));
    }

    // addField / fieldCount / hasField / removeField / removeAllFields

    public function testAddFieldIncrementsCount(): void
    {
        $f = new EmbedField('Name', 'Value', true);
        $this->embed->addField($f);
        $this->assertSame(1, $this->embed->fieldCount());
    }

    public function testHasFieldReturnsTrueAfterAdd(): void
    {
        $f = new EmbedField('Name', 'Value', true);
        $this->embed->addField($f);
        $this->assertTrue($this->embed->hasField($f));
    }

    public function testHasFieldReturnsFalseForUnaddedField(): void
    {
        $f = new EmbedField('Name', 'Value', true);
        $this->assertFalse($this->embed->hasField($f));
    }

    public function testRemoveFieldDecrementsCount(): void
    {
        $f = new EmbedField('Name', 'Value', true);
        $this->embed->addField($f);
        $this->embed->removeField($f);
        $this->assertSame(0, $this->embed->fieldCount());
    }

    public function testRemoveAllFieldsClearsAll(): void
    {
        $this->embed->addField(new EmbedField('A', 'a', true));
        $this->embed->addField(new EmbedField('B', 'b', true));
        $this->embed->removeAllFields();
        $this->assertSame(0, $this->embed->fieldCount());
    }

    public function testAddFieldBeyondMaxThrowsOverflowException(): void
    {
        $this->expectException(OverflowException::class);
        for ($i = 0; $i <= Embed::MAX_FIELDS; $i++)
        {
            $this->embed->addField(new EmbedField("F$i", 'v', true));
        }
    }

    // addFields — array with string keys (scalar values)

    public function testAddFieldsWithStringKeyedArray(): void
    {
        $this->embed->addFields(['Field One' => 'Value One', 'Field Two' => 'Value Two']);
        $this->assertSame(2, $this->embed->fieldCount());
    }

    // addFields — array with EmbedField objects

    public function testAddFieldsWithEmbedFieldObjects(): void
    {
        $fields = [new EmbedField('N', 'V', true), new EmbedField('M', 'W', true)];
        $this->embed->addFields($fields);
        $this->assertSame(2, $this->embed->fieldCount());
    }

    // addFields — invalid entry

    public function testAddFieldsWithInvalidEntryThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->embed->addFields([['not', 'a', 'field']]);
    }

    // Regression: addField/hasField/removeField must not raise any PHP error.
    // SplObjectStorage::attach()/contains()/detach() were deprecated in PHP 8.5, and this
    // application's global error handler (ExceptionHandler::errorHandler) treats every raised
    // error, including deprecations, as fatal and aborts the request with a 500 response. A
    // silently-reported PHPUnit deprecation is not enough to catch that in CI, so these assert
    // directly that no error is raised.

    public function testAddFieldDoesNotRaiseError(): void
    {
        $raised = null;
        set_error_handler(function (int $errno, string $errstr) use (&$raised): bool {
            $raised = $errstr;
            return true;
        });
        try
        {
            $this->embed->addField(new EmbedField('Name', 'Value', true));
        }
        finally
        {
            restore_error_handler();
        }
        $this->assertNull($raised, "addField() raised a PHP error/deprecation: $raised");
    }

    public function testHasFieldDoesNotRaiseError(): void
    {
        $f = new EmbedField('Name', 'Value', true);
        $this->embed->addField($f);

        $raised = null;
        set_error_handler(function (int $errno, string $errstr) use (&$raised): bool {
            $raised = $errstr;
            return true;
        });
        try
        {
            $this->embed->hasField($f);
        }
        finally
        {
            restore_error_handler();
        }
        $this->assertNull($raised, "hasField() raised a PHP error/deprecation: $raised");
    }

    public function testRemoveFieldDoesNotRaiseError(): void
    {
        $f = new EmbedField('Name', 'Value', true);
        $this->embed->addField($f);

        $raised = null;
        set_error_handler(function (int $errno, string $errstr) use (&$raised): bool {
            $raised = $errstr;
            return true;
        });
        try
        {
            $this->embed->removeField($f);
        }
        finally
        {
            restore_error_handler();
        }
        $this->assertNull($raised, "removeField() raised a PHP error/deprecation: $raised");
    }

    // setAuthor / setFooter / setTimestamp

    public function testSetAuthorStored(): void
    {
        $author = new EmbedAuthor('Author Name');
        $this->embed->setAuthor($author);
        $this->assertSame($author, $this->embed->getAuthor());
    }

    public function testSetFooterStored(): void
    {
        $footer = new EmbedFooter('Footer text');
        $this->embed->setFooter($footer);
        $this->assertSame($footer, $this->embed->getFooter());
    }

    public function testSetTimestampStored(): void
    {
        $dt = new DateTimeImmutable('2024-01-01', new DateTimeZone('UTC'));
        $this->embed->setTimestamp($dt);
        $this->assertSame($dt, $this->embed->getTimestamp());
    }

    // jsonSerialize

    public function testJsonSerializeDefaultStateHasTypeAndFields(): void
    {
        $data = $this->embed->jsonSerialize();
        $this->assertArrayHasKey('type', $data);
        $this->assertArrayHasKey('fields', $data);
        $this->assertSame('rich', $data['type']);
        $this->assertSame([], $data['fields']);
    }

    public function testJsonSerializeDefaultColorAbsent(): void
    {
        // color=-1 is removed from the serialized output
        $data = $this->embed->jsonSerialize();
        $this->assertArrayNotHasKey('color', $data);
    }

    public function testJsonSerializeColorPresentWhenSet(): void
    {
        $this->embed->setColor(0xFF0000);
        $data = $this->embed->jsonSerialize();
        $this->assertArrayHasKey('color', $data);
        $this->assertSame(0xFF0000, $data['color']);
    }

    public function testJsonSerializeColorZeroIsPresent(): void
    {
        // color=0 is not -1, so it should appear in the output
        $this->embed->setColor(0);
        $data = $this->embed->jsonSerialize();
        $this->assertArrayHasKey('color', $data);
    }

    public function testJsonSerializeTimestampFormattedAsIso8601(): void
    {
        $dt = new DateTimeImmutable('2024-06-15 10:30:00', new DateTimeZone('UTC'));
        $this->embed->setTimestamp($dt);
        $data = $this->embed->jsonSerialize();
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertSame($dt->format(DateTimeInterface::ISO8601), $data['timestamp']);
    }

    public function testJsonSerializeNullPropertiesOmitted(): void
    {
        $data = $this->embed->jsonSerialize();
        $this->assertArrayNotHasKey('author', $data);
        $this->assertArrayNotHasKey('footer', $data);
        $this->assertArrayNotHasKey('image', $data);
        $this->assertArrayNotHasKey('thumbnail', $data);
        $this->assertArrayNotHasKey('timestamp', $data);
        $this->assertArrayNotHasKey('video', $data);
    }

    public function testJsonSerializeFieldsPopulated(): void
    {
        $this->embed->addField(new EmbedField('Key', 'Val', true));
        $data = $this->embed->jsonSerialize();
        $this->assertCount(1, $data['fields']);
    }

    // Constants

    public function testMaxDescriptionConstant(): void
    {
        $this->assertSame(2048, Embed::MAX_DESCRIPTION);
    }

    public function testMaxFieldsConstant(): void
    {
        $this->assertSame(25, Embed::MAX_FIELDS);
    }

    public function testMaxTitleConstant(): void
    {
        $this->assertSame(256, Embed::MAX_TITLE);
    }
}
