<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Exceptions\TemplateNotFoundException;
use \BNETDocs\Libraries\Logger;

final class Template {

  protected $additional_css;
  protected $context;
  protected $template;
  private $template_path;

  public function __construct(&$context, $template, $template_path) {
    $this->additional_css = [];
    $this->setContext($context);
    $this->setTemplate($template);
    $this->template_path = $template_path;
  }

  public function getContext() {
    return $this->context;
  }

  public function getTemplate() {
    return $this->template;
  }

  public function render() {
    $cwd = getcwd();
    try {
      chdir($cwd . $this->template_path);
      if (!file_exists($this->template)) {
        throw new TemplateNotFoundException($this);
      }
      require($this->template);
    } finally {
      chdir($cwd);
    }
  }

  public function setContext(&$context) {
    $this->context = $context;
  }

  public function setTemplate($template) {
    $this->template = "./" . $template . ".phtml";
    Logger::logMetric("template", $template);
  }

}
