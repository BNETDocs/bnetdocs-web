<?php

namespace BNETDocs\Models\User;

class View extends \BNETDocs\Models\ActiveUser implements \JsonSerializable
{
  public $comments;
  public $contributions;
  public $documents;
  public $news_posts;
  public $packets;
  public $servers;
  public $sum_comments;
  public $sum_documents;
  public $sum_news_posts;
  public $sum_packets;
  public $sum_servers;
  public $user;
  public $user_id;
  public $user_profile;

  public function jsonSerialize(): mixed
  {
    $r = \array_merge(parent::jsonSerialize(), [
      'comments' => $this->comments,
      'contributions' => $this->contributions,
      'documents' => $this->documents,
      'news_posts' => $this->news_posts,
      'packets' => $this->packets,
      'servers' => $this->servers,
      'sum_comments' => $this->sum_comments,
      'sum_documents' => $this->sum_documents,
      'sum_news_posts' => $this->sum_news_posts,
      'sum_packets' => $this->sum_packets,
      'sum_servers' => $this->sum_servers,
      'user' => $this->user,
      'user_id' => $this->user_id,
      'user_profile' => $this->user_profile,
    ]);
    ksort($r);
    return $r;
  }
}
