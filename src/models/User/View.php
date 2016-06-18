<?php

namespace BNETDocs\Models\User;

use \BNETDocs\Libraries\Model;

class View extends Model {

  public $biography;
  public $contributions;
  public $documents;
  public $facebook;
  public $github;
  public $instagram;
  public $news_posts;
  public $packets;
  public $profiledata;
  public $servers;
  public $skype;
  public $sum_documents;
  public $sum_news_posts;
  public $sum_packets;
  public $sum_servers;
  public $twitter;
  public $user;
  public $user_est;
  public $user_id;
  public $user_profile;
  public $user_session;
  public $website;

  public function __construct() {
    parent::__construct();
    $this->biography      = null;
    $this->contributions  = null;
    $this->documents      = null;
    $this->facebook       = null;
    $this->github         = null;
    $this->instagram      = null;
    $this->news_posts     = null;
    $this->packets        = null;
    $this->profiledata    = null;
    $this->servers        = null;
    $this->skype          = null;
    $this->sum_documents  = null;
    $this->sum_news_posts = null;
    $this->sum_packets    = null;
    $this->sum_servers    = null;
    $this->twitter        = null;
    $this->user           = null;
    $this->user_est       = null;
    $this->user_id        = null;
    $this->user_profile   = null;
    $this->user_session   = null;
    $this->website        = null;
  }

}
