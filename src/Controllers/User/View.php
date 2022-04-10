<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\Credits;
use \BNETDocs\Libraries\Document;
use \BNETDocs\Libraries\Exceptions\UserNotFoundException;
use \BNETDocs\Libraries\NewsPost;
use \BNETDocs\Libraries\Packet;
use \BNETDocs\Libraries\Server;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\User\View as UserViewModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View as ViewLib;
use \DateTime;
use \InvalidArgumentException;
use \UnexpectedValueException;

class View extends Controller
{
  public function &run(Router &$router, ViewLib &$view, array &$args)
  {
    $model = new UserViewModel();
    $model->active_user = Authentication::$user;
    $model->user_id = array_shift($args);

    $this->getUserInfo($model);

    $view->render($model);
    $model->_responseCode = ($model->user ? 200 : 404);
    return $model;
  }

  protected function getUserInfo(UserViewModel &$model)
  {
    // Try to get the user
    try { $model->user = new User($model->user_id); }
    catch (UserNotFoundException $e) { $model->user = null; return; }
    catch (InvalidArgumentException $e) { $model->user = null; return; }
    catch (UnexpectedValueException $e) { $model->user = null; return; }

    $model->user_profile = ($model->user ? $model->user->getUserProfile() : null);

    // Summary of contributions
    $model->sum_comments = Credits::getTotalCommentsByUserId($model->user_id);
    $model->sum_documents = Credits::getTotalDocumentsByUserId($model->user_id);
    $model->sum_news_posts = Credits::getTotalNewsPostsByUserId($model->user_id);
    $model->sum_packets = Credits::getTotalPacketsByUserId($model->user_id);
    $model->sum_servers = Credits::getTotalServersByUserId($model->user_id);

    // Total number of contributions
    $model->contributions = 0;
    $model->contributions += $model->sum_comments;
    $model->contributions += $model->sum_documents;
    $model->contributions += $model->sum_news_posts;
    $model->contributions += $model->sum_packets;
    $model->contributions += $model->sum_servers;

    // References to the contributions
    $model->comments = ($model->sum_comments ?
      Comment::getCommentsByUserId($model->user_id, true) : null
    );
    $model->documents = ($model->sum_documents ?
      Document::getDocumentsByUserId($model->user_id) : null
    );
    $model->news_posts = ($model->sum_news_posts ?
      NewsPost::getNewsPostsByUserId($model->user_id): null
    );
    $model->packets = ($model->sum_packets ?
      Packet::getPacketsByUserId($model->user_id) : null
    );
    $model->servers = ($model->sum_servers ?
      Server::getServersByUserId($model->user_id) : null
    );

    // Process documents
    if ($model->documents)
    {
      // Alphabetically sort the documents
      usort($model->documents, function($a, $b){
        $a1 = $a->getTitle();
        $b1 = $b->getTitle();
        if ($a1 == $b1) return 0;
        return ($a1 < $b1 ? -1 : 1);
      });

      // Remove documents that are not published
      $i = count($model->documents) - 1;
      while ($i >= 0)
      {
        if (!($model->documents[$i]->isPublished()))
        {
          unset($model->documents[$i]);
        }
        --$i;
      }
    }

    // Process news posts
    if ($model->news_posts)
    {
      // Alphabetically sort the news posts
      usort($model->news_posts, function($a, $b){
        $a1 = $a->getTitle();
        $b1 = $b->getTitle();
        if ($a1 == $b1) return 0;
        return ($a1 < $b1 ? -1 : 1);
      });

      // Remove news posts that are not published
      $i = count($model->news_posts) - 1;
      while ($i >= 0)
      {
        if (!($model->news_posts[$i]->isPublished()))
        {
          unset($model->news_posts[$i]);
        }
        --$i;
      }
    }

    // Process packets
    if ($model->packets)
    {
      // Alphabetically sort the packets
      usort($model->packets, function($a, $b){
        $a1 = $a->getName();
        $b1 = $b->getName();
        if ($a1 == $b1) return 0;
        return ($a1 < $b1 ? -1 : 1);
      });

      // Remove packets that are not published
      $i = count($model->packets) - 1;
      while ($i >= 0)
      {
        if (!($model->packets[$i]->isPublished()))
        {
          unset($model->packets[$i]);
        }
        --$i;
      }
    }

    // Process servers
    if ($model->servers) {
      // Alphabetically sort the servers
      usort($model->servers, function($a, $b){
        $a1 = $a->getName();
        $b1 = $b->getName();
        if ($a1 == $b1) return 0;
        return ($a1 < $b1 ? -1 : 1);
      });
    }

  }
}
