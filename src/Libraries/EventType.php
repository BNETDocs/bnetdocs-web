<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\EventTypes;

class EventType
{
  protected int $id;

  public function __construct(int $value)
  {
    $this->id = $value;
  }

  public function __toString()
  {
    switch ($this->id)
    {
      case EventTypes::LOG_NOTE:
        return 'Log Note';
      case EventTypes::SITE_DEPLOY:
        return 'Site Deploy';
      case EventTypes::BLIZZARD_VISIT:
        return 'Blizzard Visit';
      case EventTypes::EMAIL_SENT:
        return 'Email Sent';
      case EventTypes::USER_CREATED:
        return 'User Created';
      case EventTypes::USER_EDITED:
        return 'User Edited';
      case EventTypes::USER_DELETED:
        return 'User Deleted';
      case EventTypes::USER_LOGIN:
        return 'User Login';
      case EventTypes::USER_LOGOUT:
        return 'User Logout';
      case EventTypes::USER_PASSWORD_CHANGE:
        return 'User Password Change';
      case EventTypes::USER_PASSWORD_RESET:
        return 'User Password Reset';
      case EventTypes::USER_EMAIL_CHANGE:
        return 'User Email Change';
      case EventTypes::USER_VERIFIED:
        return 'User Verified';
      case EventTypes::NEWS_CREATED:
        return 'News Post Created';
      case EventTypes::NEWS_EDITED:
        return 'News Post Edited';
      case EventTypes::NEWS_DELETED:
        return 'News Post Deleted';
      case EventTypes::PACKET_CREATED:
        return 'Packet Created';
      case EventTypes::PACKET_EDITED:
        return 'Packet Edited';
      case EventTypes::PACKET_DELETED:
        return 'Packet Deleted';
      case EventTypes::DOCUMENT_CREATED:
        return 'Document Created';
      case EventTypes::DOCUMENT_EDITED:
        return 'Document Edited';
      case EventTypes::DOCUMENT_DELETED:
        return 'Document Deleted';
      case EventTypes::COMMENT_CREATED_NEWS:
        return 'Comment Created on News Post';
      case EventTypes::COMMENT_CREATED_PACKET:
        return 'Comment Created on Packet';
      case EventTypes::COMMENT_CREATED_DOCUMENT:
        return 'Comment Created on Document';
      case EventTypes::COMMENT_CREATED_USER:
        return 'Comment Created on User';
      case EventTypes::COMMENT_EDITED_NEWS:
        return 'Comment Edited on News Post';
      case EventTypes::COMMENT_EDITED_PACKET:
        return 'Comment Edited on Packet';
      case EventTypes::COMMENT_EDITED_DOCUMENT:
        return 'Comment Edited on Document';
      case EventTypes::COMMENT_EDITED_USER:
        return 'Comment Edited on User';
      case EventTypes::COMMENT_DELETED_NEWS:
        return 'Comment Deleted on News Post';
      case EventTypes::COMMENT_DELETED_PACKET:
        return 'Comment Deleted on Packet';
      case EventTypes::COMMENT_DELETED_DOCUMENT:
        return 'Comment Deleted on Document';
      case EventTypes::COMMENT_DELETED_USER:
        return 'Comment Deleted on User';
      case EventTypes::COMMENT_CREATED_SERVER:
        return 'Comment Created on Server';
      case EventTypes::COMMENT_CREATED_COMMENT:
        return 'Comment Created on Comment';
      case EventTypes::COMMENT_EDITED_SERVER:
        return 'Comment Edited on Server';
      case EventTypes::COMMENT_EDITED_COMMENT:
        return 'Comment Edited on Comment';
      case EventTypes::COMMENT_DELETED_SERVER:
        return 'Comment Deleted on Server';
      case EventTypes::COMMENT_DELETED_COMMENT:
        return 'Comment Deleted on Comment';
      case EventTypes::SERVER_CREATED:
        return 'Server Created';
      case EventTypes::SERVER_EDITED:
        return 'Server Edited';
      case EventTypes::SERVER_DELETED:
        return 'Server Deleted';
      case EventTypes::SLACK_UNFURL:
        return 'Slack Unfurl';
      default:
        throw new \UnexpectedValueException();
    }
  }
}
