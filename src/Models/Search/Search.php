<?php

namespace BNETDocs\Models\Search;

class Search extends \BNETDocs\Models\ActiveUser implements \JsonSerializable
{
    public ?string $user_input = null;
    public ?\BNETDocs\Libraries\Search\Results $results = null;

    public function jsonSerialize(): mixed
    {
        return \array_merge(parent::jsonSerialize(), [
            'results' => $this->results,
            'user_input' => $this->user_input,
        ]);
    }
}
