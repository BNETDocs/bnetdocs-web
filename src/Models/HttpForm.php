<?php

namespace BNETDocs\Models;

class HttpForm extends ActiveUser implements \JsonSerializable
{
  /**
   * The key-value store of the form.
   *
   * @var array
   */
  public array $form = [];

  public function jsonSerialize(): mixed
  {
    return \array_merge(parent::jsonSerialize(), ['form' => $this->form]);
  }
}
