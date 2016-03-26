<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Credits;
use \BNETDocs\Libraries\Document;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Exceptions\UserProfileNotFoundException;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\User as UserLib;
use \BNETDocs\Libraries\UserProfile;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\User\View as UserViewModel;
use \BNETDocs\Views\User\ViewHtml as UserViewHtmlView;
use \DateTime;
use \DateTimeZone;

class View extends Controller {

  protected $user_id;

  public function __construct($user_id) {
    parent::__construct();
    $this->user_id = $user_id;
  }

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new UserViewHtmlView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new UserViewModel();
    $model->user_session = UserSession::load($router);
    $this->getUserInfo($model);
    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

  protected function getUserInfo(UserViewModel &$model) {
    $model->user_id = $this->user_id;

    $model->sum_documents = Credits::getTotalDocumentsByUserId(
      $this->user_id
    );
    $model->sum_news_posts = Credits::getTotalNewsPostsByUserId(
      $this->user_id
    );
    $model->sum_packets = Credits::getTotalPacketsByUserId(
      $this->user_id
    );
    $model->sum_servers = Credits::getTotalServersByUserId(
      $this->user_id
    );

    $model->contributions = 0;
    $model->contributions += $model->sum_documents;
    $model->contributions += $model->sum_news_posts;
    $model->contributions += $model->sum_packets;
    $model->contributions += $model->sum_servers;

    $model->documents  = ($model->sum_documents  ?
      Document::getDocumentsByUserId($this->user_id) : null
    );
    $model->news_posts = ($model->sum_news_posts ? true : null);
    $model->packets    = ($model->sum_packets    ? true : null);
    $model->servers    = ($model->sum_servers    ? true : null);

    if ($model->documents) {
      // Alphabetically sort the documents
      usort($model->documents, function($a, $b){
        $a1 = $a->getTitle();
        $b1 = $b->getTitle();
        if ($a1 == $b1) return 0;
        return ($a1 < $b1 ? -1 : 1);
      });

      // Remove documents that are not published
      $i = count($model->documents) - 1;
      while ($i >= 0) {
        if (!($model->documents[$i]->getOptionsBitmask()
          & Document::OPTION_PUBLISHED)) {
          unset($model->documents[$i]);
        }
        --$i;
      }
    }

    $model->user = new UserLib($this->user_id);

    $model->user_est = Common::intervalToString(
      $model->user->getCreatedDateTime()->diff(
        new DateTime("now", new DateTimeZone("UTC"))
      )
    );
    $user_est_comma = strpos($model->user_est, ",");
    if ($user_est_comma !== false)
      $model->user_est = substr($model->user_est, 0, $user_est_comma);

    try {
      $model->user_profile = new UserProfile($this->user_id);
      $model->biography    = $model->user_profile->getBiography();
      $model->github       = $model->user_profile->getGitHubUsername();
      $model->facebook     = $model->user_profile->getFacebookUsername();
      $model->twitter      = $model->user_profile->getTwitterUsername();
      $model->instagram    = $model->user_profile->getInstagramUsername();
      $model->skype        = $model->user_profile->getSkypeUsername();
      $model->website      = $model->user_profile->getWebsite();
    } catch (UserProfileNotFoundException $e) {
      $model->user_profile = null;
      $model->biography    = null;
      $model->github       = null;
      $model->facebook     = null;
      $model->twitter      = null;
      $model->instagram    = null;
      $model->skype        = null;
      $model->website      = null;
    }
    
    $model->profiledata = (
      $model->github  || $model->facebook  ||
      $model->twitter || $model->instagram ||
      $model->skype   || $model->website
    );
  }

}
