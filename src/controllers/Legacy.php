<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Models\Legacy as LegacyModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

class Legacy extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $data        = $router->getRequestQueryArray();
    $model       = new LegacyModel();
    $model->did  = (isset($data['did' ]) ? $data['did' ] : null);
    $model->lang = (isset($data['lang']) ? $data['lang'] : null);
    $model->nid  = (isset($data['nid' ]) ? $data['nid' ] : null);
    $model->op   = (isset($data['op'  ]) ? $data['op'  ] : null);
    $model->pid  = (isset($data['pid' ]) ? $data['pid' ] : null);
    $model->url  = null;

    if ($model->op == 'cpw') {
      $model->url = '/user/changepassword';
    } else if ($model->op == 'credits') {
      $model->url = '/credits';
    } else if ($model->op == 'doc' && !is_null($model->did)) {
      $model->url = '/document/' . rawurlencode($model->did);
    } else if ($model->op == 'generatecode' && !is_null($model->lang)) {
      $model->url = '/packet/index.' . rawurlencode($model->lang);
    } else if ($model->op == 'legalism') {
      $model->url = '/legal';
    } else if ($model->op == 'login') {
      $model->url = '/user/login';
    } else if ($model->op == 'news' && !is_null($model->nid)) {
      $model->url = '/news/' . rawurlencode($model->nid);
    } else if ($model->op == 'news') {
      $model->url = '/news';
    } else if ($model->op == 'packet' && !is_null($model->pid)) {
      $model->url = '/packet/' . rawurlencode($model->pid);
    } else if ($model->op == 'register') {
      $model->url = '/user/register';
    } else if ($model->op == 'resetpw') {
      $model->url = '/user/resetpassword';
    }

    if (is_null($model->url)) {
      $model->url = '/welcome';
      $model->is_legacy = false;
      $code = 302;
    } else {
      $model->is_legacy = true;
      $code = 301;
    }

    $model->url = Common::relativeUrlToAbsolute($model->url);

    $view->render($model);

    $model->_responseCode = $code;
    $model->_responseHeaders['Content-Type'] = $view->getMimeType();
    $model->_responseHeaders['Location'] = $model->url;
    $model->_responseTTL = 0;

    return $model;

  }

}
