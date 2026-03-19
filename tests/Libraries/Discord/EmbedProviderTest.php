<?php

namespace BNETDocs\Tests\Libraries\Discord;

use \BNETDocs\Libraries\Discord\EmbedProvider;
use \PHPUnit\Framework\TestCase;

class EmbedProviderTest extends TestCase
{
    // Constructor

    public function testConstructorSetsName(): void
    {
        $p = new EmbedProvider('BNETDocs');
        $data = $p->jsonSerialize();
        $this->assertSame('BNETDocs', $data['name']);
    }

    public function testConstructorOptionalUrl(): void
    {
        $p = new EmbedProvider('BNETDocs', 'https://bnetdocs.org');
        $data = $p->jsonSerialize();
        $this->assertSame('https://bnetdocs.org', $data['url']);
    }

    // jsonSerialize

    public function testJsonSerializeOmitsEmptyUrl(): void
    {
        $p = new EmbedProvider('BNETDocs');
        $data = $p->jsonSerialize();
        $this->assertArrayNotHasKey('url', $data);
    }

    public function testJsonSerializeIncludesNonEmptyUrl(): void
    {
        $p = new EmbedProvider('BNETDocs', 'https://bnetdocs.org');
        $data = $p->jsonSerialize();
        $this->assertArrayHasKey('url', $data);
    }

    public function testJsonSerializeOmitsEmptyName(): void
    {
        // No validation on name length; empty name is stripped by empty()
        $p = new EmbedProvider('');
        $data = $p->jsonSerialize();
        $this->assertArrayNotHasKey('name', $data);
    }

    public function testJsonSerializeImplementsJsonSerializable(): void
    {
        $p = new EmbedProvider('Name');
        $this->assertInstanceOf(\JsonSerializable::class, $p);
    }
}
