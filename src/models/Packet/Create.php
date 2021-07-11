<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Models\Packet;
class Create extends \BNETDocs\Models\ActiveUser
{
  const ERROR_ACL_DENIED = 'ACL_DENIED';
  const ERROR_CREATED = 'CREATED';
  const ERROR_INTERNAL = 'INTERNAL';
  const ERROR_NONE = 'NONE';
  const ERROR_OUTOFBOUNDS_FORMAT = 'OUTOFBOUNDS_FORMAT';
  const ERROR_OUTOFBOUNDS_ID = 'OUTOFBOUNDS_ID';
  const ERROR_OUTOFBOUNDS_NAME = 'OUTOFBOUNDS_NAME';
  const ERROR_OUTOFBOUNDS_REMARKS = 'OUTOFBOUNDS_REMARKS';
  const ERROR_OUTOFBOUNDS_USED_BY = 'OUTOFBOUNDS_USED_BY';

  public $error;
  public $form_fields;
  public $packet;
  public $products;
}
