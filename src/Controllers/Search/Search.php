<?php

namespace BNETDocs\Controllers\Search;

use \BNETDocs\Libraries\Core\Router;
use \BNETDocs\Libraries\Search\Search as SearchLib;

class Search extends \BNETDocs\Controllers\Base
{
    /**
     * Constructs a Controller, typically to initialize properties.
     */
    public function __construct()
    {
        $this->model = new \BNETDocs\Models\Search\Search();
    }

    /**
     * Invoked by the Router class to handle the request.
     *
     * @param array|null $args The optional route arguments and any captured URI arguments.
     * @return boolean Whether the Router should invoke the configured View.
     */
    public function invoke(?array $args): bool
    {
        $request_args = Router::query();
        $this->model->user_input = $request_args['q'] ?? null;

        if (!empty($this->model->user_input))
        {
            $this->model->results = SearchLib::query($this->model->user_input);
        }

        $this->model->_responseCode = \BNETDocs\Libraries\Core\HttpCode::HTTP_OK;
        return true;
    }
}
