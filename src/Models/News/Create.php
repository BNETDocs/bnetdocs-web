<?php

namespace BNETDocs\Models\News;

class Create extends \BNETDocs\Models\ActiveUser implements \JsonSerializable
{
  public bool $acl_allowed = false;
  public ?\BNETDocs\Libraries\NewsCategory $category = null;
  public string $content = '';
  public mixed $error = 'INTERNAL_ERROR';
  public bool $markdown = false;
  public ?\BNETDocs\Libraries\NewsPost $news_post = null;
  public ?array $news_categories = null;
  public bool $rss_exempt = false;
  public string $title = '';

  public function jsonSerialize(): mixed
  {
    return \array_merge(parent::jsonSerialize(), [
      'acl_allowed' => $this->acl_allowed,
      'category' => $this->category,
      'content' => $this->content,
      'error' => $this->error,
      'markdown' => $this->markdown,
      'news_categories' => $this->news_categories,
      'rss_exempt' => $this->rss_exempt,
      'title' => $this->title,
    ]);
  }
}
