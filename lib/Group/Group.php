<?php

namespace BauerBox\NNTP\Group;

class Group
{
    const POSTING_UNKNOWN = 0;
    const POSTING_ALLOWED = 1;
    const POSTING_NOT_ALLOWED = 2;
    const POSTING_MODERATED = 3;

    protected $active;

    protected $group;

    protected $maxPosts;

    protected $postingAllowed;

    protected $waterMarkHigh;
    protected $waterMarkLow;

    protected $regexGroup = '@(?P<posts>[0-9]+) (?P<low>[0-9]+) (?P<high>[0-9]+) (?P<group>[^ ]+)@i';
    protected $regexList = '@(?P<group>[^ ]+) (?P<high>[0-9]+) (?P<low>[0-9]+) (?P<posting>[y|m|n]?)@i';

    public function __toString()
    {
        return $this->group;
    }

    public function isActive()
    {
        return $this->active;
    }

    protected function parseGroupResponseString($string)
    {
        if (0 < preg_match($this->regexGroup, $string, $match)) {
            $this->waterMarkHigh = (int) $match['high'];
            $this->waterMarkLow = (int) $match['low'];

            $this->maxPosts = (int) $match['posts'];

            if ($this->maxPosts < 1 || $this->waterMarkHigh < $this->waterMarkLow) {
                $this->active = false;
            } else {
                $this->active = true;
            }

            $this->group = $match['group'];

            $this->postingAllowed = self::POSTING_UNKNOWN;

            return $this;
        }

        throw new \Exception('Invalid GROUP response');
    }

    protected function parseListResponseString($string)
    {
        if (0 < preg_match($this->regexList, $string, $match)) {
            $this->waterMarkHigh = (int) $match['high'];
            $this->waterMarkLow = (int) $match['low'];

            $this->maxPosts = $this->waterMarkHigh = $this->waterMarkLow;

            if ($this->maxPosts < 1 || $this->waterMarkHigh < $this->waterMarkLow) {
                $this->active = false;
            } else {
                $this->active = true;
            }

            $this->group = $match['group'];

            switch (strtolower($match['posting'])) {
                case 'y':
                    $this->postingAllowed = self::POSTING_ALLOWED;
                    break;
                case 'n':
                    $this->postingAllowed = self::POSTING_NOT_ALLOWED;
                    break;
                case 'm':
                    $this->postingAllowed = self::POSTING_MODERATED;
                    break;
                default:
                    $this->postingAllowed = self::POSTING_UNKNOWN;
                    break;
            }

            return $this;
        }

        throw new \Exception('Invalid LIST response');
    }

    public function getMaxPosts()
    {
        return $this->maxPosts;
    }

    public function getHighWaterMark()
    {
        return $this->waterMarkHigh;
    }

    public function getLowWaterMark()
    {
        return $this->waterMarkLow;
    }

    public static function instanceFromListResponse($string)
    {
        $instance = new Group();
        $instance->parseListResponseString($string);
        return $instance;
    }

    public static function instanceFromGroupResponse($string)
    {
        $instance = new Group();
        $instance->parseGroupResponseString($string);
        return $instance;
    }
}
