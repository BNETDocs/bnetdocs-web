<?php

namespace BNETDocs\Models;

class Base implements \BNETDocs\Interfaces\Model
{
    public int $_responseCode = 500;
    public array $_responseHeaders = [
        'Cache-Control' => 'max-age=0,no-cache,no-store', // disables cache in the browser for all PHP pages by default.
        'X-Frame-Options' => 'DENY' // DENY tells the browser to prevent archaic frame/iframe embeds of all pages including from ourselves (see also: SAMEORIGIN).
    ];
}
