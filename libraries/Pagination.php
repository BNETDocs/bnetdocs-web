<?php

namespace BNETDocs\Libraries;

use \OutOfBoundsException;
use \Traversable;
use \UnexpectedValueException;

class Pagination {

  private $dataset;
  private $limit;
  private $page;

  public function __construct($dataset, $page, $limit) {
    $this->dataset = $dataset;
    $this->limit   = $limit;
    $this->page    = $page;

    if (!$this->dataset instanceof Traversable && !is_array($this->dataset))
      throw new UnexpectedValueException("Dataset is not traversable");
    if ($this->limit < 1)
      throw new OutOfBoundsException("Limit is less than one");
    if ($this->page < 0 || $this->page > $this->pageCount())
      throw new OutOfBoundsException("Page is out of bounds");
  }

  public function currentPage() {
    return $this->page;
  }

  public function getPage() {
    $lbound = $this->page * $this->limit;
    $ubound = $lbound + $this->limit;
    $set    = [];
    $keys   = array_keys($this->dataset);
    $size   = count($keys);

    if ($ubound > $size) $ubound = $size;

    for ($i = $lbound; $i < $ubound; ++$i) {
      $set[] = $this->dataset[$keys[$i]];
    }

    return $set;
  }

  public function nextPage() {
    if ($this->page >= $this->pageCount())
      throw new OutOfBoundsException("Current page reached upper bound");
    $this->page += 1;
    return $this->page;
  }

  public function pageCount() {
    return ceil(count(array_keys($this->dataset)) / $this->limit);
  }

  public function previousPage() {
    if ($this->page <= 0)
      throw new OutOfBoundsException("Current page reached lower bound");
    $this->page -= 1;
    return $this->page;
  }

  public function setPage($page) {
    if ($page < 0 || $page > $this->pageCount())
      throw new OutOfBoundsException("Page is out of bounds");
    $this->page = $page;
    return $this->page;
  }

}
