<?php

namespace BNETDocs\Tests\Libraries\EventLog;

use \BNETDocs\Libraries\EventLog\EventType;
use \BNETDocs\Libraries\EventLog\EventTypes;
use \PHPUnit\Framework\TestCase;
use \UnexpectedValueException;

class EventTypeTest extends TestCase
{
    // color() — representative cases for each color bucket

    public function testColorBlueForBlizzardVisit(): void
    {
        $this->assertSame(0x5865F2, EventType::color(EventTypes::BLIZZARD_VISIT));
    }

    public function testColorGrayForLogNote(): void
    {
        $this->assertSame(0x99AAB5, EventType::color(EventTypes::LOG_NOTE));
    }

    public function testColorGrayForSlackUnfurl(): void
    {
        $this->assertSame(0x99AAB5, EventType::color(EventTypes::SLACK_UNFURL));
    }

    public function testColorGreenForUserCreated(): void
    {
        $this->assertSame(0x57F287, EventType::color(EventTypes::USER_CREATED));
    }

    public function testColorGreenForDocumentCreated(): void
    {
        $this->assertSame(0x57F287, EventType::color(EventTypes::DOCUMENT_CREATED));
    }

    public function testColorGreenForServerCreated(): void
    {
        $this->assertSame(0x57F287, EventType::color(EventTypes::SERVER_CREATED));
    }

    public function testColorRedForUserDeleted(): void
    {
        $this->assertSame(0xED4245, EventType::color(EventTypes::USER_DELETED));
    }

    public function testColorRedForUserLogout(): void
    {
        $this->assertSame(0xED4245, EventType::color(EventTypes::USER_LOGOUT));
    }

    public function testColorRedForPacketDeleted(): void
    {
        $this->assertSame(0xED4245, EventType::color(EventTypes::PACKET_DELETED));
    }

    public function testColorYellowForUserEdited(): void
    {
        $this->assertSame(0xFEE75C, EventType::color(EventTypes::USER_EDITED));
    }

    public function testColorYellowForDocumentEdited(): void
    {
        $this->assertSame(0xFEE75C, EventType::color(EventTypes::DOCUMENT_EDITED));
    }

    public function testColorYellowForServerEdited(): void
    {
        $this->assertSame(0xFEE75C, EventType::color(EventTypes::SERVER_EDITED));
    }

    public function testColorDefaultsToGrayForUnknownId(): void
    {
        $this->assertSame(0x99AAB5, EventType::color(99999));
    }

    // __toString() — representative cases across all categories

    public function testToStringLogNote(): void
    {
        $e = new EventType(EventTypes::LOG_NOTE);
        $this->assertSame('Log Note', (string) $e);
    }

    public function testToStringSiteDeploy(): void
    {
        $e = new EventType(EventTypes::SITE_DEPLOY);
        $this->assertSame('Site Deploy', (string) $e);
    }

    public function testToStringUserCreated(): void
    {
        $e = new EventType(EventTypes::USER_CREATED);
        $this->assertSame('User Created', (string) $e);
    }

    public function testToStringUserLogin(): void
    {
        $e = new EventType(EventTypes::USER_LOGIN);
        $this->assertSame('User Login', (string) $e);
    }

    public function testToStringUserLogout(): void
    {
        $e = new EventType(EventTypes::USER_LOGOUT);
        $this->assertSame('User Logout', (string) $e);
    }

    public function testToStringNewsCreated(): void
    {
        $e = new EventType(EventTypes::NEWS_CREATED);
        $this->assertSame('News Post Created', (string) $e);
    }

    public function testToStringPacketEdited(): void
    {
        $e = new EventType(EventTypes::PACKET_EDITED);
        $this->assertSame('Packet Edited', (string) $e);
    }

    public function testToStringDocumentDeleted(): void
    {
        $e = new EventType(EventTypes::DOCUMENT_DELETED);
        $this->assertSame('Document Deleted', (string) $e);
    }

    public function testToStringCommentCreatedOnPacket(): void
    {
        $e = new EventType(EventTypes::COMMENT_CREATED_PACKET);
        $this->assertSame('Comment Created on Packet', (string) $e);
    }

    public function testToStringCommentEditedOnDocument(): void
    {
        $e = new EventType(EventTypes::COMMENT_EDITED_DOCUMENT);
        $this->assertSame('Comment Edited on Document', (string) $e);
    }

    public function testToStringCommentDeletedOnServer(): void
    {
        $e = new EventType(EventTypes::COMMENT_DELETED_SERVER);
        $this->assertSame('Comment Deleted on Server', (string) $e);
    }

    public function testToStringServerCreated(): void
    {
        $e = new EventType(EventTypes::SERVER_CREATED);
        $this->assertSame('Server Created', (string) $e);
    }

    public function testToStringSlackUnfurl(): void
    {
        $e = new EventType(EventTypes::SLACK_UNFURL);
        $this->assertSame('Slack Unfurl', (string) $e);
    }

    public function testToStringUnknownIdThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $e = new EventType(99999);
        (string) $e;
    }
}
