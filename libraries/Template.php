<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Exceptions\TemplateNotFoundException;
use \BNETDocs\Libraries\Logger;
use \SplObjectStorage;

final class Template {

  const TEMPLATE_DIR = "/templates";

  protected $additional_css;
  protected $context;
  protected $opengraph;
  protected $template;

  public function __construct(&$context, $template) {
    $this->additional_css = [];
    $this->opengraph      = new SplObjectStorage();
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
    $cwd = getcwd();
    try {
      chdir($cwd . DIRECTORY_SEPARATOR . "templates");
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
    $this->template = "." . DIRECTORY_SEPARATOR
      . str_replace("/", DIRECTORY_SEPARATOR, $template) . ".phtml";
    Logger::logMetric("template", $template);
  }

}
