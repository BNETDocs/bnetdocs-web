<?php

namespace BNETDocs\Libraries\Search;

use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\Document;
use \BNETDocs\Libraries\News\Post as NewsPost;
use \BNETDocs\Libraries\Packet\Packet;
use \BNETDocs\Libraries\Server\Server;
use \BNETDocs\Libraries\User\User;
use \UnexpectedValueException;

class Results implements \JsonSerializable
{
    private array $comments = [];
    private array $documents = [];
    private array $news_posts = [];
    private array $packets = [];
    private array $servers = [];
    private array $users = [];

    public function addComment(Comment $value, bool $check_for_duplicate = true): void
    {
        if (!$value || is_null($value->getId()))
        {
            throw new UnexpectedValueException('Comment id cannot be null');
        }

        if ($check_for_duplicate)
        {
            foreach ($this->comments as $existing)
            {
                if ($existing === $value) return;
            }
        }

        $this->comments[] = $value;
    }

    public function addDocument(Document $value, bool $check_for_duplicate = true): void
    {
        if (!$value || is_null($value->getId()))
        {
            throw new UnexpectedValueException('Document id cannot be null');
        }

        if ($check_for_duplicate)
        {
            foreach ($this->documents as $existing)
            {
                if ($existing === $value) return;
            }
        }

        $this->documents[] = $value;
    }

    public function addNewsPost(NewsPost $value, bool $check_for_duplicate = true): void
    {
        if (!$value || is_null($value->getId()))
        {
            throw new UnexpectedValueException('NewsPost id cannot be null');
        }

        if ($check_for_duplicate)
        {
            foreach ($this->news_posts as $existing)
            {
                if ($existing === $value) return;
            }
        }

        $this->news_posts[] = $value;
    }

    public function addPacket(Packet $value, bool $check_for_duplicate = true): void
    {
        if (!$value || is_null($value->getId()))
        {
            throw new UnexpectedValueException('Packet id cannot be null');
        }

        if ($check_for_duplicate)
        {
            foreach ($this->packets as $existing)
            {
                if ($existing === $value) return;
            }
        }

        $this->packets[] = $value;
    }

    public function addServer(Server $value, bool $check_for_duplicate = true): void
    {
        if (!$value || is_null($value->getId()))
        {
            throw new UnexpectedValueException('Server id cannot be null');
        }

        if ($check_for_duplicate)
        {
            foreach ($this->servers as $existing)
            {
                if ($existing === $value) return;
            }
        }

        $this->servers[] = $value;
    }

    public function addUser(User $value, bool $check_for_duplicate = true): void
    {
        if (!$value || is_null($value->getId()))
        {
            throw new UnexpectedValueException('Server id cannot be null');
        }

        if ($check_for_duplicate)
        {
            foreach ($this->users as $existing)
            {
                if ($existing === $value) return;
            }
        }

        $this->users[] = $value;
    }

    public function getComments(): array
    {
        return $this->comments;
    }

    public function getDocuments(): array
    {
        return $this->documents;
    }

    public function getNewsPosts(): array
    {
        return $this->news_posts;
    }

    public function getPackets(): array
    {
        return $this->packets;
    }

    public function getServers(): array
    {
        return $this->servers;
    }

    public function getUsers(): array
    {
        return $this->users;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'comments' => $this->comments,
            'documents' => $this->documents,
            'news_posts' => $this->news_posts,
            'packets' => $this->packets,
            'servers' => $this->servers,
            'users' => $this->users,
        ];
    }

    public function removeComment(Comment $value): bool
    {
        foreach ($this->comments as $k => &$v)
        {
            if ($v === $value)
            {
                unset($k);
                return true;
            }
        }
        return false;
    }

    public function removeDocument(Document $value): bool
    {
        foreach ($this->documents as $k => &$v)
        {
            if ($v === $value)
            {
                unset($k);
                return true;
            }
        }
        return false;
    }

    public function removeNewsPost(NewsPost $value): bool
    {
        foreach ($this->news_posts as $k => &$v)
        {
            if ($v === $value)
            {
                unset($k);
                return true;
            }
        }
        return false;
    }

    public function removePacket(Packet $value): bool
    {
        foreach ($this->packets as $k => &$v)
        {
            if ($v === $value)
            {
                unset($k);
                return true;
            }
        }
        return false;
    }

    public function removeServer(Server $value): bool
    {
        foreach ($this->servers as $k => &$v)
        {
            if ($v === $value)
            {
                unset($k);
                return true;
            }
        }
        return false;
    }

    public function removeUser(User $value): bool
    {
        foreach ($this->users as $k => &$v)
        {
            if ($v === $value)
            {
                unset($k);
                return true;
            }
        }
        return false;
    }

    public function setComments(array $value): void
    {
        foreach ($value as $values)
        {
            if (!$values instanceof Comment)
            {
                throw new UnexpectedValueException('Expected Comment object, got something else');
            }
        }
        $this->comments = $value;
    }

    public function setDocuments(array $value): void
    {
        foreach ($value as $values)
        {
            if (!$values instanceof Document)
            {
                throw new UnexpectedValueException('Expected Document object, got something else');
            }
        }
        $this->documents = $value;
    }

    public function setNewsPosts(array $value): void
    {
        foreach ($value as $values)
        {
            if (!$values instanceof NewsPost)
            {
                throw new UnexpectedValueException('Expected NewsPost object, got something else');
            }
        }
        $this->news_posts = $value;
    }

    public function setPackets(array $value): void
    {
        foreach ($value as $values)
        {
            if (!$values instanceof Packet)
            {
                throw new UnexpectedValueException('Expected Packet object, got something else');
            }
        }
        $this->packets = $value;
    }

    public function setServers(array $value): void
    {
        foreach ($value as $values)
        {
            if (!$values instanceof Server)
            {
                throw new UnexpectedValueException('Expected Server object, got something else');
            }
        }
        $this->servers = $value;
    }

    public function setUsers(array $value): void
    {
        foreach ($value as $values)
        {
            if (!$values instanceof User)
            {
                throw new UnexpectedValueException('Expected User object, got something else');
            }
        }
        $this->users = $value;
    }
}
