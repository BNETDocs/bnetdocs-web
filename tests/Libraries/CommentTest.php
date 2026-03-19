<?php

namespace BNETDocs\Tests\Libraries;

use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\EventLog\EventTypes;
use \PHPUnit\Framework\TestCase;
use \UnexpectedValueException;

class CommentTest extends TestCase
{
    private Comment $comment;

    protected function setUp(): void
    {
        // null id → allocate() returns true early; no database access.
        $this->comment = new Comment(null);
    }

    // Default state

    public function testDefaultIdIsNull(): void
    {
        $this->assertNull($this->comment->getId());
    }

    public function testDefaultContentIsEmpty(): void
    {
        $this->assertSame('', $this->comment->getContent(false));
    }

    public function testDefaultEditedCountIsZero(): void
    {
        $this->assertSame(0, $this->comment->getEditedCount());
    }

    public function testDefaultEditedDateTimeIsNull(): void
    {
        $this->assertNull($this->comment->getEditedDateTime());
    }

    public function testDefaultParentIdIsZero(): void
    {
        $this->assertSame(0, $this->comment->getParentId());
    }

    public function testDefaultParentTypeIsComment(): void
    {
        $this->assertSame(Comment::PARENT_TYPE_COMMENT, $this->comment->getParentType());
    }

    public function testDefaultUserIdIsNull(): void
    {
        $this->assertNull($this->comment->getUserId());
    }

    // PARENT_TYPE_* constant values

    public function testParentTypeCommentIsZero(): void
    {
        $this->assertSame(0, Comment::PARENT_TYPE_COMMENT);
    }

    public function testParentTypeDocumentIsOne(): void
    {
        $this->assertSame(1, Comment::PARENT_TYPE_DOCUMENT);
    }

    public function testParentTypeNewsPostIsTwo(): void
    {
        $this->assertSame(2, Comment::PARENT_TYPE_NEWS_POST);
    }

    public function testParentTypePacketIsThree(): void
    {
        $this->assertSame(3, Comment::PARENT_TYPE_PACKET);
    }

    public function testParentTypeServerIsFour(): void
    {
        $this->assertSame(4, Comment::PARENT_TYPE_SERVER);
    }

    public function testParentTypeUserIsFive(): void
    {
        $this->assertSame(5, Comment::PARENT_TYPE_USER);
    }

    // validateParentType static

    public function testValidateParentTypeAllSixAreValid(): void
    {
        foreach ([
            Comment::PARENT_TYPE_COMMENT,
            Comment::PARENT_TYPE_DOCUMENT,
            Comment::PARENT_TYPE_NEWS_POST,
            Comment::PARENT_TYPE_PACKET,
            Comment::PARENT_TYPE_SERVER,
            Comment::PARENT_TYPE_USER,
        ] as $type) {
            $this->assertTrue(Comment::validateParentType($type), "Expected type $type to be valid");
        }
    }

    public function testValidateParentTypeUnknownReturnsFalse(): void
    {
        $this->assertFalse(Comment::validateParentType(99));
    }

    public function testValidateParentTypeNegativeThrowsUnexpectedValue(): void
    {
        $this->expectException(UnexpectedValueException::class);
        Comment::validateParentType(-1);
    }

    // setParentType

    public function testSetParentTypeDocumentIsAllowed(): void
    {
        $this->comment->setParentType(Comment::PARENT_TYPE_DOCUMENT);
        $this->assertSame(Comment::PARENT_TYPE_DOCUMENT, $this->comment->getParentType());
    }

    public function testSetParentTypeInvalidThrowsUnexpectedValue(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->comment->setParentType(99);
    }

    // getParentTypeCreatedEventId — all six parent types

    public function testCreatedEventIdForComment(): void
    {
        $this->comment->setParentType(Comment::PARENT_TYPE_COMMENT);
        $this->assertSame(EventTypes::COMMENT_CREATED_COMMENT, $this->comment->getParentTypeCreatedEventId());
    }

    public function testCreatedEventIdForDocument(): void
    {
        $this->comment->setParentType(Comment::PARENT_TYPE_DOCUMENT);
        $this->assertSame(EventTypes::COMMENT_CREATED_DOCUMENT, $this->comment->getParentTypeCreatedEventId());
    }

    public function testCreatedEventIdForNewsPost(): void
    {
        $this->comment->setParentType(Comment::PARENT_TYPE_NEWS_POST);
        $this->assertSame(EventTypes::COMMENT_CREATED_NEWS, $this->comment->getParentTypeCreatedEventId());
    }

    public function testCreatedEventIdForPacket(): void
    {
        $this->comment->setParentType(Comment::PARENT_TYPE_PACKET);
        $this->assertSame(EventTypes::COMMENT_CREATED_PACKET, $this->comment->getParentTypeCreatedEventId());
    }

    public function testCreatedEventIdForServer(): void
    {
        $this->comment->setParentType(Comment::PARENT_TYPE_SERVER);
        $this->assertSame(EventTypes::COMMENT_CREATED_SERVER, $this->comment->getParentTypeCreatedEventId());
    }

    public function testCreatedEventIdForUser(): void
    {
        $this->comment->setParentType(Comment::PARENT_TYPE_USER);
        $this->assertSame(EventTypes::COMMENT_CREATED_USER, $this->comment->getParentTypeCreatedEventId());
    }

    // getParentTypeEditedEventId — all six parent types

