<?php

namespace BNETDocs\Tests\Libraries\Core;

use \BNETDocs\Libraries\Core\Pagination;
use \OutOfBoundsException;
use \PHPUnit\Framework\TestCase;

class PaginationTest extends TestCase
{
    // Constructor validation

    public function testConstructorThrowsOnZeroLimit(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $data = [1, 2, 3];
        new Pagination($data, 0, 0);
    }

    public function testConstructorThrowsOnNegativePage(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $data = [1, 2, 3];
        new Pagination($data, -1, 5);
    }

    public function testConstructorThrowsWhenPageExceedsCount(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $data = [1, 2, 3];
        // pageCount = ceil(3/2) = 2; page 3 > 2
        new Pagination($data, 3, 2);
    }

    public function testConstructorAcceptsPageEqualToPageCount(): void
    {
        // pageCount = ceil(4/2) = 2; page 2 == 2, which the constructor allows
        $data = [1, 2, 3, 4];
        $p = new Pagination($data, 2, 2);
        $this->assertSame(2, $p->currentPage());
    }

    // pageCount

    public function testPageCountEvenDivision(): void
    {
        $data = range(1, 9);
        $p = new Pagination($data, 0, 3);
        $this->assertSame(3, $p->pageCount());
    }

    public function testPageCountWithRemainder(): void
    {
        $data = range(1, 5);
        $p = new Pagination($data, 0, 2);
        $this->assertSame(3, $p->pageCount());
    }

    public function testPageCountEmptyDataset(): void
    {
        $p = new Pagination([], 0, 5);
        $this->assertSame(0, $p->pageCount());
    }

    public function testPageCountSinglePage(): void
    {
        $data = range(1, 10);
        $p = new Pagination($data, 0, 10);
        $this->assertSame(1, $p->pageCount());
    }

    // currentPage

    public function testCurrentPageDefault(): void
    {
        $p = new Pagination([1, 2, 3], 0, 5);
        $this->assertSame(0, $p->currentPage());
    }

    public function testCurrentPageNonZero(): void
    {
        $data = range(1, 10);
        $p = new Pagination($data, 2, 3);
        $this->assertSame(2, $p->currentPage());
    }

    // getPage

    public function testGetPageFirstPage(): void
    {
        $data = ['a', 'b', 'c', 'd', 'e'];
        $p = new Pagination($data, 0, 2);
        $this->assertSame(['a', 'b'], $p->getPage());
    }

    public function testGetPageSecondPage(): void
    {
        $data = ['a', 'b', 'c', 'd', 'e'];
        $p = new Pagination($data, 1, 2);
        $this->assertSame(['c', 'd'], $p->getPage());
    }

    public function testGetPageLastPageWithRemainder(): void
    {
        $data = ['a', 'b', 'c', 'd', 'e'];
        $p = new Pagination($data, 2, 2);
        $this->assertSame(['e'], $p->getPage());
    }

    public function testGetPageOnEmptyDataset(): void
    {
        $p = new Pagination([], 0, 5);
        $this->assertSame([], $p->getPage());
    }

    // nextPage

    public function testNextPageIncrementsAndReturns(): void
    {
        $data = range(1, 9);
        $p = new Pagination($data, 0, 3);
        $result = $p->nextPage();
        $this->assertSame(1, $result);
        $this->assertSame(1, $p->currentPage());
    }

    public function testNextPageThrowsAtUpperBound(): void
    {
        $this->expectException(OutOfBoundsException::class);
        // pageCount = ceil(9/3) = 3; at page 3 (== pageCount), nextPage throws
        $data = range(1, 9);
        $p = new Pagination($data, 3, 3);
        $p->nextPage();
    }

    // previousPage

    public function testPreviousPageDecrementsAndReturns(): void
    {
        $data = range(1, 6);
        $p = new Pagination($data, 2, 2);
        $result = $p->previousPage();
        $this->assertSame(1, $result);
        $this->assertSame(1, $p->currentPage());
    }

    public function testPreviousPageThrowsAtLowerBound(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $p = new Pagination([1, 2, 3], 0, 5);
        $p->previousPage();
    }

    // setPage

    public function testSetPageChangesCurrentPage(): void
    {
        $data = range(1, 10);
        $p = new Pagination($data, 0, 2);
        $result = $p->setPage(3);
        $this->assertSame(3, $result);
        $this->assertSame(3, $p->currentPage());
    }

    public function testSetPageThrowsOnNegativePage(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $p = new Pagination([1, 2, 3], 0, 5);
        $p->setPage(-1);
    }

    public function testSetPageThrowsWhenPageExceedsCount(): void
    {
        $this->expectException(OutOfBoundsException::class);
        // pageCount = ceil(3/5) = 1; page 2 > 1
        $p = new Pagination([1, 2, 3], 0, 5);
        $p->setPage(2);
    }

    // jsonSerialize

    public function testJsonSerializeStructure(): void
    {
        $data = ['x', 'y', 'z'];
        $p = new Pagination($data, 1, 2);
        $json = $p->jsonSerialize();
        $this->assertSame(1, $json['current_page']);
        $this->assertSame(2, $json['page_count']);
        $this->assertSame(2, $json['items_per_page']);
        $this->assertSame(['z'], $json['page_items']);
    }
}
