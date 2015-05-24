<?php

namespace BNETDocs\Libraries;

use BNETDocs\Libraries\Exceptions\TemplateNotFoundException;

final class Template {

  protected $context;
  protected $template;

  public function __construct(&$context, $template) {
    $this->setContext($context);
    $this->setTemplate($template);
  }

  public function getContext() {
    return $this->context;
  }

  public function getTemplate() {
    return $this->template;
  }

  public function render() {
    chdir("./templates/");
    if (!file_exists($this->template)) {
      chdir("../");
      throw new TemplateNotFoundException($this);
    }
    require($this->template);
    chdir("../");
  }

  public function setContext(&$context) {
    $this->context = $context;
  }

  public function setTemplate($template) {
    $this->template = "./" . $template . ".phtml";
    if (extension_loaded("newrelic")) {
      newrelic_add_custom_parameter("template", $template);
    }
  }

}
