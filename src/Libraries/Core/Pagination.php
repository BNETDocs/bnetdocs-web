<?php

namespace BNETDocs\Libraries\Core;

use \OutOfBoundsException;

class Pagination implements \JsonSerializable
{
  private array $dataset;
  private int $limit;
  private int $page;

  public function __construct(array $dataset, int $page, int $limit)
  {
    $this->dataset = $dataset;
    $this->limit = $limit;
    $this->page = $page;

    if ($this->limit < 1) throw new OutOfBoundsException();
    if ($this->page < 0 || $this->page > $this->pageCount()) throw new OutOfBoundsException();
  }

  public function currentPage(): int
  {
    return $this->page;
  }

  public function getPage(): array
  {
    $lbound = $this->page * $this->limit;
    $ubound = $lbound + $this->limit;
    $set    = [];
    $keys   = array_keys($this->dataset);
    $size   = count($keys);

    if ($ubound > $size) $ubound = $size;

    for ($i = $lbound; $i < $ubound; ++$i)
    {
      $set[] = $this->dataset[$keys[$i]];
    }

    return $set;
  }

  public function jsonSerialize(): mixed
  {
    return [
      'current_page' => $this->currentPage(),
      'page_count' => $this->pageCount(),
      'items_per_page' => $this->limit,
      'page_items' => $this->getPage(),
    ];
  }

  public function nextPage(): int
  {
    if ($this->page >= $this->pageCount())
    {
      throw new OutOfBoundsException('Current page reached upper bound');
    }

    $this->page += 1;
    return $this->page;
  }

  public function pageCount(): int
  {
    return (int) ceil(count($this->dataset) / $this->limit);
  }

  public function previousPage(): int
  {
    if ($this->page <= 0)
    {
      throw new OutOfBoundsException('Current page reached lower bound');
    }

    $this->page -= 1;
    return $this->page;
  }

  public function setPage(int $page): int
  {
    if ($page < 0 || $page > $this->pageCount())
    {
      throw new OutOfBoundsException('Page is out of bounds');
    }

    $this->page = $page;
    return $this->page;
  }
}
