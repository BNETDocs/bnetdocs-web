<?php
namespace BNETDocs\Libraries;
interface IDatabaseObject
{
  /**
   * Allocates object properties from the database.
   */
  function allocate();

  /**
   * Commit object properties to the database.
   */
  function commit();
}
