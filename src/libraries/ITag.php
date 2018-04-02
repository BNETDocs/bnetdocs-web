<?php

namespace BNETDocs\Libraries;

interface ITag
{
  public function addTag( $tag_id );
  public function getTags();
  public function removeTag( $tag_id );
}
