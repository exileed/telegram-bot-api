<?php

namespace Telegram\Bot;

use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\HttpClients\GuzzleHttpClient;
use Telegram\Bot\HttpClients\HttpClientInterface;

/**
 * Class TelegramClient.
 */
class TelegramClient
{
    /**
     * @const string Telegram Bot API URL.
     */
    public const BASE_BOT_URL = 'https://api.telegram.org/bot';

    /**
     * @const int The timeout in seconds for a request that contains file uploads.
     */
    public const DEFAULT_FILE_UPLOAD_REQUEST_TIMEOUT = 3600;

    /**
     * @const int The timeout in seconds for a request that contains video uploads.
     */
    public const DEFAULT_VIDEO_UPLOAD_REQUEST_TIMEOUT = 7200;

    /**
     * @var HttpClientInterface|null HTTP Client
     */
    protected $httpClientHandler;

    /**
     * Instantiates a new TelegramClient object.
     *
     * @param HttpClientInterface|null $httpClientHandler
     */
    public function __construct(HttpClientInterface $httpClientHandler = null)
    {
        $this->httpClientHandler = $httpClientHandler ?: new GuzzleHttpClient();
    }

    /**
     * Returns the HTTP client handler.
     *
     * @return HttpClientInterface
     */
    public function getHttpClientHandler()
    {
        return $this->httpClientHandler;
    }

    /**
     * Sets the HTTP client handler.
     *
     * @param HttpClientInterface $httpClientHandler
     */
    public function setHttpClientHandler(HttpClientInterface $httpClientHandler)
    {
        $this->httpClientHandler = $httpClientHandler;
    }

    /**
     * Send an API request and process the result.
     *
     * @param TelegramRequest $request
     *
     * @return TelegramResponse
     * @throws TelegramSDKException
     *
     */
    public function sendRequest(TelegramRequest $request)
    {
        [$url, $method, $headers, $isAsyncRequest] = $this->prepareRequest($request);

        $timeOut        = $request->getTimeOut();
        $connectTimeOut = $request->getConnectTimeOut();

        $options = $this->getOptions($request, $method);

        $rawResponse = $this->httpClientHandler->send(
            $url,
            $method,
            $headers,
            $options,
            $timeOut,
            $isAsyncRequest,
            $connectTimeOut
        );

        $returnResponse = $this->getResponse($request, $rawResponse);

        if ($returnResponse->isError()) {
            throw $returnResponse->getThrownException();
        }

        return $returnResponse;
    }

    /**
     * Prepares the API request for sending to the client handler.
     *
     * @param TelegramRequest $request
     *
     * @return array
     */
    public function prepareRequest(TelegramRequest $request)
    {
        $url = $this->getBaseBotUrl() . $request->getAccessToken() . '/' . $request->getEndpoint();

        return [
            $url,
            $request->getMethod(),
            $request->getHeaders(),
            $request->isAsyncRequest(),
        ];
    }

    /**
     * Returns the base Bot URL.
     *
     * @return string
     */
    public function getBaseBotUrl()
    {
        return static::BASE_BOT_URL;
    }

    /**
     * @param \Telegram\Bot\TelegramRequest $request
     * @param string $method
     * @return array
     * +     */
    private function getOptions(TelegramRequest $request, $method)
    {
        if ($method === 'POST') {
            return $request->getPostParams();
        }

        return ['query' => $request->getParams()];
    }

    /**
     * Creates response object.
     *
     * @param TelegramRequest                    $request
     * @param ResponseInterface|PromiseInterface $response
     *
     * @return TelegramResponse
     */
    protected function getResponse(TelegramRequest $request, $response)
    {
        return new TelegramResponse($request, $response);
    }
}
