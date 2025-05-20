<?php

namespace BNETDocs\Libraries\Tag;

use \BNETDocs\Libraries\Db\MariaDb;
use \BNETDocs\Libraries\Tag\Types;
use \OutOfBoundsException;

class Tag implements \BNETDocs\Interfaces\DatabaseObject, \JsonSerializable
{
    public const MAX_REFERENCE_ID = 0x7FFFFFFFFFFFFFFF;
    public const MAX_REFERENCE_TYPE = 0x7FFFFFFFFFFFFFFF;

    private ?int $reference_id = null;
    private ?Types $reference_type = null;
    private ?string $tag_string = null;

    public function __construct(?object $value)
    {
        if (is_object($value))
        {
            $this->allocateObject($value);
        }
    }

    public function __toString(): string
    {
        return $this->getTagString() ?? '';
    }

    public function allocate(): bool
    {
        $p = [
            'refid' => $this->getReferenceId(),
            'reftype' => $this->getReferenceType(),
            'tagstr' => $this->getTagString(),
        ];
        if ($p['reftype'] instanceof Types) $p['reftype'] = $p['reftype']->toInt();

        try
        {
            $q = MariaDb::instance()->prepare('
                SELECT
                    `reference_id`,
                    `reference_type`,
                    `tag_string`
                FROM `tags` WHERE
                    `reference_id` = :refid AND
                    `reference_type` = :reftype AND
                    `tag_string` = :tagstr
                LIMIT 1;
            ');
            if (!$q || !$q->execute($p) || $q->rowCount() !== 1) return false;
            $this->allocateObject($q->fetchObject());
            return true;
        }
        finally
        {
            if ($q) $q->closeCursor();
        }
    }

    public static function allocateAll(int|Types $reference_type, int $reference_id): ?array
    {
        $p = [
            'refid' => $reference_id,
            'reftype' => $reference_type,
        ];
        if ($p['reftype'] instanceof Types) $p['reftype'] = $p['reftype']->toInt();

        try
        {
            $q = MariaDb::instance()->prepare('
                SELECT
                    `reference_id`,
                    `reference_type`,
                    `tag_string`
                FROM `tags` WHERE
                    `reference_id` = :refid AND
                    `reference_type` = :reftype
                ORDER BY `tag_string` ASC;
            ');
            if (!$q || !$q->execute($p)) return null;
            $rows = [];
            while ($row = $q->fetchObject()) $rows[] = new self($row);
            return $rows;
        }
        finally
        {
            if ($q) $q->closeCursor();
        }
    }

    public function allocateObject(object $value): void
    {
        $this->setReferenceId($value->reference_id ?? null);
        $this->setReferenceType($value->reference_type ?? null);
        $this->setTagString($value->tag_string ?? null);
    }

    public function commit(): bool
    {
        $p = [
            'refid' => $this->getReferenceId(),
            'reftype' => $this->getReferenceType(),
            'tagstr' => $this->getTagString(),
        ];
        if ($p['reftype'] instanceof Types) $p['reftype'] = $p['reftype']->toInt();

        try
        {
            $q = MariaDb::instance()->prepare('
                INSERT INTO `tags` (
                    `reference_id`,
                    `reference_type`,
                    `tag_string`
                ) VALUES (
                    `reference_id` = :refid,
                    `reference_type` = :reftype,
                    `tag_string` = :tagstr
                ) ON DUPLICATE KEY UPDATE
                    `tag_string` = :tagstr;
            ');

            if ($q && $q->execute($p))
            {
                if ($q->rowCount() === 1)
                {
                    $this->allocateObject($q->fetchObject());
                }
                return true;
            }
            else
            {
                return false;
            }
        }
        finally
        {
            if ($q) $q->closeCursor();
        }
    }

    public function deallocate(): bool
    {
        $p = [
            'refid' => $this->getReferenceId(),
            'reftype' => $this->getReferenceType(),
            'tagstr' => $this->getTagString(),
        ];
        if ($p['reftype'] instanceof Types) $p['reftype'] = $p['reftype']->toInt();

        try
        {
            $q = MariaDb::instance()->prepare('
                DELETE FROM `tags` WHERE
                    `reference_id` = :refid AND
                    `reference_type` = :reftype AND
                    `tag_string` = :tagstr
                LIMIT 1;
            ');
            return $q && $q->execute($p);
        }
        finally
        {
            if ($q) $q->closeCursor();
        }
    }

    public function getReferenceId(): ?int
    {
        return $this->reference_id;
    }

    public function getReferenceType(): int|null|Types
    {
        return $this->reference_type;
    }

    public function getTagString(): ?string
    {
        return $this->tag_string;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'reference_id' => $this->getReferenceId(),
            'reference_type' => $this->getReferenceType(),
            'tag_string' => $this->getTagString(),
        ];
    }

    public function setReferenceId(?int $value): void
    {
        if (!is_null($value) && ($value < 0 || $value > self::MAX_REFERENCE_ID))
        {
            throw new OutOfBoundsException(sprintf(
                'value must be null or an integer between 0-%d', self::MAX_REFERENCE_ID
            ));
        }

        $this->reference_id = $value;
    }

    public function setReferenceType(int|null|Types $value): void
    {
        if (!is_null($value) && !($value instanceof Types)
            && ($value < 0 || $value > self::MAX_REFERENCE_TYPE))
        {
            throw new OutOfBoundsException(sprintf(
                'value must be null, Types enum, or an integer between 0-%d', self::MAX_REFERENCE_TYPE
            ));
        }

        $this->reference_type = is_null($value) ? null : (is_int($value) ? Types::fromInt($value) : $value);
    }

    public function setTagString(?string $value): void
    {
        $this->tag_string = $value;
    }
}
