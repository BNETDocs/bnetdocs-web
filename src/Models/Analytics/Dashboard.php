<?php

namespace BNETDocs\Models\Analytics;

use \DateInterval;
use \DateTimeInterface;

class Dashboard extends \BNETDocs\Models\Core\AccessControl implements \JsonSerializable
{
    public int $count_comments = 0;
    public int $count_documents = 0;
    public int $count_event_log = 0;
    public int $count_news_posts = 0;
    public int $count_packets = 0;
    public int $count_servers = 0;
    public int $count_tags = 0;
    public int $count_user_profiles = 0;
    public int $count_users = 0;
    public ?DateTimeInterface $date_end = null;
    public ?DateTimeInterface $date_start = null;
    public int $interval_new_comments = 0;
    public int $interval_new_documents = 0;
    public int $interval_new_event_log = 0;
    public int $interval_new_news_posts = 0;
    public int $interval_new_packets = 0;
    public int $interval_new_servers = 0;
    public int $interval_new_users = 0;

    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), [
            'count_comments' => $this->count_comments,
            'count_documents' => $this->count_documents,
            'count_event_log' => $this->count_event_log,
            'count_news_posts' => $this->count_news_posts,
            'count_packets' => $this->count_packets,
            'count_servers' => $this->count_servers,
            'count_tags' => $this->count_tags,
            'count_user_profiles' => $this->count_user_profiles,
            'count_users' => $this->count_users,
            'date_end' => $this->date_end,
            'date_start' => $this->date_start,
            'interval_new_comments' => $this->interval_new_comments,
            'interval_new_documents' => $this->interval_new_documents,
            'interval_new_event_log' => $this->interval_new_event_log,
            'interval_new_news_posts' => $this->interval_new_news_posts,
            'interval_new_packets' => $this->interval_new_packets,
            'interval_new_servers' => $this->interval_new_servers,
            'interval_new_users' => $this->interval_new_users,
        ]);
    }
}
