<?php

namespace BNETDocs\Tests\Libraries\Search;

use \BNETDocs\Libraries\Search\Results;
use \PHPUnit\Framework\TestCase;
use \UnexpectedValueException;

class ResultsTest extends TestCase
{
    private Results $results;

    protected function setUp(): void
    {
        $this->results = new Results();
    }

    // isEmpty

    public function testIsEmptyOnFreshInstance(): void
    {
        $this->assertTrue($this->results->isEmpty());
    }

    // Getters return empty arrays by default

    public function testGetCommentsDefaultEmpty(): void
    {
        $this->assertSame([], $this->results->getComments());
    }

    public function testGetDocumentsDefaultEmpty(): void
    {
        $this->assertSame([], $this->results->getDocuments());
    }

    public function testGetNewsPostsDefaultEmpty(): void
    {
        $this->assertSame([], $this->results->getNewsPosts());
    }

    public function testGetPacketsDefaultEmpty(): void
    {
        $this->assertSame([], $this->results->getPackets());
    }

    public function testGetServersDefaultEmpty(): void
    {
        $this->assertSame([], $this->results->getServers());
    }

    public function testGetUsersDefaultEmpty(): void
    {
        $this->assertSame([], $this->results->getUsers());
    }

    // jsonSerialize structure

    public function testJsonSerializeHasAllSixKeys(): void
    {
        $data = $this->results->jsonSerialize();
        $this->assertArrayHasKey('comments', $data);
        $this->assertArrayHasKey('documents', $data);
        $this->assertArrayHasKey('news_posts', $data);
        $this->assertArrayHasKey('packets', $data);
        $this->assertArrayHasKey('servers', $data);
        $this->assertArrayHasKey('users', $data);
    }

    public function testJsonSerializeDefaultAllEmptyArrays(): void
    {
        $data = $this->results->jsonSerialize();
        foreach ($data as $key => $value)
        {
            $this->assertSame([], $value, "Expected $key to be empty");
        }
    }

    // setX — type validation (wrong type throws, empty array is fine)

    public function testSetCommentsEmptyArrayIsAllowed(): void
    {
        $this->results->setComments([]);
        $this->assertSame([], $this->results->getComments());
    }

    public function testSetCommentsWithWrongTypeThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->results->setComments(['not a Comment object']);
    }

    public function testSetDocumentsEmptyArrayIsAllowed(): void
    {
        $this->results->setDocuments([]);
        $this->assertSame([], $this->results->getDocuments());
    }

    public function testSetDocumentsWithWrongTypeThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->results->setDocuments([new \stdClass()]);
    }

    public function testSetNewsPostsEmptyArrayIsAllowed(): void
    {
        $this->results->setNewsPosts([]);
        $this->assertSame([], $this->results->getNewsPosts());
    }

    public function testSetNewsPostsWithWrongTypeThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->results->setNewsPosts([42]);
    }

    public function testSetPacketsEmptyArrayIsAllowed(): void
    {
        $this->results->setPackets([]);
        $this->assertSame([], $this->results->getPackets());
    }

    public function testSetPacketsWithWrongTypeThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->results->setPackets([new \stdClass()]);
    }

    public function testSetServersEmptyArrayIsAllowed(): void
    {
        $this->results->setServers([]);
        $this->assertSame([], $this->results->getServers());
    }

    public function testSetServersWithWrongTypeThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->results->setServers(['not a Server']);
    }

    public function testSetUsersEmptyArrayIsAllowed(): void
    {
        $this->results->setUsers([]);
        $this->assertSame([], $this->results->getUsers());
    }

    public function testSetUsersWithWrongTypeThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->results->setUsers([new \stdClass()]);
    }

    // removeX — returns false when collection is empty (no match found)

    public function testRemoveCommentReturnsFalseWhenNotFound(): void
    {
        // We cannot construct a real Comment without DB, but we can verify the
        // method signature is callable and returns false on an empty collection.
        // Use a mock to satisfy the type hint without hitting the database.
        $mock = $this->createMock(\BNETDocs\Libraries\Comment::class);
        $this->assertFalse($this->results->removeComment($mock));
    }

    public function testRemoveDocumentReturnsFalseWhenNotFound(): void
    {
        $mock = $this->createMock(\BNETDocs\Libraries\Document::class);
        $this->assertFalse($this->results->removeDocument($mock));
    }

    public function testRemoveServerReturnsFalseWhenNotFound(): void
    {
        $mock = $this->createMock(\BNETDocs\Libraries\Server\Server::class);
        $this->assertFalse($this->results->removeServer($mock));
    }
}