    public function testEditedEventIdForComment(): void
    {
        $this->comment->setParentType(Comment::PARENT_TYPE_COMMENT);
        $this->assertSame(EventTypes::COMMENT_EDITED_COMMENT, $this->comment->getParentTypeEditedEventId());
    }

    public function testEditedEventIdForDocument(): void
    {
        $this->comment->setParentType(Comment::PARENT_TYPE_DOCUMENT);
        $this->assertSame(EventTypes::COMMENT_EDITED_DOCUMENT, $this->comment->getParentTypeEditedEventId());
    }

    public function testEditedEventIdForNewsPost(): void
    {
        $this->comment->setParentType(Comment::PARENT_TYPE_NEWS_POST);
        $this->assertSame(EventTypes::COMMENT_EDITED_NEWS, $this->comment->getParentTypeEditedEventId());
    }

    public function testEditedEventIdForPacket(): void
    {
        $this->comment->setParentType(Comment::PARENT_TYPE_PACKET);
        $this->assertSame(EventTypes::COMMENT_EDITED_PACKET, $this->comment->getParentTypeEditedEventId());
    }

    public function testEditedEventIdForServer(): void
    {
        $this->comment->setParentType(Comment::PARENT_TYPE_SERVER);
        $this->assertSame(EventTypes::COMMENT_EDITED_SERVER, $this->comment->getParentTypeEditedEventId());
    }

    public function testEditedEventIdForUser(): void
    {
        $this->comment->setParentType(Comment::PARENT_TYPE_USER);
        $this->assertSame(EventTypes::COMMENT_EDITED_USER, $this->comment->getParentTypeEditedEventId());
    }

    // getParentTypeDeletedEventId — all six parent types

    public function testDeletedEventIdForComment(): void
    {
        $this->comment->setParentType(Comment::PARENT_TYPE_COMMENT);
        $this->assertSame(EventTypes::COMMENT_DELETED_COMMENT, $this->comment->getParentTypeDeletedEventId());
    }

    public function testDeletedEventIdForDocument(): void
    {
        $this->comment->setParentType(Comment::PARENT_TYPE_DOCUMENT);
        $this->assertSame(EventTypes::COMMENT_DELETED_DOCUMENT, $this->comment->getParentTypeDeletedEventId());
    }

    public function testDeletedEventIdForNewsPost(): void
    {
        $this->comment->setParentType(Comment::PARENT_TYPE_NEWS_POST);
        $this->assertSame(EventTypes::COMMENT_DELETED_NEWS, $this->comment->getParentTypeDeletedEventId());
    }

    public function testDeletedEventIdForPacket(): void
    {
        $this->comment->setParentType(Comment::PARENT_TYPE_PACKET);
        $this->assertSame(EventTypes::COMMENT_DELETED_PACKET, $this->comment->getParentTypeDeletedEventId());
    }

    public function testDeletedEventIdForServer(): void
    {
        $this->comment->setParentType(Comment::PARENT_TYPE_SERVER);
        $this->assertSame(EventTypes::COMMENT_DELETED_SERVER, $this->comment->getParentTypeDeletedEventId());
    }

    public function testDeletedEventIdForUser(): void
    {
        $this->comment->setParentType(Comment::PARENT_TYPE_USER);
        $this->assertSame(EventTypes::COMMENT_DELETED_USER, $this->comment->getParentTypeDeletedEventId());
    }

    // setContent validation

    public function testSetContentAtMaxLengthIsAllowed(): void
    {
        $value = str_repeat('x', Comment::MAX_CONTENT);
        $this->comment->setContent($value);
        $this->assertSame($value, $this->comment->getContent(false));
    }

    public function testSetContentTooLongThrowsUnexpectedValue(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->comment->setContent(str_repeat('x', Comment::MAX_CONTENT + 1));
    }

    // incrementEdited

    public function testIncrementEditedUpdatesCount(): void
    {
        $this->comment->incrementEdited();
        $this->assertSame(1, $this->comment->getEditedCount());
    }

    public function testIncrementEditedSetsEditedDateTime(): void
    {
        $this->comment->incrementEdited();
        $this->assertNotNull($this->comment->getEditedDateTime());
    }

    // DB-free early-exit methods

    public function testGetTagsIsEmptyWhenIdIsNull(): void
    {
        $this->assertSame([], $this->comment->getTags());
    }

    public function testDeallocateReturnsFalseWhenIdIsNull(): void
    {
        $this->assertFalse($this->comment->deallocate());
    }

    // jsonSerialize

    public function testJsonSerializeHasExpectedKeys(): void
    {
        $data = $this->comment->jsonSerialize();
        foreach ([
            'content', 'created_datetime', 'edited_count', 'edited_datetime',
            'id', 'parent_id', 'parent_type', 'user_id',
        ] as $key) {
            $this->assertArrayHasKey($key, $data, "Missing key: $key");
        }
    }

    public function testJsonSerializeDefaultValues(): void
    {
        $data = $this->comment->jsonSerialize();
        $this->assertNull($data['id']);
        $this->assertNull($data['edited_datetime']);
        $this->assertNull($data['user_id']);
        $this->assertSame('', $data['content']);
        $this->assertSame(0, $data['edited_count']);
        $this->assertSame(0, $data['parent_id']);
        $this->assertSame(Comment::PARENT_TYPE_COMMENT, $data['parent_type']);
    }
}
