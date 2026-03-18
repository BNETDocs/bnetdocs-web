<?php

namespace BNETDocs\Libraries\Core;

use \UnexpectedValueException;

class HttpCode implements \JsonSerializable
{
    public const MAX_CODE = 599;
    public const MIN_CODE = 100;

    public const HTTP_ACCEPTED = 202;
    public const HTTP_BAD_GATEWAY = 502;
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_CONFLICT = 409;
    public const HTTP_CONTINUE = 100;
    public const HTTP_CREATED = 201;
    public const HTTP_EXPECTATION_FAILED = 417;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_FOUND = 302;
    public const HTTP_GATEWAY_TIMEOUT = 504;
    public const HTTP_GONE = 410;
    public const HTTP_IM_A_TEAPOT = 418;
    public const HTTP_INTERNAL_SERVER_ERROR = 500;
    public const HTTP_LENGTH_REQUIRED = 411;
    public const HTTP_METHOD_NOT_ALLOWED = 405;
    public const HTTP_MOVED_PERMANENTLY = 301;
    public const HTTP_MULTIPLE_CHOICES = 300;
    public const HTTP_NO_CONTENT = 204;
    public const HTTP_NOT_ACCEPTABLE = 406;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_NOT_IMPLEMENTED = 501;
    public const HTTP_NOT_MODIFIED = 304;
    public const HTTP_OK = 200;
    public const HTTP_PARTIAL_CONTENT = 206;
    public const HTTP_PAYLOAD_TOO_LARGE = 413;
    public const HTTP_PAYMENT_REQUIRED = 402;
    public const HTTP_PERMANENT_REDIRECT = 308;
    public const HTTP_PRECONDITION_FAILED = 412;
    public const HTTP_PRECONDITION_REQUIRED = 428;
    public const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    public const HTTP_RANGE_NOT_SATISFIABLE = 416;
    public const HTTP_REQUEST_TIMEOUT = 408;
    public const HTTP_RESET_CONTENT = 205;
    public const HTTP_SEE_OTHER = 303;
    public const HTTP_SERVICE_UNAVAILABLE = 503;
    public const HTTP_SWITCHING_PROTOCOLS = 101;
    public const HTTP_TEMPORARY_REDIRECT = 307;
    public const HTTP_TOO_MANY_REQUESTS = 429;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    public const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    public const HTTP_UPGRADE_REQUIRED = 426;
    public const HTTP_URI_TOO_LONG = 414;
    public const HTTP_VERSION_NOT_SUPPORTED = 505;

    protected int $code;
    protected static array $instances = [];

    public function __construct(int|string $value)
    {
        $this->setCode($value);
    }

    public static function instance(int $value): self
    {
        if (isset(self::$instances[$value]))
        {
            return self::$instances[$value];
        }
        self::$instances[$value] = new self($value);
        return self::$instances[$value];
    }

