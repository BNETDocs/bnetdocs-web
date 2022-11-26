<?php

namespace BNETDocs\Models;

class Credits extends ActiveUser
{
    public int $total_users;
    public array $top_contributors_by_comments;
    public array $top_contributors_by_documents;
    public array $top_contributors_by_news_posts;
    public array $top_contributors_by_packets;
    public array $top_contributors_by_servers;
}
