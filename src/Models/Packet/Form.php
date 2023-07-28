<?php

namespace BNETDocs\Models\Packet;

class Form extends \BNETDocs\Models\ActiveUser implements \JsonSerializable
{
  // possible values for $error:
  const ERROR_ACL_DENIED = 'ACL_DENIED';
  const ERROR_INTERNAL = 'INTERNAL';
  const ERROR_NONE = 'NONE';
  const ERROR_NOT_FOUND = 'NOT_FOUND';
  const ERROR_OUTOFBOUNDS_APPLICATION_LAYER_ID = 'OUTOFBOUNDS_APPLICATION_LAYER_ID';
  const ERROR_OUTOFBOUNDS_BRIEF = 'OUTOFBOUNDS_BRIEF';
  const ERROR_OUTOFBOUNDS_DIRECTION = 'OUTOFBOUNDS_DIRECTION';
  const ERROR_OUTOFBOUNDS_FORMAT = 'OUTOFBOUNDS_FORMAT';
  const ERROR_OUTOFBOUNDS_ID = 'OUTOFBOUNDS_ID';
  const ERROR_OUTOFBOUNDS_NAME = 'OUTOFBOUNDS_NAME';
  const ERROR_OUTOFBOUNDS_PACKET_ID = 'OUTOFBOUNDS_PACKET_ID';
  const ERROR_OUTOFBOUNDS_REMARKS = 'OUTOFBOUNDS_REMARKS';
  const ERROR_OUTOFBOUNDS_TRANSPORT_LAYER_ID = 'OUTOFBOUNDS_TRANSPORT_LAYER_ID';
  const ERROR_OUTOFBOUNDS_USED_BY = 'OUTOFBOUNDS_USED_BY';
  const ERROR_SUCCESS = 'SUCCESS';

  public ?array $comments = null;
  public array $form_fields = [];
  public ?\BNETDocs\Libraries\Packet $packet = null;
  public ?array $products = null;

  /**
   * Implements the JSON serialization function from the JsonSerializable interface.
   */
  public function jsonSerialize(): mixed
  {
    return \array_merge(parent::jsonSerialize(), [
      'comments' => $this->comments,
      'error' => $this->error,
      'form_fields' => $this->form_fields,
      'packet' => $this->packet,
      'products' => $this->products,
    ]);
  }
}
