<?php

namespace BNETDocs\Controllers;

class Discord extends Base
{
  /**
   * Constructs a Controller, typically to initialize properties.
   */
  public function __construct()
  {
    $this->model = new \BNETDocs\Models\Discord();
  }

  /**
   * Invoked by the Router class to handle the request.
   *
   * @param array|null $args The optional route arguments and any captured URI arguments.
   * @return boolean Whether the Router should invoke the configured View.
   */
  public function invoke(?array $args) : bool
  {
    $config = &\CarlBennett\MVC\Libraries\Common::$config->discord;

    $this->model->discord_server_id = $config->server_id;
    $this->model->discord_url = \sprintf('https://discord.gg/%s', $config->invite_code);
    $this->model->enabled = $config->enabled;

    $this->model->_responseCode = ($this->model->enabled ? 200 : 503);
    return true;
  }
}
