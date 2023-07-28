<?php

namespace BNETDocs\Interfaces;

interface Controller
{
    /**
     * Constructs a Controller, typically to initialize properties.
     */
    public function __construct();

    /**
     * Invoked by the Router class to handle the request.
     *
     * @param array|null $args The optional route arguments and any captured URI arguments.
     * @return boolean Whether the Router should invoke the configured View.
     */
    public function invoke(?array $args): bool;
}
