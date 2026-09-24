<?php

namespace BNETDocs\Libraries\Core;

class Curl
{
    public static function execute(string $url, ?array $post_content = null,
        string $content_type = '', int $connect_timeout = 5, int $max_redirects = 10): array
    {
        $curl = \curl_init();
        $time = \microtime(true);

        \curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $connect_timeout);

        \curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        \curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        \curl_setopt($curl, CURLOPT_MAXREDIRS, $max_redirects);
        \curl_setopt($curl, CURLOPT_POSTREDIR, 7);

        \curl_setopt($curl, CURLOPT_URL, $url);

        if ($post_content)
        {
            \curl_setopt($curl, CURLOPT_POST, true);
            \curl_setopt($curl, CURLOPT_POSTFIELDS, $post_content);
            if (!empty($content_type))
            {
                \curl_setopt($curl, CURLOPT_HTTPHEADER, [
                    'Content-Type: ' . $content_type
                ]);
            }
        }

        \curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = [];
        $response['data'] = \curl_exec($curl);
        $response['code'] = \curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $response['type'] = \curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        $response['time'] = \microtime(true) - $time;

        // curl_close() is a no-op since PHP 8.0 (handles are freed automatically) and deprecated
        // since PHP 8.5, so it is intentionally not called here.
        return $response;
    }
}
