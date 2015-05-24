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
    if (!file_exists($this->template)) {
      throw new TemplateNotFoundException($this);
      return false;
    }
    require($this->template);
    return true;
  }

  public function setContext(&$context) {
    $this->context = $context;
  }

  public function setTemplate($template) {
    $this->template = getcwd() . "/templates/" . $template . ".phtml";
  }

}
