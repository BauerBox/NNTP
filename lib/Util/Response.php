<?php

namespace BauerBox\NNTP\Util;

/**
 * Response Code Utility Class
 */
class Response
{
    // RFC 3977 :: Section 3.2
    const TYPE_INFORMATIVE = '1[0-9][0-9]';
    const TYPE_OK_COMPLETE = '2[0-9][0-9]';
    const TYPE_OK_INCOMPLETE = '3[0-9][0-9]';
    const TYPE_FAIL = '4[0-9][0-9]';
    const TYPE_ERROR = '5[0-9][0-9]';

    const CATEGORY_CONNECTION = '[0-9]0[0-9]';
    const CATEGORY_NEWSGROUP = '[0-9]1[0-9]';
    const CATEGORY_ARTICLE = '[0-9]2[0-9]';
    const CATEGORY_DISTRIBUTION = '[0-9]3[0-9]';
    const CATEGORY_POST = '[0-9]4[0-9]';
    const CATEGORY_AUTHENTICATION = '[0-9]8[0-9]';
    const CATEGORY_PRIVATE = '[0-9]9[0-9]';

    public static function isError($response)
    {
        return (0 < preg_match(sprintf('@^%s@', self::TYPE_ERROR), $response));
    }

    public static function parseResponseString($responseString)
    {
        $response = array();

        if (preg_match('@^(?P<code>[0-9]{3})(?P<message>.*)$@', $responseString, $match)) {
            $response['code'] = $match['code'];
            $response['message'] = $match['message'];
        }

        if (false !== $pos = strpos(trim($responseString), "\r\n")) {
            $response['split'] = $pos;
        }

        $response['raw'] = $responseString;

        return $response;
    }
}
