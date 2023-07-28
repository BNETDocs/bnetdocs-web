<?php

namespace BNETDocs\Libraries\Packet;

abstract class Layer
{
  protected int $id;
  protected string $label;
  protected string $tag;

  public function __construct(int $id)
  {
    $this->assign($id);
  }

  protected abstract function assign(int $id): void;
  public abstract static function getAllAsArray(): array;
  public abstract static function getAllAsObjects(): array;

  public function getId(): int
  {
    return $this->id;
  }

  public function getLabel(): string
  {
    return $this->label;
  }

  public function getTag(): string
  {
    return $this->tag;
  }
}
