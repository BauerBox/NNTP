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

    protected $code;
    protected $lineBuffer;
    protected $message;

    public function __construct($code, $message, array $lines = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->lineBuffer = $lines;
    }

    public function attachLineBuffer(array $lineBuffer)
    {
        $this->lineBuffer = $lineBuffer;

        return $this;
    }

    public function getStatus()
    {
        return (int) $this->code;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getLineBuffer()
    {
        return $this->lineBuffer;
    }

    public function isError()
    {
        return (0 < preg_match(sprintf('@^%s@', self::TYPE_ERROR), $this->code));
    }

    public function isFailure()
    {
        return ($this->isError() || 0 < preg_match(sprintf('@^%s@', self::TYPE_FAIL), $this->code));
    }

    public function isOk()
    {
        return (0 < preg_match(sprintf('@^(%s|%s)@', self::TYPE_OK_COMPLETE, self::TYPE_OK_INCOMPLETE), $this->code));
    }

    public function isComplete()
    {
        return (0 < preg_match(sprintf('@^(%s)@', self::TYPE_OK_COMPLETE), $this->code));
    }

    public function requiresAuthentication()
    {
        return ((int) $this->code) === 480;
    }

    public static function parseStatusResponse($responseString)
    {
        $responseString = trim(substr($responseString, 0, -1));
        if ($responseString !== false) {
            $response = array(
                'code' => substr($responseString, 0, 3),
                'message' => ltrim(substr($responseString, 4))
            );

            return new Response($response['code'], $response['message']);
        }

        throw new \Exception('Empty response');
    }
}
