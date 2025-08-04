<?php

namespace BNETDocs\Libraries\Core;

class UrlFormatter
{
    public static function assetify(string $value): string
    {
        $version = VersionInfo::get()['asset'] ?? '';
        if (!empty($version)) $version = '?v=' . $version;
        return self::format(\sprintf('/a/%s%s', $value, $version));
    }

    public static function format(string $value): string
    {
        // Is this a secure request
        $secure = \getenv('HTTPS');
        if (!\is_string($secure) || empty($secure))
        {
            $secure = (\getenv('SERVER_PORT') == 443);
        }
        else
        {
            $secure = \strtolower($secure);
            $secure = ($secure == 1
                || $secure == 'on'
                || $secure == 'true'
                || $secure == 'y'
                || $secure == 'yes'
            );
        }
    
        // Current request
        $current_scheme = 'http' . ($secure ? 's' : '') . ':';
        $current_host   = \BNETDocs\Libraries\Core\Router::serverName();
        $current_path   = \getenv('DOCUMENT_URI') ?? '';
        $current_query  = \getenv('QUERY_STRING') ?? '';
    
        // Placeholders
        $scheme = null;
        $host   = null;
        $path   = null;
        $query  = null;
    
        // Split off query part
        $i = \strpos($value, '?');
        if ($i !== false)
        {
            $query = \substr($value, $i + 1);
            $value = \substr($value, 0, $i);
        }
    
        // Retrieve the scheme from the $value
        $i = \strpos($value, '//');
        if ($i !== false)
        {
            $scheme = \substr($value, 0, $i);
            $value  = \substr($value, $i);
        }
        if (empty($scheme)) $scheme = null; // Use current scheme further down
    
        // Retrieve the host from the $value
        if (\substr($value, 0, 2) == '//')
        {
            $value = \substr($value, 2);
            $i     = \strpos($value, '/');
            if ($i === false)
            {
                $host  = $value;
                $value = '';
            }
            else
            {
                $host  = \substr($value, 0, $i);
                $value = \substr($value, $i);
            }
        }
    
        // All what's left is the path
        $path  = $value;
        $value = '';
    
        // If the path is empty, substitute our own
        if (empty($path)) $path = $current_path;
    
        // If the path is relative, splice it into current path
        if (\substr($path, 0, 1) != '/')
        {
            $dir = \dirname($current_path);
            $path = '/' . ($dir == '.' ? $path : $dir . '/' . $path);
        }
    
        // Use current values if none provided
        if (\is_null($scheme)) $scheme = $current_scheme;
        if (\is_null($host)) $host = $current_host;
        if (\is_null($path)) $path = $current_path;
    
        // Build the url
        return $scheme . '//' . $host . $path . ($query ? '?' . $query : '');
    }
}
