<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Controllers\Packet;

use \BNETDocs\Libraries\Comment;

class View extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\Packet\View();
  }

  public function invoke(?array $args): bool
  {
    $this->model->packet_id = (int) array_shift($args);

    try { $this->model->packet = new \BNETDocs\Libraries\Packet($this->model->packet_id); }
    catch (\UnexpectedValueException) { $this->model->packet = null; }

    if (!$this->model->packet)
    {
      $this->model->_responseCode = 404;
      return true;
    }

    $this->model->comments = Comment::getAll(Comment::PARENT_TYPE_PACKET, $this->model->packet_id);
    $this->model->used_by = \BNETDocs\Libraries\Product::getProductsFromIds($this->model->packet->getUsedBy());
    $this->model->_responseCode = 200;
    return true;
  }
}
