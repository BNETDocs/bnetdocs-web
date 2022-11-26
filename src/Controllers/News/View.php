<?php

namespace BNETDocs\Controllers\News;

use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\NewsPost;
use \BNETDocs\Libraries\User;

class View extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\News\View();
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
    $this->model->news_post_id = (int) array_shift($args);

    try
    {
      $this->model->news_post = new NewsPost($this->model->news_post_id);
    }
    catch (\UnexpectedValueException)
    {
      $this->model->news_post = null;
    }

    // Don't show unpublished news posts to non-staff
    if ($this->model->news_post
      && !($this->model->news_post->getOptionsBitmask() & NewsPost::OPTION_PUBLISHED)
      && !$this->model->acl_allowed) $this->model->news_post = null;

    // Load comments
    if ($this->model->news_post) {
      $this->model->comments = Comment::getAll(
        Comment::PARENT_TYPE_NEWS_POST, $this->model->news_post_id
      );
    }

    $this->model->_responseCode = ($this->model->news_post ? 200 : 404);
    return true;
  }
}
