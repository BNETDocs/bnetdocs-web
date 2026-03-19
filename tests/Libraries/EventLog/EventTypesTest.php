<?php

namespace BNETDocs\Tests\Libraries\EventLog;

use \BNETDocs\Libraries\EventLog\EventTypes;
use \PHPUnit\Framework\TestCase;

class EventTypesTest extends TestCase
{
    // Verify integer values of every constant so a renumbering is caught immediately.

    public function testLogNote(): void         { $this->assertSame(0,  EventTypes::LOG_NOTE); }
    public function testSiteDeploy(): void      { $this->assertSame(1,  EventTypes::SITE_DEPLOY); }
    public function testBlizzardVisit(): void   { $this->assertSame(2,  EventTypes::BLIZZARD_VISIT); }
    public function testEmailSent(): void       { $this->assertSame(3,  EventTypes::EMAIL_SENT); }
    public function testUserCreated(): void     { $this->assertSame(4,  EventTypes::USER_CREATED); }
    public function testUserEdited(): void      { $this->assertSame(5,  EventTypes::USER_EDITED); }
    public function testUserDeleted(): void     { $this->assertSame(6,  EventTypes::USER_DELETED); }
    public function testUserLogin(): void       { $this->assertSame(7,  EventTypes::USER_LOGIN); }
    public function testUserLogout(): void      { $this->assertSame(8,  EventTypes::USER_LOGOUT); }
    public function testUserPasswordChange(): void { $this->assertSame(9,  EventTypes::USER_PASSWORD_CHANGE); }
    public function testUserPasswordReset(): void  { $this->assertSame(10, EventTypes::USER_PASSWORD_RESET); }
    public function testUserEmailChange(): void    { $this->assertSame(11, EventTypes::USER_EMAIL_CHANGE); }
    public function testUserVerified(): void    { $this->assertSame(12, EventTypes::USER_VERIFIED); }
    public function testNewsCreated(): void     { $this->assertSame(13, EventTypes::NEWS_CREATED); }
    public function testNewsEdited(): void      { $this->assertSame(14, EventTypes::NEWS_EDITED); }
    public function testNewsDeleted(): void     { $this->assertSame(15, EventTypes::NEWS_DELETED); }
    public function testPacketCreated(): void   { $this->assertSame(16, EventTypes::PACKET_CREATED); }
    public function testPacketEdited(): void    { $this->assertSame(17, EventTypes::PACKET_EDITED); }
    public function testPacketDeleted(): void   { $this->assertSame(18, EventTypes::PACKET_DELETED); }
    public function testDocumentCreated(): void { $this->assertSame(19, EventTypes::DOCUMENT_CREATED); }
    public function testDocumentEdited(): void  { $this->assertSame(20, EventTypes::DOCUMENT_EDITED); }
    public function testDocumentDeleted(): void { $this->assertSame(21, EventTypes::DOCUMENT_DELETED); }
    public function testCommentCreatedNews(): void     { $this->assertSame(22, EventTypes::COMMENT_CREATED_NEWS); }
    public function testCommentCreatedPacket(): void   { $this->assertSame(23, EventTypes::COMMENT_CREATED_PACKET); }
    public function testCommentCreatedDocument(): void { $this->assertSame(24, EventTypes::COMMENT_CREATED_DOCUMENT); }
    public function testCommentCreatedUser(): void     { $this->assertSame(25, EventTypes::COMMENT_CREATED_USER); }
    public function testCommentEditedNews(): void      { $this->assertSame(26, EventTypes::COMMENT_EDITED_NEWS); }
    public function testCommentEditedPacket(): void    { $this->assertSame(27, EventTypes::COMMENT_EDITED_PACKET); }
    public function testCommentEditedDocument(): void  { $this->assertSame(28, EventTypes::COMMENT_EDITED_DOCUMENT); }
    public function testCommentEditedUser(): void      { $this->assertSame(29, EventTypes::COMMENT_EDITED_USER); }
    public function testCommentDeletedNews(): void     { $this->assertSame(30, EventTypes::COMMENT_DELETED_NEWS); }
    public function testCommentDeletedPacket(): void   { $this->assertSame(31, EventTypes::COMMENT_DELETED_PACKET); }
    public function testCommentDeletedDocument(): void { $this->assertSame(32, EventTypes::COMMENT_DELETED_DOCUMENT); }
    public function testCommentDeletedUser(): void     { $this->assertSame(33, EventTypes::COMMENT_DELETED_USER); }
    public function testCommentCreatedServer(): void   { $this->assertSame(34, EventTypes::COMMENT_CREATED_SERVER); }
    public function testCommentCreatedComment(): void  { $this->assertSame(35, EventTypes::COMMENT_CREATED_COMMENT); }
    public function testCommentEditedServer(): void    { $this->assertSame(36, EventTypes::COMMENT_EDITED_SERVER); }
    public function testCommentEditedComment(): void   { $this->assertSame(37, EventTypes::COMMENT_EDITED_COMMENT); }
    public function testCommentDeletedServer(): void   { $this->assertSame(38, EventTypes::COMMENT_DELETED_SERVER); }
    public function testCommentDeletedComment(): void  { $this->assertSame(39, EventTypes::COMMENT_DELETED_COMMENT); }
    public function testServerCreated(): void   { $this->assertSame(40, EventTypes::SERVER_CREATED); }
    public function testServerEdited(): void    { $this->assertSame(41, EventTypes::SERVER_EDITED); }
    public function testServerDeleted(): void   { $this->assertSame(42, EventTypes::SERVER_DELETED); }
    public function testSlackUnfurl(): void     { $this->assertSame(43, EventTypes::SLACK_UNFURL); }

    // All constants are unique (no two share an integer value).
    public function testAllConstantsAreUnique(): void
    {
        $ref = new \ReflectionClass(EventTypes::class);
        $values = array_values($ref->getConstants());
        $this->assertSame(count($values), count(array_unique($values)));
    }

    // Total count — fails if a constant is added or removed without updating tests.
    public function testConstantCount(): void
    {
        $ref = new \ReflectionClass(EventTypes::class);
        $this->assertCount(44, $ref->getConstants());
    }
}
