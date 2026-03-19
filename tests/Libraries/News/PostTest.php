<?php

namespace BNETDocs\Tests\Libraries\News;

use \BNETDocs\Libraries\News\Post;
use \OutOfBoundsException;
use \PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
    private Post $post;

    protected function setUp(): void
    {
        // null id → allocate() returns true early; no database access.
        $this->post = new Post(null);
    }

    // Default state

    public function testDefaultIdIsNull(): void
    {
        $this->assertNull($this->post->getId());
    }

    public function testDefaultCategoryIdIsZero(): void
    {
        $this->assertSame(0, $this->post->getCategoryId());
    }

    public function testDefaultContentIsEmpty(): void
    {
        $this->assertSame('', $this->post->getContent(false));
    }

    public function testDefaultEditedCountIsZero(): void
    {
        $this->assertSame(0, $this->post->getEditedCount());
    }

    public function testDefaultEditedDateTimeIsNull(): void
    {
        $this->assertNull($this->post->getEditedDateTime());
    }

    public function testDefaultOptionsBitmaskIsDefaultOption(): void
    {
        $this->assertSame(Post::DEFAULT_OPTION, $this->post->getOptionsBitmask());
    }

    public function testDefaultTitleIsEmpty(): void
    {
        $this->assertSame('', $this->post->getTitle());
    }

    public function testDefaultUserIdIsNull(): void
    {
        $this->assertNull($this->post->getUserId());
    }

    // Boolean helpers — DEFAULT_OPTION = OPTION_MARKDOWN | OPTION_RSS_EXEMPT

    public function testDefaultIsMarkdownTrue(): void
    {
        $this->assertTrue($this->post->isMarkdown());
    }

    public function testDefaultIsRSSExemptTrue(): void
    {
        $this->assertTrue($this->post->isRSSExempt());
    }

    public function testDefaultIsPublishedFalse(): void
    {
        $this->assertFalse($this->post->isPublished());
    }

    // Setter helpers

    public function testSetPublishedTrue(): void
    {
        $this->post->setPublished(true);
        $this->assertTrue($this->post->isPublished());
    }

    public function testSetPublishedFalse(): void
    {
        $this->post->setPublished(true);
        $this->post->setPublished(false);
        $this->assertFalse($this->post->isPublished());
    }

    public function testSetMarkdownFalse(): void
    {
        $this->post->setMarkdown(false);
        $this->assertFalse($this->post->isMarkdown());
    }

    public function testSetRSSExemptFalse(): void
    {
        $this->post->setRSSExempt(false);
        $this->assertFalse($this->post->isRSSExempt());
    }

    public function testMultipleOptionsCoexist(): void
    {
        $this->post->setPublished(true);
        $this->assertTrue($this->post->isPublished());
        $this->assertTrue($this->post->isMarkdown());   // still set from DEFAULT_OPTION
        $this->assertTrue($this->post->isRSSExempt()); // still set from DEFAULT_OPTION
    }

    // setTitle validation

    public function testSetTitleEmptyIsAllowed(): void
    {
        $this->post->setTitle('');
        $this->assertSame('', $this->post->getTitle());
    }

    public function testSetTitleAtMaxLengthIsAllowed(): void
    {
        $value = str_repeat('x', Post::MAX_TITLE);
        $this->post->setTitle($value);
        $this->assertSame($value, $this->post->getTitle());
    }

    public function testSetTitleTooLongThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->post->setTitle(str_repeat('x', Post::MAX_TITLE + 1));
    }

    // setCategoryId validation

    public function testSetCategoryIdZeroIsAllowed(): void
    {
        $this->post->setCategoryId(0);
        $this->assertSame(0, $this->post->getCategoryId());
    }

    public function testSetCategoryIdPositiveIsAllowed(): void
    {
        $this->post->setCategoryId(42);
        $this->assertSame(42, $this->post->getCategoryId());
    }

    public function testSetCategoryIdNegativeThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->post->setCategoryId(-1);
    }

    // setContent validation

    public function testSetContentEmptyIsAllowed(): void
    {
        $this->post->setContent('');
        $this->assertSame('', $this->post->getContent(false));
    }

    public function testSetContentTooLongThrowsOutOfBounds(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->post->setContent(str_repeat('x', Post::MAX_CONTENT + 1));
    }

    // incrementEdited

    public function testIncrementEditedUpdatesCount(): void
    {
        $this->post->incrementEdited();
        $this->assertSame(1, $this->post->getEditedCount());
    }

    public function testIncrementEditedSetsEditedDateTime(): void
    {
        $this->post->incrementEdited();
        $this->assertNotNull($this->post->getEditedDateTime());
    }

    // getPublishedDateTime fallback

    public function testGetPublishedDateTimeReturnsCreatedWhenNotEdited(): void
    {
        $this->assertSame(
            $this->post->getCreatedDateTime(),
            $this->post->getPublishedDateTime()
        );
    }

    public function testGetPublishedDateTimeReturnsEditedDateTimeAfterEdit(): void
    {
        $this->post->incrementEdited();
        $this->assertSame(
            $this->post->getEditedDateTime(),
            $this->post->getPublishedDateTime()
        );
    }

    // DB-free early-exit methods

    public function testGetUriIsNullWhenIdIsNull(): void
    {
        $this->assertNull($this->post->getURI());
    }

    public function testGetTagsIsEmptyWhenIdIsNull(): void
    {
        $this->assertSame([], $this->post->getTags());
    }

    public function testDeallocateReturnsFalseWhenIdIsNull(): void
    {
        $this->assertFalse($this->post->deallocate());
    }
}
