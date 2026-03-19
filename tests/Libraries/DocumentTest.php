<?php

namespace BNETDocs\Tests\Libraries;

use \BNETDocs\Libraries\Document;
use \OutOfBoundsException;
use \PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    private Document $document;

    protected function setUp(): void
    {
        // null id → allocate() returns true early; no database access.
        $this->document = new Document(null);
    }

    // Default state

    public function testDefaultIdIsNull(): void
    {
        $this->assertNull($this->document->getId());
    }

    public function testDefaultBriefIsEmpty(): void
    {
        $this->assertSame('', $this->document->getBrief(false));
    }

    public function testDefaultContentIsEmpty(): void
    {
        $this->assertSame('', $this->document->getContent(false));
    }

    public function testDefaultEditedCountIsZero(): void
    {
        $this->assertSame(0, $this->document->getEditedCount());
    }

    public function testDefaultEditedDateTimeIsNull(): void
    {
        $this->assertNull($this->document->getEditedDateTime());
    }

    public function testDefaultOptionsIsMarkdown(): void
    {
        $this->assertSame(Document::DEFAULT_OPTION, $this->document->getOptions());
    }

    public function testDefaultTitleIsEmpty(): void
    {
        $this->assertSame('', $this->document->getTitle());
    }

    public function testDefaultUserIdIsNull(): void
    {
        $this->assertNull($this->document->getUserId());
    }

    // Boolean helpers — DEFAULT_OPTION = OPTION_MARKDOWN

    public function testDefaultIsMarkdownTrue(): void
    {
        $this->assertTrue($this->document->isMarkdown());
    }

    public function testDefaultIsPublishedFalse(): void
    {
        $this->assertFalse($this->document->isPublished());
    }

    // setMarkdown / setPublished

    public function testSetPublishedTrue(): void
    {
        $this->document->setPublished(true);
        $this->assertTrue($this->document->isPublished());
    }

    public function testSetPublishedFalse(): void
    {
        $this->document->setPublished(true);
        $this->document->setPublished(false);
        $this->assertFalse($this->document->isPublished());
    }

    public function testSetMarkdownFalse(): void
    {
        $this->document->setMarkdown(false);
        $this->assertFalse($this->document->isMarkdown());
    }

    public function testBothOptionsCoexist(): void
    {
        $this->document->setPublished(true);
        $this->assertTrue($this->document->isPublished());
        $this->assertTrue($this->document->isMarkdown()); // DEFAULT_OPTION still set
    }

    // getOption / setOption directly

    public function testGetOptionReturnsFalseForUnsetBit(): void
    {
        $this->assertFalse($this->document->getOption(Document::OPTION_PUBLISHED));
    }

    public function testGetOptionReturnsTrueAfterSetOption(): void
    {
        $this->document->setOption(Document::OPTION_PUBLISHED, true);
        $this->assertTrue($this->document->getOption(Document::OPTION_PUBLISHED));
    }

    // setBrief validation

    public function testSetBriefEmptyIsAllowed(): void
    {
        $this->document->setBrief('');
        $this->assertSame('', $this->document->getBrief(false));
    }

    public function testSetBriefAtMaxLengthIsAllowed(): void
    {
        $value = str_repeat('x', Document::MAX_BRIEF);
        $this->document->setBrief($value);
        $this->assertSame($value, $this->document->getBrief(false));
    }

    public function testSetBriefTooLongThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->document->setBrief(str_repeat('x', Document::MAX_BRIEF + 1));
    }

    // setContent validation

    public function testSetContentEmptyIsAllowed(): void
    {
        $this->document->setContent('');
        $this->assertSame('', $this->document->getContent(false));
    }

    public function testSetContentTooLongThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->document->setContent(str_repeat('x', Document::MAX_CONTENT + 1));
    }

    // setTitle validation

    public function testSetTitleEmptyIsAllowed(): void
    {
        $this->document->setTitle('');
        $this->assertSame('', $this->document->getTitle());
    }

    public function testSetTitleAtMaxLengthIsAllowed(): void
    {
        $value = str_repeat('x', Document::MAX_TITLE);
        $this->document->setTitle($value);
        $this->assertSame($value, $this->document->getTitle());
    }

    public function testSetTitleTooLongThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->document->setTitle(str_repeat('x', Document::MAX_TITLE + 1));
    }

    // incrementEdited

    public function testIncrementEditedUpdatesCount(): void
    {
        $this->document->incrementEdited();
        $this->assertSame(1, $this->document->getEditedCount());
    }

    public function testIncrementEditedSetsEditedDateTime(): void
    {
        $this->document->incrementEdited();
        $this->assertNotNull($this->document->getEditedDateTime());
    }

    // getPublishedDateTime fallback

    public function testGetPublishedDateTimeReturnsCreatedWhenNotEdited(): void
    {
        $this->assertSame(
            $this->document->getCreatedDateTime(),
            $this->document->getPublishedDateTime()
        );
    }

    public function testGetPublishedDateTimeReturnsEditedDateTimeAfterEdit(): void
    {
        $this->document->incrementEdited();
        $this->assertSame(
            $this->document->getEditedDateTime(),
            $this->document->getPublishedDateTime()
        );
    }

    // DB-free early-exit methods

    public function testGetUriIsNullWhenIdIsNull(): void
    {
        $this->assertNull($this->document->getURI());
    }

    public function testGetTagsIsEmptyWhenIdIsNull(): void
    {
        $this->assertSame([], $this->document->getTags());
    }

    public function testDeallocateReturnsFalseWhenIdIsNull(): void
    {
        $this->assertFalse($this->document->deallocate());
    }

    // jsonSerialize

    public function testJsonSerializeHasExpectedKeys(): void
    {
        $data = $this->document->jsonSerialize();
        foreach ([
            'brief', 'content', 'created_datetime', 'edited_count',
            'edited_datetime', 'id', 'options_bitmask', 'title', 'user_id',
        ] as $key) {
            $this->assertArrayHasKey($key, $data, "Missing key: $key");
        }
    }

    public function testJsonSerializeDefaultValues(): void
    {
        $data = $this->document->jsonSerialize();
        $this->assertNull($data['id']);
        $this->assertNull($data['edited_datetime']);
        $this->assertNull($data['user_id']);
        $this->assertSame('', $data['brief']);
        $this->assertSame('', $data['content']);
        $this->assertSame(0, $data['edited_count']);
        $this->assertSame(Document::DEFAULT_OPTION, $data['options_bitmask']);
        $this->assertSame('', $data['title']);
    }
}
