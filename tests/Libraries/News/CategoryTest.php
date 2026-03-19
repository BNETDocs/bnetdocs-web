<?php

namespace BNETDocs\Tests\Libraries\News;

use \BNETDocs\Libraries\News\Category;
use \PHPUnit\Framework\TestCase;
use \stdClass;

class CategoryTest extends TestCase
{
    // Construction with null — allocate() returns true early (no DB)

    public function testConstructNullGivesEmptyDefaults(): void
    {
        $cat = new Category(null);
        $this->assertNull($cat->getId());
        $this->assertSame('', $cat->getFilename());
        $this->assertSame('', $cat->getLabel());
        $this->assertSame(0, $cat->getSortId());
    }

    // Construction with StdClass — allocateObject() path (no DB)

    public function testConstructStdClassSetsAllFields(): void
    {
        $obj = new stdClass();
        $obj->filename = 'protocol-notes';
        $obj->id       = 7;
        $obj->label    = 'Protocol Notes';
        $obj->sort_id  = 3;

        $cat = new Category($obj);
        $this->assertSame('protocol-notes', $cat->getFilename());
        $this->assertSame(7, $cat->getId());
        $this->assertSame('Protocol Notes', $cat->getLabel());
        $this->assertSame(3, $cat->getSortId());
    }

    // Setters

    public function testSetFilenameChangesValue(): void
    {
        $cat = new Category(null);
        $cat->setFilename('new-filename');
        $this->assertSame('new-filename', $cat->getFilename());
    }

    public function testSetLabelChangesValue(): void
    {
        $cat = new Category(null);
        $cat->setLabel('My Label');
        $this->assertSame('My Label', $cat->getLabel());
    }

    public function testSetIdChangesValue(): void
    {
        $cat = new Category(null);
        $cat->setId(42);
        $this->assertSame(42, $cat->getId());
    }

    public function testSetSortIdChangesValue(): void
    {
        $cat = new Category(null);
        $cat->setSortId(10);
        $this->assertSame(10, $cat->getSortId());
    }

    // jsonSerialize

    public function testJsonSerializeHasExpectedKeys(): void
    {
        $cat = new Category(null);
        $data = $cat->jsonSerialize();
        $this->assertArrayHasKey('filename', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('label', $data);
        $this->assertArrayHasKey('sort_id', $data);
    }

    public function testJsonSerializeReflectsCurrentState(): void
    {
        $obj = new stdClass();
        $obj->filename = 'battle-net';
        $obj->id       = 1;
        $obj->label    = 'Battle.net';
        $obj->sort_id  = 0;

        $cat  = new Category($obj);
        $data = $cat->jsonSerialize();

        $this->assertSame('battle-net', $data['filename']);
        $this->assertSame(1, $data['id']);
        $this->assertSame('Battle.net', $data['label']);
        $this->assertSame(0, $data['sort_id']);
    }

    // deallocate returns false when id is null (no DB needed)

    public function testDeallocateReturnsFalseWhenIdIsNull(): void
    {
        $cat = new Category(null);
        $this->assertFalse($cat->deallocate());
    }
}
