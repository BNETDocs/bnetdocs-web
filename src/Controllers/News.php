<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\User;

class News extends Base
{
  public const NEWS_PER_PAGE = 5;

  /**
   * Constructs a Controller, typically to initialize properties.
   */
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\News();
  }

  /**
   * Invoked by the Router class to handle the request.
   *
   * @param array|null $args The optional route arguments and any captured URI arguments.
   * @return boolean Whether the Router should invoke the configured View.
   */
  public function invoke(?array $args) : bool
  {
    $this->model->acl_allowed = ($this->model->active_user && $this->model->active_user->getOption(
      User::OPTION_ACL_NEWS_CREATE |
      User::OPTION_ACL_NEWS_MODIFY |
      User::OPTION_ACL_NEWS_DELETE
    ));

    $q = \BNETDocs\Libraries\Router::query();
    $page = (isset($q['page']) ? ((int) $q['page']) - 1 : null);
    $rss = \array_shift($args);
    $this->model->news_posts = \BNETDocs\Libraries\NewsPost::getAllNews(true);

    // Remove news posts that are not published or are RSS exempt
    if ($this->model->news_posts)
    {
      $i = count($this->model->news_posts) - 1;
      while ($i >= 0)
      {
        if ((!$this->model->acl_allowed && !$this->model->news_posts[$i]->isPublished())
          || ($rss && $this->model->news_posts[$i]->isRSSExempt()))
          unset($this->model->news_posts[$i]);
        --$i;
      }
    }

    if (!$rss)
    {
      try
      {
        $this->model->pagination = new \BNETDocs\Libraries\Pagination(
          $this->model->news_posts, $page ?? 0, self::NEWS_PER_PAGE
        );
        $this->model->news_posts = $this->model->pagination->getPage();
      }
      catch (\OutOfBoundsException $e)
      {
        $this->model->news_posts = null;
      }
    }
    else
    {
      $this->model->pagination = null;
    }

    $this->model->_responseCode = 200;
    return true;
  }
}
