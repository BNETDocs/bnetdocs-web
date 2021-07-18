<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Models\Packet;
class Form extends \BNETDocs\Models\ActiveUser implements \JsonSerializable
{
  // possible values for $error:
  const ERROR_ACL_DENIED = 'ACL_DENIED';
  const ERROR_INTERNAL = 'INTERNAL';
  const ERROR_NONE = 'NONE';
  const ERROR_NOT_FOUND = 'NOT_FOUND';
  const ERROR_OUTOFBOUNDS_DIRECTION = 'OUTOFBOUNDS_DIRECTION';
  const ERROR_OUTOFBOUNDS_FORMAT = 'OUTOFBOUNDS_FORMAT';
  const ERROR_OUTOFBOUNDS_ID = 'OUTOFBOUNDS_ID';
  const ERROR_OUTOFBOUNDS_NAME = 'OUTOFBOUNDS_NAME';
  const ERROR_OUTOFBOUNDS_PACKET_ID = 'OUTOFBOUNDS_PACKET_ID';
  const ERROR_OUTOFBOUNDS_REMARKS = 'OUTOFBOUNDS_REMARKS';
  const ERROR_OUTOFBOUNDS_USED_BY = 'OUTOFBOUNDS_USED_BY';
  const ERROR_SUCCESS = 'SUCCESS';

  public $comments;
  public $error;
  public $form_fields;
  public $packet;
  public $products;

  /**
   * Implements the JSON serialization function from the JsonSerializable interface.
   */
  public function jsonSerialize()
  {
    return [
      'comments' => $this->comments,
      'error' => $this->error,
      'form_fields' => $this->form_fields,
      'packet' => $this->packet,
      'products' => $this->products,
    ];
  }
}
