<?php

namespace BNETDocs\Tests\Libraries;

use \PHPUnit\Framework\Attributes\DataProvider;
use \PHPUnit\Framework\TestCase;
use \ReflectionClass;

class MaxConstantsTest extends TestCase
{
    public static function classProvider(): array
    {
        return [
            [\BNETDocs\Controllers\Core\Status::class],
            [\BNETDocs\Libraries\Comment::class],
            [\BNETDocs\Libraries\Core\ArrayFlattener::class],
            [\BNETDocs\Libraries\Core\HttpCode::class],
            [\BNETDocs\Libraries\Discord\Embed::class],
            [\BNETDocs\Libraries\Discord\EmbedAuthor::class],
            [\BNETDocs\Libraries\Discord\EmbedField::class],
            [\BNETDocs\Libraries\Discord\EmbedFooter::class],
            [\BNETDocs\Libraries\Discord\Webhook::class],
            [\BNETDocs\Libraries\Document::class],
            [\BNETDocs\Libraries\EventLog\Event::class],
            [\BNETDocs\Libraries\News\Category::class],
            [\BNETDocs\Libraries\News\Post::class],
            [\BNETDocs\Libraries\Packet\Packet::class],
            [\BNETDocs\Libraries\Server\Server::class],
            [\BNETDocs\Libraries\Server\Type::class],
            [\BNETDocs\Libraries\Tag\Tag::class],
            [\BNETDocs\Libraries\User\Authentication::class],
            [\BNETDocs\Libraries\User\Profile::class],
            [\BNETDocs\Libraries\User\User::class],
        ];
    }

    // Regression: constants like 0xFFFFFFFFFFFFFFFF exceed PHP's integer range, so they were
    // floats, and the bounds checks format them with %d, which warns on PHP 8.5.
    #[DataProvider('classProvider')]
    public function testMaxConstantsAreIntegers(string $class): void
    {
        $constants = (new ReflectionClass($class))->getConstants();
        foreach ($constants as $name => $value)
        {
            if (\substr($name, 0, 4) !== 'MAX_') continue;
            $this->assertIsInt($value, \sprintf('%s::%s', $class, $name));
        }
    }
}
