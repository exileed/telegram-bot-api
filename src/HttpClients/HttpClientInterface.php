<?php

namespace Telegram\Bot\HttpClients;

use Telegram\Bot\Exceptions\TelegramSDKException;

/**
 * Interface HttpClientInterface.
 */
interface HttpClientInterface
{
    /**
     * @param            $url
     * @param            $method
     * @param array      $headers
     * @param array      $options
     * @param int        $timeOut
     * @param bool|false $isAsyncRequest
     * @param int        $connectTimeOut
     *
     * @throws TelegramSDKException
     *
     * @return mixed
     */
    public function send(
        $url,
        $method,
        array $headers = [],
        array $options = [],
        $timeOut = 30,
        $isAsyncRequest = false,
        $connectTimeOut = 10
    );
}