    public static function codeFromInt(int $value): string
    {
        switch ($value)
        {
            case self::HTTP_ACCEPTED: $r = 'ACCEPTED'; break;
            case self::HTTP_BAD_GATEWAY: $r = 'BAD_GATEWAY'; break;
            case self::HTTP_BAD_REQUEST: $r = 'BAD_REQUEST'; break;
            case self::HTTP_CONFLICT: $r = 'CONFLICT'; break;
            case self::HTTP_CONTINUE: $r = 'CONTINUE'; break;
            case self::HTTP_CREATED: $r = 'CREATED'; break;
            case self::HTTP_EXPECTATION_FAILED: $r = 'EXPECTATION_FAILED'; break;
            case self::HTTP_FORBIDDEN: $r = 'FORBIDDEN'; break;
            case self::HTTP_FOUND: $r = 'FOUND'; break;
            case self::HTTP_GATEWAY_TIMEOUT: $r = 'GATEWAY_TIMEOUT'; break;
            case self::HTTP_GONE: $r = 'GONE'; break;
            case self::HTTP_IM_A_TEAPOT: $r = 'IM_A_TEAPOT'; break;
            case self::HTTP_INTERNAL_SERVER_ERROR: $r = 'INTERNAL_SERVER_ERROR'; break;
            case self::HTTP_LENGTH_REQUIRED: $r = 'LENGTH_REQUIRED'; break;
            case self::HTTP_METHOD_NOT_ALLOWED: $r = 'METHOD_NOT_ALLOWED'; break;
            case self::HTTP_MOVED_PERMANENTLY: $r = 'MOVED_PERMANENTLY'; break;
            case self::HTTP_MULTIPLE_CHOICES: $r = 'MULTIPLE_CHOICES'; break;
            case self::HTTP_NO_CONTENT: $r = 'NO_CONTENT'; break;
            case self::HTTP_NOT_ACCEPTABLE: $r = 'NOT_ACCEPTABLE'; break;
            case self::HTTP_NOT_FOUND: $r = 'NOT_FOUND'; break;
            case self::HTTP_NOT_IMPLEMENTED: $r = 'NOT_IMPLEMENTED'; break;
            case self::HTTP_NOT_MODIFIED: $r = 'NOT_MODIFIED'; break;
            case self::HTTP_OK: $r = 'OK'; break;
            case self::HTTP_PARTIAL_CONTENT: $r = 'PARTIAL_CONTENT'; break;
            case self::HTTP_PAYLOAD_TOO_LARGE: $r = 'PAYLOAD_TOO_LARGE'; break;
            case self::HTTP_PAYMENT_REQUIRED: $r = 'PAYMENT_REQUIRED'; break;
            case self::HTTP_PERMANENT_REDIRECT: $r = 'PERMANENT_REDIRECT'; break;
            case self::HTTP_PRECONDITION_FAILED: $r = 'PRECONDITION_FAILED'; break;
            case self::HTTP_PRECONDITION_REQUIRED: $r = 'PRECONDITION_REQUIRED'; break;
            case self::HTTP_PROXY_AUTHENTICATION_REQUIRED: $r = 'PROXY_AUTHENTICATION_REQUIRED'; break;
            case self::HTTP_RANGE_NOT_SATISFIABLE: $r = 'RANGE_NOT_SATISFIABLE'; break;
            case self::HTTP_REQUEST_TIMEOUT: $r = 'REQUEST_TIMEOUT'; break;
            case self::HTTP_RESET_CONTENT: $r = 'RESET_CONTENT'; break;
            case self::HTTP_SEE_OTHER: $r = 'SEE_OTHER'; break;
            case self::HTTP_SERVICE_UNAVAILABLE: $r = 'SERVICE_UNAVAILABLE'; break;
            case self::HTTP_SWITCHING_PROTOCOLS: $r = 'SWITCHING_PROTOCOLS'; break;
            case self::HTTP_TEMPORARY_REDIRECT: $r = 'TEMPORARY_REDIRECT'; break;
            case self::HTTP_TOO_MANY_REQUESTS: $r = 'TOO_MANY_REQUESTS'; break;
            case self::HTTP_UNAUTHORIZED: $r = 'UNAUTHORIZED'; break;
            case self::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS: $r = 'UNAVAILABLE_FOR_LEGAL_REASONS'; break;
            case self::HTTP_UNSUPPORTED_MEDIA_TYPE: $r = 'UNSUPPORTED_MEDIA_TYPE'; break;
            case self::HTTP_UPGRADE_REQUIRED: $r = 'UPGRADE_REQUIRED'; break;
            case self::HTTP_URI_TOO_LONG: $r = 'URI_TOO_LONG'; break;
            case self::HTTP_VERSION_NOT_SUPPORTED: $r = 'HTTP_VERSION_NOT_SUPPORTED'; break;
            default: throw new UnexpectedValueException(\sprintf('Unknown integer (%d) cannot be translated', $value));
        }

        return \ucwords(\strtolower(\str_replace('_', ' ', $r)));
    }

