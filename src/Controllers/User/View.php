<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\Credits;
use \BNETDocs\Libraries\Document;
use \BNETDocs\Libraries\NewsPost;
use \BNETDocs\Libraries\Packet;
use \BNETDocs\Libraries\Server;
use \BNETDocs\Libraries\User;

class View extends \BNETDocs\Controllers\Base
{
  /**
   * Constructs a Controller, typically to initialize properties.
   */
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\User\View();
  }

  /**
   * Invoked by the Router class to handle the request.
   *
   * @param array|null $args The optional route arguments and any captured URI arguments.
   * @return boolean Whether the Router should invoke the configured View.
   */
  public function invoke(?array $args) : bool
  {
    $this->model->_responseCode = 404;
    $this->model->user_id = array_shift($args);

    // Try to get the user
    try { $this->model->user = new User($this->model->user_id); }
    catch (\Throwable) { $this->model->user = null; return true; }

    $this->model->_responseCode = 200;
    $this->model->user_id = $this->model->user->getId();
    $this->model->user_profile = ($this->model->user ? $this->model->user->getUserProfile() : null);

    // Summary of contributions
    $this->model->sum_comments = Credits::getTotalCommentsByUserId($this->model->user_id);
    $this->model->sum_documents = Credits::getTotalDocumentsByUserId($this->model->user_id);
    $this->model->sum_news_posts = Credits::getTotalNewsPostsByUserId($this->model->user_id);
    $this->model->sum_packets = Credits::getTotalPacketsByUserId($this->model->user_id);
    $this->model->sum_servers = Credits::getTotalServersByUserId($this->model->user_id);

    // Total number of contributions
    $this->model->contributions = 0;
    $this->model->contributions += $this->model->sum_comments;
    $this->model->contributions += $this->model->sum_documents;
    $this->model->contributions += $this->model->sum_news_posts;
    $this->model->contributions += $this->model->sum_packets;
    $this->model->contributions += $this->model->sum_servers;

    // References to the contributions
    $this->model->comments = ($this->model->sum_comments ?
      Comment::getCommentsByUserId($this->model->user_id, true) : null
    );
    $this->model->documents = ($this->model->sum_documents ?
      Document::getDocumentsByUserId($this->model->user_id) : null
    );
    $this->model->news_posts = ($this->model->sum_news_posts ?
      NewsPost::getNewsPostsByUserId($this->model->user_id): null
    );
    $this->model->packets = ($this->model->sum_packets ?
      Packet::getPacketsByUserId($this->model->user_id) : null
    );
    $this->model->servers = ($this->model->sum_servers ?
      Server::getServersByUserId($this->model->user_id) : null
    );

    // Process documents
    if ($this->model->documents)
    {
      // Alphabetically sort the documents
      usort($this->model->documents, function($a, $b){
        $a1 = $a->getTitle();
        $b1 = $b->getTitle();
        if ($a1 == $b1) return 0;
        return ($a1 < $b1 ? -1 : 1);
      });

      // Remove documents that are not published
      $i = count($this->model->documents) - 1;
      while ($i >= 0)
      {
        if (!($this->model->documents[$i]->isPublished())) unset($this->model->documents[$i]);
        --$i;
      }
    }

    // Process news posts
    if ($this->model->news_posts)
    {
      // Alphabetically sort the news posts
      usort($this->model->news_posts, function($a, $b){
        $a1 = $a->getTitle();
        $b1 = $b->getTitle();
        if ($a1 == $b1) return 0;
        return ($a1 < $b1 ? -1 : 1);
      });

      // Remove news posts that are not published
      $i = count($this->model->news_posts) - 1;
      while ($i >= 0)
      {
        if (!($this->model->news_posts[$i]->isPublished())) unset($this->model->news_posts[$i]);
        --$i;
      }
    }

    // Process packets
    if ($this->model->packets)
    {
      // Alphabetically sort the packets
      usort($this->model->packets, function($a, $b){
        $a1 = $a->getName();
        $b1 = $b->getName();
        if ($a1 == $b1) return 0;
        return ($a1 < $b1 ? -1 : 1);
      });

      // Remove packets that are not published
      $i = count($this->model->packets) - 1;
      while ($i >= 0)
      {
        if (!($this->model->packets[$i]->isPublished())) unset($this->model->packets[$i]);
        --$i;
      }
    }

    // Process servers
    if ($this->model->servers) {
      // Alphabetically sort the servers
      usort($this->model->servers, function($a, $b){
        $a1 = $a->getName();
        $b1 = $b->getName();
        if ($a1 == $b1) return 0;
        return ($a1 < $b1 ? -1 : 1);
      });
    }

    $this->model->_responseCode = ($this->model->user ? 200 : 404);
    return true;
  }
}
