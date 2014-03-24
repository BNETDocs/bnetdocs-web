<?php
  
  interface SQLInsertable extends Collectable {
    
    public function __sqlGetColumnNames();
    public function __sqlGetColumnValues();
    public function __sqlInsertQueryFull();
    public function __sqlInsertQueryColumnsAndValues();
    
  }
  