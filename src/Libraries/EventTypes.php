<?php

namespace BNETDocs\Libraries;

class EventTypes
{
  public const LOG_NOTE = 0;
  public const SITE_DEPLOY = 1;
  public const BLIZZARD_VISIT = 2;
  public const EMAIL_SENT = 3;
  public const USER_CREATED = 4;
  public const USER_EDITED = 5;
  public const USER_DELETED = 6;
  public const USER_LOGIN = 7;
  public const USER_LOGOUT = 8;
  public const USER_PASSWORD_CHANGE = 9;
  public const USER_PASSWORD_RESET = 10;
  public const USER_EMAIL_CHANGE = 11;
  public const USER_VERIFIED = 12;
  public const NEWS_CREATED = 13;
  public const NEWS_EDITED = 14;
  public const NEWS_DELETED = 15;
  public const PACKET_CREATED = 16;
  public const PACKET_EDITED = 17;
  public const PACKET_DELETED = 18;
  public const DOCUMENT_CREATED = 19;
  public const DOCUMENT_EDITED = 20;
  public const DOCUMENT_DELETED = 21;
  public const COMMENT_CREATED_NEWS = 22;
  public const COMMENT_CREATED_PACKET = 23;
  public const COMMENT_CREATED_DOCUMENT = 24;
  public const COMMENT_CREATED_USER = 25;
  public const COMMENT_EDITED_NEWS = 26;
  public const COMMENT_EDITED_PACKET = 27;
  public const COMMENT_EDITED_DOCUMENT = 28;
  public const COMMENT_EDITED_USER = 29;
  public const COMMENT_DELETED_NEWS = 30;
  public const COMMENT_DELETED_PACKET = 31;
  public const COMMENT_DELETED_DOCUMENT = 32;
  public const COMMENT_DELETED_USER = 33;
  public const COMMENT_CREATED_SERVER = 34;
  public const COMMENT_CREATED_COMMENT = 35;
  public const COMMENT_EDITED_SERVER = 36;
  public const COMMENT_EDITED_COMMENT = 37;
  public const COMMENT_DELETED_SERVER = 38;
  public const COMMENT_DELETED_COMMENT = 39;
  public const SERVER_CREATED = 40;
  public const SERVER_EDITED = 41;
  public const SERVER_DELETED = 42;
  public const SLACK_UNFURL = 43;
}
