<?php

namespace BNETDocs\Libraries\Tag;

enum Types: int
{
    case Comment = 0;
    case Document = 1;
    case NewsPost = 2;
    case Packet = 3;
    case Server = 4;
    case User = 5;

    public static function fromInt(int $value): self
    {
        return match($value) {
            0 => self::Comment,
            1 => self::Document,
            2 => self::NewsPost,
            3 => self::Packet,
            4 => self::Server,
            5 => self::User,
            default => throw new \ValueError("Invalid Types enum value: $value"),
        };
    }

    public function toInt(): int
    {
        return $this->value;
    }
}
