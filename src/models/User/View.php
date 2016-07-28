<?php

namespace BNETDocs\Models\User;

use \BNETDocs\Libraries\Model;

class View extends Model {

  public $biography;
  public $contributions;
  public $documents;
  public $facebook;
  public $facebook_uri;
  public $github;
  public $github_uri;
  public $instagram;
  public $instagram_uri;
  public $news_posts;
  public $packets;
  public $profiledata;
  public $reddit;
  public $reddit_uri;
  public $servers;
  public $skype;
  public $skype_uri;
  public $steam_id;
  public $steam_uri;
  public $sum_documents;
  public $sum_news_posts;
  public $sum_packets;
  public $sum_servers;
  public $twitter;
  public $twitter_uri;
  public $user;
  public $user_est;
  public $user_id;
  public $user_profile;
  public $user_session;
  public $website;
  public $website_uri;

  public function __construct() {
    parent::__construct();
    $this->biography      = null;
    $this->contributions  = null;
    $this->documents      = null;
    $this->facebook       = null;
    $this->facebook_uri   = null;
    $this->github         = null;
    $this->github_uri     = null;
    $this->instagram      = null;
    $this->instagram_uri  = null;
    $this->news_posts     = null;
    $this->packets        = null;
    $this->profiledata    = null;
    $this->reddit         = null;
    $this->reddit_uri     = null;
    $this->servers        = null;
    $this->skype          = null;
    $this->skype_uri      = null;
    $this->steam_id       = null;
    $this->steam_uri      = null;
    $this->sum_documents  = null;
    $this->sum_news_posts = null;
    $this->sum_packets    = null;
    $this->sum_servers    = null;
    $this->twitter        = null;
    $this->twitter_uri    = null;
    $this->user           = null;
    $this->user_est       = null;
    $this->user_id        = null;
    $this->user_profile   = null;
    $this->user_session   = null;
    $this->website        = null;
    $this->website_uri    = null;
  }

}
