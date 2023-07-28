<?php
namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\User;

class Index extends \BNETDocs\Controllers\Base
{
  public const PAGINATION_LIMIT_DEF = 20; // The default amount of items per page.
  public const PAGINATION_LIMIT_MAX = 250; // The most amount of items per page.
  public const PAGINATION_LIMIT_MIN = 5; // The least amount of items per page.

  /**
   * Constructs a Controller, typically to initialize properties.
   */
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\User\Index();
  }

  /**
   * Invoked by the Router class to handle the request.
   *
   * @param array|null $args The optional route arguments and any captured URI arguments.
   * @return boolean Whether the Router should invoke the configured View.
   */
  public function invoke(?array $args): bool
  {
    $q = \BNETDocs\Libraries\Router::query();
    $this->model->order = $q['order'] ?? 'registered-desc';

    // Translate order value to SQL-compatible properties
    switch ($this->model->order)
    {
      case 'id-asc': $order = ['id', 'ASC']; break;
      case 'id-desc': $order = ['id', 'DESC']; break;
      case 'username-asc': $order = ['username', 'ASC']; break;
      case 'username-desc': $order = ['username', 'DESC']; break;
      case 'registered-asc': $order = ['created_datetime', 'ASC']; break;
      case 'registered-desc': $order = ['created_datetime', 'DESC']; break;
      default: $order = null;
    }

    // Bounds checking
    $this->model->page = (int) ($q['page'] ?? null);
    $this->model->limit = (int) ($q['limit'] ?? self::PAGINATION_LIMIT_DEF);
    if ($this->model->limit < self::PAGINATION_LIMIT_MIN) $this->model->limit = self::PAGINATION_LIMIT_MIN;
    if ($this->model->limit > self::PAGINATION_LIMIT_MAX) $this->model->limit = self::PAGINATION_LIMIT_MAX;
    $this->model->pages = ceil(User::getUserCount() / $this->model->limit);
    if ($this->model->page < 1) $this->model->page = 1;
    if ($this->model->page > $this->model->pages) $this->model->page = $this->model->pages;

    // Get all by page
    $this->model->users = User::getAllUsers(
      $order,
      $this->model->limit, // limit per page
      $this->model->limit * ($this->model->page - 1) // page offset
    );

    // Post-filter summary of users
    $this->model->sum_users = count($this->model->users);

    $this->model->_responseCode = 200;
    return true;
  }
}
