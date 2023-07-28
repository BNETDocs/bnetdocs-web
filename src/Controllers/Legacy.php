<?php

namespace BNETDocs\Controllers;

use \BNETDocs\Libraries\Router;
use \CarlBennett\MVC\Libraries\Common;

class Legacy extends Base
{
  /**
   * Constructs a Controller, typically to initialize properties.
   */
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\Legacy();
  }

  /**
   * Invoked by the Router class to handle the request.
   *
   * @param array|null $args The optional route arguments and any captured URI arguments.
   * @return boolean Whether the Router should invoke the configured View.
   */
  public function invoke(?array $args): bool
  {
    $data              = Router::query();
    $this->model->did  = $data['did'] ?? null;
    $this->model->lang = $data['lang'] ?? null;
    $this->model->nid  = $data['nid'] ?? null;
    $this->model->op   = $data['op'] ?? null;
    $this->model->pid  = $data['pid'] ?? null;
    $this->model->url  = null;

    if ($this->model->op == 'cpw') {
      $this->model->url = '/user/changepassword';
    } else if ($this->model->op == 'credits') {
      $this->model->url = '/credits';
    } else if ($this->model->op == 'doc' && !is_null($this->model->did)) {
      $this->model->url = '/document/' . rawurlencode($this->model->did);
    } else if ($this->model->op == 'generatecode' && !is_null($this->model->lang)) {
      $this->model->url = '/packet/index.' . rawurlencode($this->model->lang);
    } else if ($this->model->op == 'legalism') {
      $this->model->url = '/legal';
    } else if ($this->model->op == 'login') {
      $this->model->url = '/user/login';
    } else if ($this->model->op == 'news' && !is_null($this->model->nid)) {
      $this->model->url = '/news/' . rawurlencode($this->model->nid);
    } else if ($this->model->op == 'news') {
      $this->model->url = '/news';
    } else if ($this->model->op == 'packet' && !is_null($this->model->pid)) {
      $this->model->url = '/packet/' . rawurlencode($this->model->pid);
    } else if ($this->model->op == 'register') {
      $this->model->url = '/user/register';
    } else if ($this->model->op == 'resetpw') {
      $this->model->url = '/user/resetpassword';
    }

    if (is_null($this->model->url)) {
      $this->model->url = Common::$config->bnetdocs->navigation->front_page;
      $this->model->is_legacy = false;
      $code = 302;
    } else {
      $this->model->is_legacy = true;
      $code = 301;
    }

    $this->model->url = Common::relativeUrlToAbsolute($this->model->url);

    $this->model->_responseCode = $code;
    $this->model->_responseHeaders['Location'] = $this->model->url;

    return true;
  }
}
