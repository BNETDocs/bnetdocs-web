<?php

namespace BNETDocs\Views\Search;

use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\Document;
use \BNETDocs\Libraries\News\Post as NewsPost;
use \BNETDocs\Libraries\Packet\Packet;
use \BNETDocs\Libraries\Server\Server;
use \BNETDocs\Libraries\User\User;

class SearchPlain extends \BNETDocs\Views\Base\Plain
{
    public static function invoke(\BNETDocs\Interfaces\Model $model): void
    {
        if (!$model instanceof \BNETDocs\Models\Search\Search)
        {
            throw new \BNETDocs\Exceptions\InvalidModelException($model);
        }

        $model->_responseHeaders['Content-Type'] = self::mimeType();

        $q = $model->user_input ?? '';
        $results = $model->results;

        if (!$results || $results->isEmpty())
        {
            printf("No results for: %s\n", $q);
            return;
        }

        printf("Search Results\n==============\nQuery: %s\n\n", $q);

        $sections = [
            'Comments'   => $results->getComments(),
            'Documents'  => $results->getDocuments(),
            'News Posts' => $results->getNewsPosts(),
            'Packets'    => $results->getPackets(),
            'Servers'    => $results->getServers(),
            'Users'      => $results->getUsers(),
        ];

        foreach ($sections as $label => $items)
        {
            if (empty($items)) continue;

            $heading = sprintf('%s (%d)', $label, count($items));
            printf("%s\n%s\n", $heading, str_repeat('-', strlen($heading)));

            foreach ($items as $item)
            {
                switch (true)
                {
                    case $item instanceof Comment:
                        printf("- Comment #%d <%s>\n", $item->getId(), $item->getParentUrl());
                        break;
                    case $item instanceof Document:
                        printf("- %s <%s>\n", $item->getTitle(), $item->getURI());
                        break;
                    case $item instanceof NewsPost:
                        printf("- %s <%s>\n", $item->getTitle(), $item->getURI());
                        break;
                    case $item instanceof Packet:
                        printf("- %s <%s>\n", $item->getLabel(), $item->getURI());
                        break;
                    case $item instanceof Server:
                        printf("- %s <%s>\n", $item->getLabel(), $item->getURI());
                        break;
                    case $item instanceof User:
                        printf("- %s <%s>\n", $item->getName(), $item->getURI());
                        break;
                    default:
                        printf("- ID %d\n", $item->getId());
                }
            }

            echo "\n";
        }
    }
}
