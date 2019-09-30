<?php

namespace App\Library\Service;

/**
 * Created by PhpStorm.
 * User: zzh
 * Date: 2019/4/23
 * Time: 14:12
 */
class MessageKeyService
{
    /**
     * 静态对象
     * @var null
     */
    protected static $instance = null;

    /**
     * 获取实例
     * @return null|static
     */
    public static function instance()
    {
        if (empty(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public function getMessageKey($platformId, $personalUid, $opponentUid)
    {
        return md5($platformId . $personalUid . $opponentUid . SEND_MESSAGE_KEY);
    }
}