    public static function codeFromString(string $value): int
    {
        $needle = \str_replace(' ', '_', \strtoupper(\preg_replace('/[^A-Za-z\s_]/', '', \trim($value))));
        if (\substr($needle, 0, 5) == 'HTTP_') $needle = \substr($needle, 5);

        switch ($needle)
        {
            case 'ACCEPTED': return self::HTTP_ACCEPTED;
            case 'ACCESS_DENIED': return self::HTTP_FORBIDDEN;
            case 'AUTH_REQUIRED': return self::HTTP_UNAUTHORIZED;
            case 'AUTHENTICATION_REQUIRED': return self::HTTP_UNAUTHORIZED;
            case 'AUTHORIZATION_REQUIRED': return self::HTTP_UNAUTHORIZED;
            case 'BAD_GATEWAY': return self::HTTP_BAD_GATEWAY;
            case 'BAD_REQUEST': return self::HTTP_BAD_REQUEST;
            case 'CONFLICT': return self::HTTP_CONFLICT;
            case 'CONTINUE': return self::HTTP_CONTINUE;
            case 'CREATED': return self::HTTP_CREATED;
            case 'EMPTY_CONTENT': return self::HTTP_NO_CONTENT;
            case 'EXPECTATION_FAILED': return self::HTTP_EXPECTATION_FAILED;
            case 'FORBIDDEN': return self::HTTP_FORBIDDEN;
            case 'FOUND': return self::HTTP_FOUND;
            case 'GATEWAY_TIMEOUT': return self::HTTP_GATEWAY_TIMEOUT;
            case 'GONE': return self::HTTP_GONE;
            case 'IM_A_TEAPOT': return self::HTTP_IM_A_TEAPOT;
            case 'INTERNAL_ERROR': return self::HTTP_INTERNAL_SERVER_ERROR;
            case 'INTERNAL_SERVER_ERROR': return self::HTTP_INTERNAL_SERVER_ERROR;
            case 'INTERNAL': return self::HTTP_INTERNAL_SERVER_ERROR;
            case 'LENGTH_REQUIRED': return self::HTTP_LENGTH_REQUIRED;
            case 'METHOD_NOT_ALLOWED': return self::HTTP_METHOD_NOT_ALLOWED;
            case 'MOVED_PERMANENTLY': return self::HTTP_MOVED_PERMANENTLY;
            case 'MOVED': return self::HTTP_FOUND;
            case 'MULTIPLE_CHOICE': return self::HTTP_MULTIPLE_CHOICES;
            case 'MULTIPLE_CHOICES': return self::HTTP_MULTIPLE_CHOICES;
            case 'NO_CONTENT': return self::HTTP_NO_CONTENT;
            case 'NOT_ACCEPTABLE': return self::HTTP_NOT_ACCEPTABLE;
            case 'NOT_AUTHENTICATED': return self::HTTP_UNAUTHORIZED;
            case 'NOT_AUTHORIZED': return self::HTTP_UNAUTHORIZED;
            case 'NOT_FOUND': return self::HTTP_NOT_FOUND;
            case 'NOT_IMPLEMENTED': return self::HTTP_NOT_IMPLEMENTED;
            case 'NOT_MODIFIED': return self::HTTP_NOT_MODIFIED;
            case 'OK': return self::HTTP_OK;
            case 'OKAY': return self::HTTP_OK;
            case 'OTHER': return self::HTTP_SEE_OTHER;
            case 'PARTIAL_CONTENT': return self::HTTP_PARTIAL_CONTENT;
            case 'PAYLOAD_TOO_LARGE': return self::HTTP_PAYLOAD_TOO_LARGE;
            case 'PERMANENT_REDIRECT': return self::HTTP_PERMANENT_REDIRECT;
            case 'PERMANENT': return self::HTTP_MOVED_PERMANENTLY;
            case 'PRECONDITION_FAILED': return self::HTTP_PRECONDITION_FAILED;
            case 'PRECONDITION_REQUIRED': return self::HTTP_PRECONDITION_REQUIRED;
            case 'PROXY_AUTH_REQUIRED': return self::HTTP_PROXY_AUTHENTICATION_REQUIRED;
            case 'PROXY_AUTHENTICATION_REQUIRED': return self::HTTP_PROXY_AUTHENTICATION_REQUIRED;
            case 'PROXY_AUTHORIZATION_REQUIRED': return self::HTTP_PROXY_AUTHENTICATION_REQUIRED;
            case 'PROXY_UNAUTHENTICATED': return self::HTTP_PROXY_AUTHENTICATION_REQUIRED;
            case 'PROXY_UNAUTHORIZED': return self::HTTP_PROXY_AUTHENTICATION_REQUIRED;
            case 'RANGE_NOT_SATISFIABLE': return self::HTTP_RANGE_NOT_SATISFIABLE;
            case 'REQUEST_TIMEOUT': return self::HTTP_REQUEST_TIMEOUT;
            case 'RESET_CONTENT': return self::HTTP_RESET_CONTENT;
            case 'RESOURCE_NOT_FOUND': return self::HTTP_NOT_FOUND;
            case 'SEE_OTHER': return self::HTTP_SEE_OTHER;
            case 'SERVICE_UNAVAILABLE': return self::HTTP_SERVICE_UNAVAILABLE;
            case 'SWITCHING_PROTOCOLS': return self::HTTP_SWITCHING_PROTOCOLS;
            case 'TEMPORARY_REDIRECT': return self::HTTP_TEMPORARY_REDIRECT;
            case 'TEMPORARY': return self::HTTP_TEMPORARY_REDIRECT;
            case 'TOO_MANY_REQUESTS': return self::HTTP_TOO_MANY_REQUESTS;
            case 'UNAUTHENTICATED': return self::HTTP_UNAUTHORIZED;
            case 'UNAUTHORIZED': return self::HTTP_UNAUTHORIZED;
            case 'UNAVAILABLE_FOR_LEGAL_REASONS': return self::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS;
            case 'UNSUPPORTED_MEDIA_TYPE': return self::HTTP_UNSUPPORTED_MEDIA_TYPE;
            case 'UPGRADE_REQUIRED': return self::HTTP_UPGRADE_REQUIRED;
            case 'URI_TOO_LONG': return self::HTTP_URI_TOO_LONG;
            case 'VERSION_NOT_SUPPORTED': return self::HTTP_VERSION_NOT_SUPPORTED;
            default: throw new UnexpectedValueException(\sprintf('Unknown string (%s) cannot be translated', $needle));
        }
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function isRedirect(bool $range_check = true): bool
    {
        return self::isRedirectFromCode($this->code, $range_check);
    }

    public static function isRedirectFromCode(int $code, bool $range_check = true): bool
    {
        switch ($code)
        {
            case self::HTTP_MULTIPLE_CHOICES:
            case self::HTTP_MOVED_PERMANENTLY:
            case self::HTTP_FOUND:
            case self::HTTP_SEE_OTHER:
            case self::HTTP_TEMPORARY_REDIRECT:
            case self::HTTP_PERMANENT_REDIRECT:
                return true;
            default:
                return $range_check ? ($code >= 300 && $code < 400) : false;
        }
    }

    public function jsonSerialize(): mixed
    {
        return ['code' => $this->code, 'name' => self::codeFromInt($this->code)];
    }

    public function setCode(int|string $value): void
    {
        if (\is_string($value))
        {
            $this->code = self::codeFromString($value);
        }
        else if ($value < self::MIN_CODE || $value > self::MAX_CODE)
        {
            throw new \OutOfBoundsException(\sprintf('Value must be between %d-%d, %d given', self::MIN_CODE, self::MAX_CODE, $value));
        }
        else
        {
            $this->code = $value;
        }
    }

    public function __toString(): string
    {
       return self::codeFromInt($this->code);
    }
}
