<?php

namespace BNETDocs\Libraries\Packet;

abstract class Layer {

  protected $id;
  protected $label;
  protected $tag;

  public function __construct(int $id) {
    $this->assign($id);
  }

  protected abstract function assign(int $id);
  public abstract static function getAllAsArray();
  public abstract static function getAllAsObjects();

  public function getId() {
    return $this->id;
  }

  public function getLabel() {
    return $this->label;
  }

  public function getTag() {
    return $this->tag;
  }

}
