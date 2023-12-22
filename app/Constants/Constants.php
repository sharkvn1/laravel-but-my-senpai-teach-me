<?php

namespace App\Constants;

class Constants
{
    // Common
    public const PROJECT_NAME = 'G-Performance Service';
    public const ID = 'id';
    public const RESPONSE_MESSAGE = 'message';
    public const RESPONSE_DATA = 'data';
    public const RESPONSE_CODE = 'code';
    public const RESPONSE_MESSAGE_SUCCESS = 'SUCCESS';
    public const RESPONSE_MESSAGE_FAIL = 'Fail';
    public const RESPONSE_NOT_FOUND = 'Not Found';
    public const RESPONSE_REQUEST_TIMEOUT = 'Request timeout';
    public const RESPONSE_SERVER_ERROR = 'Server error';
    public const RESPONSE_UNAUTHENTICATED = 'Unauthenticated';
    public const RESPONSE_UNAUTHORIZED = 'Unauthorized';
    public const MESSAGE_ERROR = 'Error: ';

    // Query constants
    public const QUERY_WHERE = 'queries';
    public const QUERY_WHERE_IN = 'in_queries';
    public const QUERY_WHERE_NOT_IN = 'not_in_queries';
    public const QUERY_WHERE_BETWEEN = 'between_queries';
    public const QUERY_WHERE_DATE = 'date_queries';
}
