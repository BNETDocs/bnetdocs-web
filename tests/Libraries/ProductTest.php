<?php

namespace BNETDocs\Tests\Libraries;

use \BNETDocs\Libraries\Product;
use \PHPUnit\Framework\TestCase;
use \stdClass;

class ProductTest extends TestCase
{
    private Product $product;

    protected function setUp(): void
    {
        // Construct via StdClass path (allocateObject), which requires no DB access.
        $obj = new stdClass();
        $obj->bnet_product_id  = 0x01;
        $obj->bnet_product_raw = 'STAR';
        $obj->bnls_product_id  = 1;
        $obj->label            = 'StarCraft';
        $obj->sort             = 0;
        $obj->version_byte     = 0xCD;

        $this->product = new Product($obj);
    }

    // Getters reflect StdClass construction

    public function testGetBnetProductId(): void
    {
        $this->assertSame(0x01, $this->product->getBnetProductId());
    }

    public function testGetBnetProductRaw(): void
    {
        $this->assertSame('STAR', $this->product->getBnetProductRaw());
    }

    public function testGetBnlsProductId(): void
    {
        $this->assertSame(1, $this->product->getBnlsProductId());
    }

    public function testGetLabel(): void
    {
        $this->assertSame('StarCraft', $this->product->getLabel());
    }

    public function testGetSort(): void
    {
        $this->assertSame(0, $this->product->getSort());
    }

    public function testGetVersionByte(): void
    {
        $this->assertSame(0xCD, $this->product->getVersionByte());
    }

    // Setters

    public function testSetBnetProductRawChangesValue(): void
    {
        $this->product->setBnetProductRaw('W2BN');
        $this->assertSame('W2BN', $this->product->getBnetProductRaw());
    }

    public function testSetBnlsProductIdChangesValue(): void
    {
        $this->product->setBnlsProductId(42);
        $this->assertSame(42, $this->product->getBnlsProductId());
    }

    public function testSetLabelChangesValue(): void
    {
        $this->product->setLabel('Warcraft II');
        $this->assertSame('Warcraft II', $this->product->getLabel());
    }

    public function testSetSortChangesValue(): void
    {
        $this->product->setSort(5);
        $this->assertSame(5, $this->product->getSort());
    }

    public function testSetVersionByteChangesValue(): void
    {
        $this->product->setVersionByte(0xDD);
        $this->assertSame(0xDD, $this->product->getVersionByte());
    }

    // commit() always returns false (read-only product table)

    public function testCommitAlwaysReturnsFalse(): void
    {
        $this->assertFalse($this->product->commit());
    }

    // jsonSerialize

    public function testJsonSerializeHasExpectedKeys(): void
    {
        $data = $this->product->jsonSerialize();
        foreach ([
            'bnet_product_id', 'bnet_product_raw', 'bnls_product_id',
            'label', 'sort', 'version_byte',
        ] as $key) {
            $this->assertArrayHasKey($key, $data, "Missing key: $key");
        }
    }

    public function testJsonSerializeReflectsConstructedValues(): void
    {
        $data = $this->product->jsonSerialize();
        $this->assertSame(0x01, $data['bnet_product_id']);
        $this->assertSame('STAR', $data['bnet_product_raw']);
        $this->assertSame(1, $data['bnls_product_id']);
        $this->assertSame('StarCraft', $data['label']);
        $this->assertSame(0, $data['sort']);
        $this->assertSame(0xCD, $data['version_byte']);
    }
}
