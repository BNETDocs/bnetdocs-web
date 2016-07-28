<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Credits;
use \BNETDocs\Libraries\Document;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Exceptions\UserNotFoundException;
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
    $router->setResponseCode(($model->user ? 200 : 404));
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }

  protected function getUserInfo(UserViewModel &$model) {
    $model->user_id = $this->user_id;

    // Try to get the user
    try {
      $model->user = new UserLib($this->user_id);
    } catch (UserNotFoundException $e) {
      $model->user = null;
      return;
    }

    // Try to get their user profile
    try {

      $model->user_profile  = new UserProfile($this->user_id);

      $model->biography     = $model->user_profile->getBiography();

      $model->facebook      = $model->user_profile->getFacebookUsername();
      $model->facebook_uri  = $model->user_profile->getFacebookURI();

      $model->github        = $model->user_profile->getGitHubUsername();
      $model->github_uri    = $model->user_profile->getGitHubURI();

      $model->instagram     = $model->user_profile->getInstagramUsername();
      $model->instagram_uri = $model->user_profile->getInstagramURI();

      $model->phone         = $model->user_profile->getPhone();
      $model->phone_uri     = $model->user_profile->getPhoneURI();

      $model->reddit        = $model->user_profile->getRedditUsername();
      $model->reddit_uri    = $model->user_profile->getRedditURI();

      $model->skype         = $model->user_profile->getSkypeUsername();
      $model->skype_uri     = $model->user_profile->getSkypeURI();

      $model->steam_id      = $model->user_profile->getSteamId();
      $model->steam_uri     = $model->user_profile->getSteamURI();

      $model->twitter       = $model->user_profile->getTwitterUsername();
      $model->twitter_uri   = $model->user_profile->getTwitterURI();

      $model->website       = $model->user_profile->getWebsite();
      $model->website_uri   = $model->user_profile->getWebsiteURI();

    } catch (UserProfileNotFoundException $e) {
      // Not a problem
    }

    // Should we display profile data at all?
    $model->profiledata = (
      $model->github  || $model->facebook  ||
      $model->twitter || $model->instagram ||
      $model->skype   || $model->website
    );

    // How long have they been a member?
    $model->user_est = Common::intervalToString(
      $model->user->getCreatedDateTime()->diff(
        new DateTime("now", new DateTimeZone("UTC"))
      )
    );
    $user_est_comma = strpos($model->user_est, ",");
    if ($user_est_comma !== false)
      $model->user_est = substr($model->user_est, 0, $user_est_comma);

    // Summary of contributions
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

    // Total number of contributions
    $model->contributions = 0;
    $model->contributions += $model->sum_documents;
    $model->contributions += $model->sum_news_posts;
    $model->contributions += $model->sum_packets;
    $model->contributions += $model->sum_servers;

    // References to the contributions
    $model->documents  = ($model->sum_documents  ?
      Document::getDocumentsByUserId($this->user_id) : null
    );
    $model->news_posts = ($model->sum_news_posts ? true : null);
    $model->packets    = ($model->sum_packets    ? true : null);
    $model->servers    = ($model->sum_servers    ? true : null);

    // Process documents
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
  }

}
