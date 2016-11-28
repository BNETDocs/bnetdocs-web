<?php

namespace BNETDocs\Controllers\User;

use \BNETDocs\Libraries\Credits;
use \BNETDocs\Libraries\Document;
use \BNETDocs\Libraries\Exceptions\UserNotFoundException;
use \BNETDocs\Libraries\Exceptions\UserProfileNotFoundException;
use \BNETDocs\Libraries\NewsPost;
use \BNETDocs\Libraries\Packet;
use \BNETDocs\Libraries\User as UserLib;
use \BNETDocs\Libraries\UserProfile;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\User\View as UserViewModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \DateTime;
use \DateTimeZone;

class View extends Controller {

  protected $user_id;

  public function __construct($user_id) {
    parent::__construct();
    $this->user_id = $user_id;
  }

  public function &run(Router &$router, View &$view, array &$args) {

    $model = new UserViewModel();
    $model->user_session = UserSession::load($router);

    $this->getUserInfo($model);

    $view->render($model);

    $model->_responseCode = ($model->user ? 200 : 404);
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

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
    $model->news_posts = ($model->sum_news_posts ?
      NewsPost::getNewsPostsByUserId($this->user_id): null
    );
    $model->packets    = ($model->sum_packets    ?
      Packet::getPacketsByUserId($this->user_id) : null
    );
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

    // Process news posts
    if ($model->news_posts) {
      // Alphabetically sort the documents
      usort($model->news_posts, function($a, $b){
        $a1 = $a->getTitle();
        $b1 = $b->getTitle();
        if ($a1 == $b1) return 0;
        return ($a1 < $b1 ? -1 : 1);
      });

      // Remove documents that are not published
      $i = count($model->news_posts) - 1;
      while ($i >= 0) {
        if (!($model->news_posts[$i]->getOptionsBitmask()
          & NewsPost::OPTION_PUBLISHED)) {
          unset($model->news_posts[$i]);
        }
        --$i;
      }
    }

    // Process packets
    if ($model->packets) {
      // Alphabetically sort the documents
      usort($model->packets, function($a, $b){
        $a1 = $a->getPacketName();
        $b1 = $b->getPacketName();
        if ($a1 == $b1) return 0;
        return ($a1 < $b1 ? -1 : 1);
      });

      // Remove documents that are not published
      $i = count($model->packets) - 1;
      while ($i >= 0) {
        if (!($model->packets[$i]->getOptionsBitmask()
          & Packet::OPTION_PUBLISHED)) {
          unset($model->packets[$i]);
        }
        --$i;
      }
    }
  }

}
