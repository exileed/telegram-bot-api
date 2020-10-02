<?php

namespace Telegram\Bot;

use Illuminate\Contracts\Container\Container;
use Telegram\Bot\Callbacks\CallbackCommandBus;
use Telegram\Bot\Commands\CommandBus;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\HttpClients\HttpClientInterface;
use Telegram\Bot\Objects\BotCommand;
use Telegram\Bot\Objects\Chat;
use Telegram\Bot\Objects\ChatMember;
use Telegram\Bot\Objects\File;
use Telegram\Bot\Objects\MaskPosition;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Poll;
use Telegram\Bot\Objects\StickerSet;
use Telegram\Bot\Objects\UnknownObject;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Objects\User;
use Telegram\Bot\Objects\UserProfilePhotos;
use Telegram\Bot\Objects\WebhookInfo;

/**
 * Class Api.
 *
 * @mixin Commands\CommandBus
 * @mixin Callbacks\CallbackCommandBus
 */
class Api
{
    /**
     * @var string Version number of the Telegram Bot PHP SDK.
     */
    const VERSION = '0.2.0';

    /**
     * @var Container IoC Container
     */
    protected static $container = null;
    /**
     * @var TelegramClient The Telegram client service.
     */
    protected $client;
    /**
     * @var string Telegram Bot API Access Token.
     */
    protected $accessToken = null;
    /**
     * @var TelegramResponse|null Stores the last request made to Telegram Bot API.
     */
    protected $lastResponse;
    /**
     * @var bool Indicates if the request to Telegram will be asynchronous (non-blocking).
     */
    protected $isAsyncRequest = false;
    /**
     * @var CommandBus|null Telegram Command Bus.
     */
    protected $commandBus = null;

    /**
     * @var CallbackCommandBus|null Telegram Command Bus.
     */
    protected $callbackBus = null;

    /**
     * Timeout of the request in seconds.
     *
     * @var int
     */
    protected $timeOut = 60;

    /**
     * Connection timeout of the request in seconds.
     *
     * @var int
     */
    protected $connectTimeOut = 10;

    /**
     * Instantiates a new Telegram super-class object.
     *
     *
     * @param string              $token             The Telegram Bot API Access Token.
     * @param bool                $async             (Optional) Indicates if the request to Telegram
     *                                               will be asynchronous (non-blocking).
     * @param HttpClientInterface $httpClientHandler (Optional) Custom HTTP Client Handler.
     *
     * @throws TelegramSDKException
     */
    public function __construct(string $token, bool $async = false, HttpClientInterface $httpClientHandler = null)
    {
        $this->accessToken = $token;

        if (isset($async)) {
            $this->setAsyncRequest($async);
        }

        $this->client      = new TelegramClient($httpClientHandler);
        $this->commandBus  = new CommandBus($this);
        $this->callbackBus = new CallbackCommandBus($this);
    }

    /**
     * Make this request asynchronous (non-blocking).
     *
     * @param bool $isAsyncRequest
     *
     * @return Api
     */
    public function setAsyncRequest($isAsyncRequest)
    {
        $this->isAsyncRequest = $isAsyncRequest;

        return $this;
    }

    /**
     * Invoke Bots Manager.
     *
     * @param $config
     *
     * @return BotsManager
     */
    public static function manager($config)
    {
        return new BotsManager($config);
    }

    /**
     * Set the IoC Container.
     *
     * @param $container Container instance
     *
     * @return void
     */
    public static function setContainer(Container $container)
    {
        self::$container = $container;
    }

    /**
     * Returns the TelegramClient service.
     *
     * @return TelegramClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Returns the last response returned from API request.
     *
     * @return TelegramResponse
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * A simple method for testing your bot's auth token.
     * Returns basic information about the bot in form of a User object.
     *
     * @link https://core.telegram.org/bots/api#getme
     *
     * @throws TelegramSDKException
     *
     * @return User
     */
    public function getMe()
    {
        $response = $this->post('getMe');

        return new User($response->getDecodedBody());
    }

    /**
     * Sends a POST request to Telegram Bot API and returns the result.
     *
     * @param string $endpoint
     * @param array  $params
     * @param bool   $fileUpload Set true if a file is being uploaded.
     *
     * @return TelegramResponse
     *
     * @throws TelegramSDKException
     */
    protected function post(string $endpoint, array $params = [], $fileUpload = false)
    {
        if ($fileUpload) {
            $params = ['multipart' => $params];
        } else {
            if (array_key_exists('reply_markup', $params)) {
                $params[ 'reply_markup' ] = (string)$params[ 'reply_markup' ];
            }

            $params = ['form_params' => $params];
        }

        return $this->sendRequest(
            'POST',
            $endpoint,
            $params
        );
    }

    /**
     * Sends a request to Telegram Bot API and returns the result.
     *
     * @param string $method
     * @param string $endpoint
     * @param array  $params
     *
     * @throws TelegramSDKException
     *
     * @return TelegramResponse
     */
    protected function sendRequest(
        $method,
        $endpoint,
        array $params = []
    ) {
        $request = $this->request($method, $endpoint, $params);

        return $this->lastResponse = $this->client->sendRequest($request);
    }

    /**
     * Instantiates a new TelegramRequest entity.
     *
     * @param string $method
     * @param string $endpoint
     * @param array  $params
     *
     * @return TelegramRequest
     */
    protected function request(
        $method,
        $endpoint,
        array $params = []
    ) {
        return new TelegramRequest(
            $this->getAccessToken(),
            $method,
            $endpoint,
            $params,
            $this->isAsyncRequest(),
            $this->getTimeOut(),
            $this->getConnectTimeOut()
        );
    }

    /**
     * Returns Telegram Bot API Access Token.
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Sets the bot access token to use with API requests.
     *
     * @param string $accessToken The bot access token to save.
     *
     * @throws \InvalidArgumentException
     *
     * @return Api
     */
    public function setAccessToken($accessToken)
    {
        if (is_string($accessToken)) {
            $this->accessToken = $accessToken;

            return $this;
        }

        throw new \InvalidArgumentException('The Telegram bot access token must be of type "string"');
    }

    /**
     * Check if this is an asynchronous request (non-blocking).
     *
     * @return bool
     */
    public function isAsyncRequest()
    {
        return $this->isAsyncRequest;
    }

    /**
     * @return int
     */
    public function getTimeOut()
    {
        return $this->timeOut;
    }

    /**
     * @param int $timeOut
     *
     * @return $this
     */
    public function setTimeOut($timeOut)
    {
        $this->timeOut = $timeOut;

        return $this;
    }

    /**
     * @return int
     */
    public function getConnectTimeOut()
    {
        return $this->connectTimeOut;
    }

    /**
     * @param int $connectTimeOut
     *
     * @return $this
     */
    public function setConnectTimeOut($connectTimeOut)
    {
        $this->connectTimeOut = $connectTimeOut;

        return $this;
    }

    /**
     * Send text messages.
     *
     * <code>
     * $params = [
     *   'chat_id'                  => '',
     *   'text'                     => '',
     *   'parse_mode'               => '',
     *   'disable_web_page_preview' => '',
     *   'disable_notification'     => '',
     *   'reply_to_message_id'      => '',
     *   'reply_markup'             => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#sendmessage
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var string     $params ['text']
     * @var string     $params ['parse_mode']
     * @var bool       $params ['disable_web_page_preview']
     * @var bool       $params ['disable_notification']
     * @var int        $params ['reply_to_message_id']
     * @var string     $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return Message
     */
    public function sendMessage(array $params)
    {
        $response = $this->post('sendMessage', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Delete a message.
     *
     * <code>
     * $params = [
     *   'chat_id'                  => '',
     *   'message_id'               => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#deletemessage
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var int        $params ['message_id']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function deleteMessage(array $params)
    {
        $this->post('deleteMessage', $params);

        return true;
    }

    /**
     * Forward messages of any kind.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'from_chat_id'         => '',
     *   'disable_notification' => '',
     *   'message_id'           => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#forwardmessage
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var int        $params ['from_chat_id']
     * @var bool       $params ['disable_notification']
     * @var int        $params ['message_id']
     *
     * @throws TelegramSDKException
     *
     * @return Message
     */
    public function forwardMessage(array $params)
    {
        $response = $this->post('forwardMessage', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Send Photos.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'photo'                => '',
     *   'caption'              => '',
     *   'disable_notification' => '',
     *   'reply_to_message_id'  => '',
     *   'reply_markup'         => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#sendphoto
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var string     $params ['photo']
     * @var string     $params ['caption']
     * @var bool       $params ['disable_notification']
     * @var int        $params ['reply_to_message_id']
     * @var string     $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return Message
     */
    public function sendPhoto(array $params)
    {
        $response = $this->uploadFile('sendPhoto', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Sends a multipart/form-data request to Telegram Bot API and returns the result.
     * Used primarily for file uploads.
     *
     * @param string $endpoint
     * @param array  $params
     *
     * @throws TelegramSDKException
     *
     * @return TelegramResponse
     */
    protected function uploadFile($endpoint, array $params = [])
    {
        $multipart_params = collect($params)
            ->reject(
                function ($value) {
                    return is_null($value);
                }
            )
            ->map(
                function ($contents, $name) {
                    if ( ! is_resource($contents) && $this->isValidFileOrUrl($name, $contents)) {
                        $contents = (new InputFile($contents))->open();
                    }

                    return [
                        'name'     => $name,
                        'contents' => $contents,
                    ];
                }
            )
            //Reset the keys on the collection
            ->values()
            ->all();

        return $this->post($endpoint, $multipart_params, true);
    }

    /**
     * Determines if the string passed to be uploaded is a valid
     * file on the local file system, or a valid remote URL.
     *
     * @param string $name
     * @param string $contents
     *
     * @return bool
     */
    protected function isValidFileOrUrl($name, $contents)
    {
        //Don't try to open a url as an actual file when using this method to setWebhook.
        if ($name == 'url') {
            return false;
        }

        //If a certificate name is passed, we must check for the file existing on the local server,
        // otherwise telegram ignores the fact it wasn't sent and no error is thrown.
        if ($name == 'certificate') {
            return true;
        }

        //Is the content a valid file name.
        if (is_readable($contents)) {
            return true;
        }

        //Is the content a valid URL
        return filter_var($contents, FILTER_VALIDATE_URL);
    }

    /**
     * Send regular audio files.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'audio'                => '',
     *   'duration'             => '',
     *   'performer'            => '',
     *   'title'                => '',
     *   'disable_notification' => '',
     *   'reply_to_message_id'  => '',
     *   'reply_markup'         => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#sendaudio
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var string     $params ['audio']
     * @var int        $params ['duration']
     * @var string     $params ['performer']
     * @var string     $params ['title']
     * @var bool       $params ['disable_notification']
     * @var int        $params ['reply_to_message_id']
     * @var string     $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return Message
     */
    public function sendAudio(array $params)
    {
        $response = $this->uploadFile('sendAudio', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Send send a game.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'game_short_name'      => '',
     *   'disable_notification' => '',
     *   'reply_to_message_id'  => '',
     *   'reply_markup'         => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#sendgame
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var string     $params ['game_short_name']
     * @var bool       $params ['disable_notification']
     * @var string     $params ['reply_to_message_id']
     * @var string     $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return Message
     */
    public function sendGame(array $params)
    {
        $response = $this->uploadFile('sendGame', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Send general files.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'document'             => '',
     *   'caption'              => '',
     *   'disable_notification' => '',
     *   'reply_to_message_id'  => '',
     *   'reply_markup'         => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#senddocument
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var string     $params ['document']
     * @var string     $params ['caption']
     * @var bool       $params ['disable_notification']
     * @var int        $params ['reply_to_message_id']
     * @var string     $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return Message
     */
    public function sendDocument(array $params)
    {
        $response = $this->uploadFile('sendDocument', $params);

        return new Message($response->getDecodedBody());
    }


    /**
     *  Send animation files.
     *
     * <code>
     * $params = [
     *   'chat_id'               => '',
     *   'animation'             => '',
     *   'duration'              => '',
     *   'duration'              => '',
     *   'width'                 => '',
     *   'height'                => '',
     *   'thumb'                 => '',
     *   'caption'               => '',
     *   'parse_mode'            => '',
     *   'disable_notification'  => '',
     *   'reply_to_message_id'   => '',
     *   'reply_markup'          => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#sendanimation
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var string     $params ['animation']
     * @var string     $params ['duration']
     * @var string     $params ['width']
     * @var string     $params ['height']
     * @var string     $params ['thumb']
     * @var string     $params ['caption']
     * @var string     $params ['parse_mode']
     * @var bool       $params ['disable_notification']
     * @var int        $params ['reply_to_message_id']
     * @var string     $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return Message
     */
    public function sendAnimation(array $params)
    {
        $response = $this->uploadFile('sendAnimation', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Send .webp stickers.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'sticker'              => '',
     *   'disable_notification' => '',
     *   'reply_to_message_id'  => '',
     *   'reply_markup'         => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#sendsticker
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var string     $params ['sticker']
     * @var bool       $params ['disable_notification']
     * @var int        $params ['reply_to_message_id']
     * @var string     $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return Message
     */
    public function sendSticker(array $params)
    {
        if (is_file($params[ 'sticker' ]) && (pathinfo($params[ 'sticker' ], PATHINFO_EXTENSION) !== 'webp')) {
            throw new TelegramSDKException('Invalid Sticker Provided. Supported Format: Webp');
        }

        $response = $this->uploadFile('sendSticker', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Send Video File, Telegram clients support mp4 videos (other formats may be sent as Document).
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'video'                => '',
     *   'duration'             => '',
     *   'width'                => '',
     *   'height'               => '',
     *   'caption'              => '',
     *   'disable_notification' => '',
     *   'reply_to_message_id'  => '',
     *   'reply_markup'         => '',
     * ];
     * </code>
     *
     * @see  sendDocument
     * @link https://core.telegram.org/bots/api#sendvideo
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var string     $params ['video']
     * @var int        $params ['duration']
     * @var int        $params ['width']
     * @var int        $params ['height']
     * @var string     $params ['caption']
     * @var bool       $params ['disable_notification']
     * @var int        $params ['reply_to_message_id']
     * @var string     $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return Message
     */
    public function sendVideo(array $params)
    {
        $response = $this->uploadFile('sendVideo', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Method to send video messages.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'video'                => '',
     *   'duration'             => '',
     *   'length'               => '',
     *   'disable_notification' => '',
     *   'reply_to_message_id'  => '',
     *   'reply_markup'         => '',
     * ];
     * </code>
     *
     * @see  sendDocumen
     * @link https://core.telegram.org/bots/api#sendvideonote
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var string     $params ['video_note']
     * @var int        $params ['duration']
     * @var int        $params ['length']
     * @var bool       $params ['disable_notification']
     * @var int        $params ['reply_to_message_id']
     * @var string     $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return Message
     */
    public function sendVideoNote(array $params)
    {
        $response = $this->uploadFile('sendVideoNote', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Send voice audio files.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'voice'                => '',
     *   'duration'             => '',
     *   'disable_notification' => '',
     *   'reply_to_message_id'  => '',
     *   'reply_markup'         => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#sendaudio
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var string     $params ['voice']
     * @var int        $params ['duration']
     * @var bool       $params ['disable_notification']
     * @var int        $params ['reply_to_message_id']
     * @var string     $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return Message
     */
    public function sendVoice(array $params)
    {
        $response = $this->uploadFile('sendVoice', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Send point on the map.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'latitude'             => '',
     *   'longitude'            => '',
     *   'live_period'          => '',
     *   'disable_notification' => '',
     *   'reply_to_message_id'  => '',
     *   'reply_markup'         => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#sendlocation
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var float      $params ['latitude']
     * @var float      $params ['longitude']
     * @var int        $params ['live_period']
     * @var bool       $params ['disable_notification']
     * @var int        $params ['reply_to_message_id']
     * @var string     $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return Message
     */
    public function sendLocation(array $params)
    {
        $response = $this->post('sendLocation', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Edit live location messages sent by the bot or via the bot.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'message_id'           => '',
     *   'inline_message_id'    => '',
     *   'latitude'             => '',
     *   'longitude'            => '',
     *   'reply_markup'         => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#editmessagelivelocation
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var int        $params ['message_id']
     * @var int        $params ['inline_message_id']
     * @var float      $params ['latitude']
     * @var float      $params ['longitude']
     * @var string     $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function editMessageLiveLocation(array $params)
    {
        $this->post('editMessageLiveLocation', $params);

        return true;
    }

    /**
     * Edit edit audio, document, photo, or video messages.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'message_id'           => '',
     *   'inline_message_id'    => '',
     *   'media'                => '',
     *   'reply_markup'         => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#editmessagemedia
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var int        $params ['message_id']
     * @var int        $params ['inline_message_id']
     * @var float      $params ['media']
     * @var string     $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function editMessageMedia(array $params)
    {
        $this->post('editMessageMedia', $params);

        return true;
    }


    /**
     * Stop updating a live location message sent by the bot or via the bot.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'message_id'           => '',
     *   'inline_message_id'    => '',
     *   'reply_markup'         => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#stopMessageLiveLocation
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var int        $params ['message_id']
     * @var int        $params ['inline_message_id']
     * @var string     $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function stopMessageLiveLocation(array $params)
    {
        $this->post('stopMessageLiveLocation', $params);

        return true;
    }

    /**
     * Send information about a venue.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'latitude'             => '',
     *   'longitude'            => '',
     *   'title'                => '',
     *   'address'              => '',
     *   'foursquare_id'        => '',
     *   'disable_notification' => '',
     *   'reply_to_message_id'  => '',
     *   'reply_markup'         => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#sendvenue
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var float      $params ['latitude']
     * @var float      $params ['longitude']
     * @var string     $params ['title']
     * @var string     $params ['address']
     * @var string     $params ['foursquare_id']
     * @var bool       $params ['disable_notification']
     * @var int        $params ['reply_to_message_id']
     * @var string     $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return Message
     */
    public function sendVenue(array $params)
    {
        $response = $this->post('sendVenue', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Send phone contacts.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'phone_number'         => '',
     *   'first_name'           => '',
     *   'last_name'            => '',
     *   'vcard'                => '',
     *   'disable_notification' => '',
     *   'reply_to_message_id'  => '',
     *   'reply_markup'         => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#sendcontact
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var string     $params ['phone_number']
     * @var string     $params ['first_name']
     * @var string     $params ['last_name']
     * @var string     $params ['vcard']
     * @var bool       $params ['disable_notification']
     * @var int        $params ['reply_to_message_id']
     * @var string     $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return Message
     */
    public function sendContact(array $params)
    {
        $response = $this->post('sendContact', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Send a native pol.
     *
     * <code>
     * $params = [
     *   'chat_id' => '',
     *   'question' => '',
     *   'options' => '',
     *   'is_anonymous' => true,
     *   'type' => '',
     *   'allows_multiple_answers' => true,
     *   'correct_option_id' => '',
     *   'explanation' => '',
     *   'explanation_parse_mode' => '',
     *   'open_period' => '',
     *   'close_date' => '',
     *   'is_closed' => true,
     *   'disable_notification' => '',
     *   'reply_to_message_id'  => '',
     *   'reply_markup'         => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#sendpoll
     *
     * @param array $params
     *
     * @var int|string $params ['chat_id']
     * @var string $params ['question']
     * @var string $params ['options']
     * @var bool $params ['is_anonymous']
     * @var string $params ['type']
     * @var bool $params ['allows_multiple_answers']
     * @var int $params ['correct_option_id']
     * @var string $params ['explanation']
     * @var string $params ['explanation_parse_mode']
     * @var int $params ['open_period']
     * @var int $params ['close_date']
     * @var bool $params ['is_closed']
     * @var bool $params ['disable_notification']
     * @var int $params ['reply_to_message_id']
     * @var string $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return Message
     */
    public function sendPoll(array $params)
    {
        $response = $this->post('sendPoll', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Broadcast a Chat Action.
     *
     * <code>
     * $params = [
     *   'chat_id' => '',
     *   'action'  => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#sendchataction
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var string     $params ['action']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function sendChatAction(array $params)
    {
        $validActions = [
            'typing',
            'upload_photo',
            'record_video',
            'upload_video',
            'record_audio',
            'upload_audio',
            'upload_document',
            'find_location',
            'record_video_note',
            'upload_video_note',
        ];

        if (isset($params[ 'action' ]) && in_array($params[ 'action' ], $validActions)) {
            $this->post('sendChatAction', $params);

            return true;
        }

        throw new TelegramSDKException('Invalid Action! Accepted value: ' . implode(', ', $validActions));
    }

    /**
     * Returns a list of profile pictures for a user.
     *
     * <code>
     * $params = [
     *   'user_id' => '',
     *   'offset'  => '',
     *   'limit'   => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#getuserprofilephotos
     *
     * @param array $params
     *
     * @var int     $params ['user_id']
     * @var int     $params ['offset']
     * @var int     $params ['limit']
     *
     * @throws TelegramSDKException
     *
     * @return UserProfilePhotos
     */
    public function getUserProfilePhotos(array $params)
    {
        $response = $this->post('getUserProfilePhotos', $params);

        return new UserProfilePhotos($response->getDecodedBody());
    }

    /**
     * Returns basic info about a file and prepare it for downloading.
     *
     * <code>
     * $params = [
     *   'file_id' => '',
     * ];
     * </code>
     *
     * The file can then be downloaded via the link
     * https://api.telegram.org/file/bot<token>/<file_path>,
     * where <file_path> is taken from the response.
     *
     * @link https://core.telegram.org/bots/api#getFile
     *
     * @param array $params
     *
     * @var string  $params ['file_id']
     *
     * @throws TelegramSDKException
     *
     * @return File
     */
    public function getFile(array $params)
    {
        $response = $this->post('getFile', $params);

        return new File($response->getDecodedBody());
    }

    /**
     * Kick a user from a group or a supergroup.
     *
     * In the case of supergroups, the user will not be able to return to the group on their own using
     * invite links etc., unless unbanned first.
     *
     * The bot must be an administrator in the group for this to work.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'user_id'              => '',
     *   'until_date'           => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#kickchatmember
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var int        $params ['user_id']
     * @var int        $params ['until_date']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function kickChatMember(array $params)
    {
        $this->post('kickChatMember', $params);

        return true;
    }

    /**
     * Leave a group, supergroup or channel.
     *
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#leavechat
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function leaveChat(array $params)
    {
        $this->post('leaveChat', $params);

        return true;
    }

    /**
     * Unban a previously kicked user in a supergroup.
     *
     * The user will not return to the group automatically, but will be able to join via link, etc.
     *
     * The bot must be an administrator in the group for this to work.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'user_id'              => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#unbanchatmember
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var int        $params ['user_id']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function unbanChatMember(array $params)
    {
        $this->post('unbanChatMember', $params);

        return true;
    }

    /**
     * Get up to date information about the chat (current name of the user for one-on-one conversations,
     * current username of a user, group or channel,.
     *
     * <code>
     * $params = [
     *   'chat_id'  => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#getchat
     *
     * @param array    $params
     *
     * @var string|int $params ['chat_id'] Unique identifier for the target chat or username of the target supergroup or channel (in the format @channelusername)
     *
     * @throws TelegramSDKException
     *
     * @return Chat
     */
    public function getChat(array $params)
    {
        $response = $this->post('getChat', $params);

        return new Chat($response->getDecodedBody());
    }

    /**
     * Get a list of administrators in a chat.
     *
     * <code>
     * $params = [
     *   'chat_id'  => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#getchatadministrators
     *
     * @param array    $params
     *
     * @var string|int $params ['chat_id'] Unique identifier for the target chat or username of the target supergroup or channel (in the format @channelusername);
     *
     * @throws TelegramSDKException
     *
     * @return ChatMember[]
     */
    public function getChatAdministrators(array $params)
    {
        $response = $this->post('getChatAdministrators', $params);

        return collect($response->getResult())
            ->map(
                function ($admin) {
                    return new ChatMember($admin);
                }
            )
            ->all();
    }

    /**
     * Get the number of members in a chat.
     *
     * <code>
     * $params = [
     *   'chat_id'  => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#getchatmemberscount
     *
     * @param array    $params
     *
     * @var string|int $params ['chat_id'] Unique identifier for the target chat or username of the target supergroup or channel (in the format @channelusername)
     *
     * @throws TelegramSDKException
     *
     * @return int
     */
    public function getChatMembersCount(array $params)
    {
        $response = $this->post('getChatMembersCount', $params);

        return $response->getResult();
    }

    /**
     * Get information about a member of a chat.
     *
     * <code>
     * $params = [
     *   'chat_id'  => '',
     *   'user_id'  => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#getchatmember
     *
     * @param array    $params
     *
     * @var string|int $params ['chat_id'] Unique identifier for the target chat or username of the target supergroup or channel (in the format @channelusername)
     * @var int        $params ['user_id'] Unique identifier of the target user.
     *
     * @throws TelegramSDKException
     *
     * @return ChatMember
     */
    public function getChatMember(array $params)
    {
        $response = $this->post('getChatMember', $params);

        return new ChatMember($response->getDecodedBody());
    }

    /**
     * Send answers to callback queries sent from inline keyboards.
     *
     * he answer will be displayed to the user as a notification at the top of the chat screen or as an alert.
     *
     * <code>
     * $params = [
     *   'callback_query_id'  => '',
     *   'text'               => '',
     *   'show_alert'         => '',
     *   'cache_time'         => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#answerCallbackQuery
     *
     * @param array $params
     *
     * @var string  $params ['callback_query_id'] Unique identifier for the query to be answered
     * @var string  $params ['text'] Text of the notification.
     * @var bool    $params ['show_alert']
     * @var int     $params ['cache_time']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function answerCallbackQuery(array $params)
    {
        $this->post('answerCallbackQuery', $params);

        return true;
    }

    /**
     * Use this method to change the list of the bot's commands.
     *
     * <code>
     * $params = [
     *   'commands'  => [],
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#setmycommands
     *
     * @param array $params
     *
     * @var BotCommand[] $params['commands']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function setMyCommands(array $params): bool
    {
        $this->post('setMyCommands', $params);

        return true;
    }

    /**
     * Use this method to change the list of the bot's commands.
     *
     * @link https://core.telegram.org/bots/api#getmycommands
     *
     * @throws TelegramSDKException
     *
     * @return BotCommand[]
     */
    public function getMyCommands(): array
    {
        $response = $this->post('getMyCommands');

        $commands = [];

        foreach ($response->getDecodedBody() as $command){
            $commands[] = new BotCommand($command);
        }

        return $commands;
    }

    /**
     * Edit text messages sent by the bot or via the bot (for inline bots).
     *
     * <code>
     * $params = [
     *   'chat_id'                  => '',
     *   'message_id'               => '',
     *   'inline_message_id'        => '',
     *   'text'                     => '',
     *   'parse_mode'               => '',
     *   'disable_web_page_preview' => '',
     *   'reply_markup'             => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#editMessageText
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var int        $params ['message_id']
     * @var string     $params ['inline_message_id']
     * @var string     $params ['text']
     * @var string     $params ['parse_mode']
     * @var bool       $params ['disable_web_page_preview']
     * @var string     $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return Message|bool
     */
    public function editMessageText(array $params)
    {
        $response = $this->post('editMessageText', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Edit captions of messages sent by the bot or via the bot (for inline bots).
     *
     * <code>
     * $params = [
     *   'chat_id'                  => '',
     *   'message_id'               => '',
     *   'inline_message_id'        => '',
     *   'caption'                  => '',
     *   'reply_markup'             => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#editMessageCaption
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var int        $params ['message_id']
     * @var string     $params ['inline_message_id']
     * @var string     $params ['caption']
     * @var string     $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return Message|bool
     */
    public function editMessageCaption(array $params)
    {
        $response = $this->post('editMessageCaption', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Edit only the reply markup of messages sent by the bot or via the bot (for inline bots).
     *
     * <code>
     * $params = [
     *   'chat_id'                  => '',
     *   'message_id'               => '',
     *   'inline_message_id'        => '',
     *   'reply_markup'             => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#editMessageReplyMarkup
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var int        $params ['message_id']
     * @var string     $params ['inline_message_id']
     * @var string     $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return Message|bool
     */
    public function editMessageReplyMarkup(array $params)
    {
        $response = $this->post('editMessageReplyMarkup', $params);

        return new Message($response->getDecodedBody());
    }


    /**
     * Stop a poll which was sent by the bot.
     *
     * <code>
     * $params = [
     *   'chat_id'                  => '',
     *   'message_id'               => '',
     *   'reply_markup'             => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#stoppoll
     *
     * @param array $params
     *
     * @var int|string $params ['chat_id']
     * @var int        $params ['message_id']
     * @var string     $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return Poll
     */
    public function stopPoll(array $params)
    {
        $response = $this->post('stopPoll', $params);

        return new Poll($response->getDecodedBody());
    }

    /**
     * Use this method to send answers to an inline query.
     *
     * <code>
     * $params = [
     *   'inline_query_id'      => '',
     *   'results'              => [],
     *   'cache_time'           => 0,
     *   'is_personal'          => false,
     *   'next_offset'          => '',
     *   'switch_pm_text'       => '',
     *   'switch_pm_parameter'  => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#answerinlinequery
     *
     * @param array     $params
     *
     * @var string      $params ['inline_query_id']
     * @var array       $params ['results']
     * @var int|null    $params ['cache_time']
     * @var bool|null   $params ['is_personal']
     * @var string|null $params ['next_offset']
     * @var string|null $params ['switch_pm_text']
     * @var string|null $params ['switch_pm_parameter']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function answerInlineQuery(array $params = [])
    {
        if (is_array($params[ 'results' ])) {
            $params[ 'results' ] = json_encode($params[ 'results' ]);
        }

        $this->post('answerInlineQuery', $params);

        return true;
    }

    /**
     * Use this method to send invoices.
     *
     * <code>
     * $params = [
     *   'chat_id'                => '',
     *   'title'                  => '',
     *   'description'            => '',
     *   'payload'                => '',
     *   'provider_token'         => '',
     *   'start_parameter'        => '',
     *   'currency'               => '',
     *   'prices'                 => '',
     *   'photo_url'              => '',
     *   'photo_size'             => '',
     *   'photo_width'            => '',
     *   'photo_height'           => '',
     *   'need_name'              => true,
     *   'need_phone_number'      => true,
     *   'need_email'             => true,
     *   'need_shipping_address'  => true,
     *   'is_flexible'            => true,
     *   'disable_notification'   => '',
     *   'reply_to_message_id'    => '',
     *   'reply_markup'           => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#sendinvoice
     *
     * @param array  $params
     *
     * @var int      $params ['chat_id']
     * @var string   $params ['title']
     * @var string   $params ['description']
     * @var string   $params ['payload']
     * @var string   $params ['provider_token']
     * @var string   $params ['start_parameter']
     * @var string   $params ['currency']
     * @var array    $params ['prices']
     * @var string   $params ['photo_url']
     * @var int|null $params ['photo_size']
     * @var int|null $params ['photo_width']
     * @var int|null $params ['photo_height']
     * @var bool     $params ['need_name']
     * @var bool     $params ['need_phone_number']
     * @var bool     $params ['need_email']
     * @var bool     $params ['need_shipping_address']
     * @var bool     $params ['is_flexible']
     * @var bool     $params ['disable_notification']
     * @var int|null $params ['reply_to_message_id']
     * @var string   $params ['reply_markup']
     *
     * @throws TelegramSDKException
     *
     * @return Message
     */
    public function sendInvoice(array $params = [])
    {
        $response = $this->post('sendInvoice', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Use this method to get a sticker set.
     *
     * <code>
     * $params = [
     *   'name'                => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#getstickerset
     *
     * @param array $params
     *
     * @var string  $params ['name']
     *
     * @throws TelegramSDKException
     *
     * @return StickerSet
     */
    public function getStickerSet(array $params = [])
    {
        $response = $this->post('getStickerSet', $params);

        return new StickerSet($response->getDecodedBody());
    }

    /**
     * Use this method to upload a .png file with a sticker.
     *
     * <code>
     * $params = [
     *   'user_id'              => '',
     *   'png_sticker'          => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#uploadstickerfile
     *
     * @param array    $params
     *
     * @var int|string $params ['user_id']
     * @var InputFile  $params ['png_sticker']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function uploadStickerFile(array $params)
    {
        $this->post('uploadStickerFile', $params);

        return true;
    }

    /**
     * Use this method to upload a .png file with a sticker.
     *
     * <code>
     * $params = [
     *   'user_id' => '',
     *   'name' => '',
     *   'title' => '',
     *   'png_sticker' => '',
     *   'tgs_sticker' => '',
     *   'emojis' => '',
     *   'contains_masks' => '',
     *   'mask_position' => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#uploadstickerfile
     *
     * @param array    $params
     *
     * @var int|string $params ['user_id']
     * @var string $params ['name']
     * @var string $params ['title']
     * @var InputFile $params ['png_sticker']
     * @var InputFile $params ['tgs_sticker']
     * @var string $params ['emojis']
     * @var bool $params ['contains_masks']
     * @var MaskPosition $params ['mask_position']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function createNewStickerSet(array $params)
    {
        $this->post('createNewStickerSet', $params);

        return true;
    }

    /**
     * Use this method to add a new sticker to a set created by the bot.
     *
     * <code>
     * $params = [
     *   'user_id' => '',
     *   'name' => '',
     *   'png_sticker' => '',
     *   'tgs_sticker' => '',
     *   'emojis' => '',
     *   'mask_position' => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#addstickertoset
     *
     * @param array $params
     *
     * @var int|string $params ['user_id']
     * @var string $params ['name']
     * @var InputFile $params ['png_sticker']
     * @var InputFile $params ['tgs_sticker']
     * @var string $params ['emojis']
     * @var bool $params ['contains_masks']
     * @var MaskPosition $params ['mask_position']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function addStickerToSet(array $params): bool
    {
        $this->post('addStickerToSet', $params);

        return true;
    }


    /**
     * Use this method to restrict a user in a supergroup.
     *
     * <code>
     * $params = [
     *   'chat_id'                   => '',
     *   'user_id'                   => '',
     *   'until_date'                => '',
     *   'permissions'               => [],
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#restrictchatmember
     *
     * @param array    $params
     *
     * @var int|string            $params ['chat_id']
     * @var int|string            $params ['user_id']
     * @var int                   $params ['until_date']
     * @var Chat\ChatPermissions  $params ['permissions']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function restrictChatMember(array $params)
    {
        $this->post('restrictChatMember', $params);

        return true;
    }

    /**
     * Use this method to promote or demote a user in a supergroup or a channel.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'user_id'              => '',
     *   'can_change_info'      => '',
     *   'can_post_messages'    => '',
     *   'can_edit_messages'    => '',
     *   'can_delete_messages'  => '',
     *   'can_invite_users'     => '',
     *   'can_restrict_members' => '',
     *   'can_pin_messages'     => '',
     *   'can_promote_members'  => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#promotechatmember
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var int|string $params ['user_id']
     * @var bool       $params ['can_change_info']
     * @var bool       $params ['can_post_messages']
     * @var bool       $params ['can_edit_messages']
     * @var bool       $params ['can_delete_messages']
     * @var bool       $params ['can_invite_users']
     * @var bool       $params ['can_restrict_members']
     * @var bool       $params ['can_pin_messages']
     * @var bool       $params ['can_promote_members']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function promoteChatMember(array $params)
    {
        $this->post('promoteChatMember', $params);

        return true;
    }

    /**
     * Use this method to set a custom title for an administrator in a supergroup promoted by the bot.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'user_id'              => '',
     *   'custom_title'         => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#setChatAdministratorCustomTitle
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var int|string $params ['user_id']
     * @var string     $params ['custom_title']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function setChatAdministratorCustomTitle(array $params)
    {
        $this->post('setChatAdministratorCustomTitle', $params);

        return true;
    }


    /**
     * Use this method to set default chat permissions for all members.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'permissions'          => [],
     * </code>
     *
     * @link https://core.telegram.org/bots/api#setchatpermissions
     *
     * @param array    $params
     *
     * @var int|string           $params ['chat_id']
     * @var Chat\ChatPermissions $params ['permissions']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function setChatPermissions(array $params)
    {
        $this->post('setChatPermissions', $params);

        return true;
    }

    /**
     * Use this method to export an invite link to a supergroup or a channel.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#exportchatinvitelink
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     *
     * @throws TelegramSDKException
     *
     * @return string
     */
    public function exportChatInviteLink(array $params)
    {
        $result = $this->post('exportChatInviteLink', $params);

        return $result->getBody();
    }

    /**
     * Use this method to set a new profile photo for the chat.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'photo'                => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#setchatphoto
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var InputFile  $params ['photo']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function setChatPhoto(array $params)
    {
        $this->uploadFile('setChatPhoto', $params);

        return true;
    }

    /**
     * Use this method to delete a chat photo.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#deletechatphoto
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function deleteChatPhoto(array $params)
    {
        $this->post('deleteChatPhoto', $params);

        return true;
    }

    /**
     * Use this method to change the title of a chat.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'title'                => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#setchattitle
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var string     $params ['title']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function setChatTitle(array $params)
    {
        $this->post('setChatTitle', $params);

        return true;
    }

    /**
     * Use this method to change the description of a supergroup or a channel..
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'description'          => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#setchatdescription
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var string     $params ['description']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function setChatDescription(array $params)
    {
        $this->post('setChatDescription', $params);

        return true;
    }

    /**
     * Set a new group sticker set for a supergroup.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     *   'sticker_set_name'     => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#setChatStickerSet
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var string     $params ['sticker_set_name']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function setChatStickerSet(array $params)
    {
        $this->post('setChatStickerSet', $params);

        return true;
    }

    /**
     * Delete a group sticker set from a supergroup.
     *
     * <code>
     * $params = [
     *   'chat_id'              => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#deleteChatStickerSet
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function deleteChatStickerSet(array $params)
    {
        $this->post('deleteChatStickerSet', $params);

        return true;
    }

    /**
     * Use this method to set the thumbnail of a sticker set. Animated thumbnails can be set for animated sticker sets only.
     *
     * <code>
     * $params = [
     *   'name' => '',
     *   'user_id' => '',
     *   'thumb' => '',
     * ];
     * </code>
     * @link https://core.telegram.org/bots/api#setstickersetthumb
     *
     * @param array $params
     *
     * @var string $params['name']
     * @var int|string $params['user_id']
     * @var InputFile|string $params['thumb']
     *
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function setStickerSetThumb(array $params): bool
    {
        $this->post('setStickerSetThumb', $params);

        return true;
    }

    /**
     *  Use this method to send an animated emoji that will display a random value.
     *
     * <code>
     * $params = [
     *   'chat_id' => '',
     *   'emoji' => '',
     *   'disable_notification' => true,
     *   'reply_to_message_id' => '',
     *   'reply_markup' => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#senddice
     *
     * @param array $params
     *
     * @var int|string $params['chat_id']
     * @var string $params['emoji']
     * @var bool $params['disable_notification']
     * @var int $params['reply_to_message_id']
     *
     * @throws TelegramSDKException
     *
     * @return Message
     */
    public function sendDice(array $params): Message
    {
        $response = $this->post('sendDice', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Use this method to pin a message in a supergroup.
     *
     * <code>
     * $params = [
     *   'chat_id'               => '',
     *   'message_id'            => '',
     *   'disable_notification'  => true,
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#pinchatmessage
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     * @var int        $params ['message_id']
     * @var bool       $params ['disable_notification']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function pinChatMessage(array $params)
    {
        $this->post('pinChatMessage', $params);

        return true;
    }

    /**
     * Use this method to unpin a message in a supergroup chat.
     *
     * <code>
     * $params = [
     *   'chat_id'               => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#unpinchatmessage
     *
     * @param array    $params
     *
     * @var int|string $params ['chat_id']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function unpinChatMessage(array $params)
    {
        $this->post('unpinChatMessage', $params);

        return true;
    }

    /**
     * Use this method to send answers to an inline query.
     *
     * <code>
     * $params = [
     *   'shipping_query_id' => '',
     *   'ok'                => true,
     *   'shipping_options'  => [],
     *   'error_message'     => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#answershippingquery
     *
     * @param array $params
     *
     * @var string  $params ['shipping_query_id']
     * @var bool    $params ['ok']
     * @var array   $params ['shipping_options']
     * @var string  $params ['error_message']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function answerShippingQuery(array $params = [])
    {
        $this->post('answerShippingQuery', $params);

        return true;
    }

    /**
     * Use this method to send pre-checkout queries to an inline query.
     *
     * <code>
     * $params = [
     *   'pre_checkout_query_id' => '',
     *   'ok'                    => true,
     *   'error_message'         => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#answerprecheckoutquery
     *
     * @param array $params
     *
     * @var string  $params ['pre_checkout_query_id']
     * @var bool    $params ['ok']
     * @var string  $params ['error_message']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function answerPreCheckoutQuery(array $params = [])
    {
        $this->post('answerPreCheckoutQuery', $params);

        return true;
    }

    /**
     * Use this method to send answers to an inline query.
     *
     * <code>
     * $params = [
     *   'user_id'              => '',
     *   'score'                => '',
     *   'force'                => true,
     *   'disable_edit_message' => true,
     *   'chat_id'              => '',
     *   'message_id'           => '',
     *   'inline_message_id'    => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#setgamescore
     *
     * @param array   $params
     *
     * @var int       $params ['user_id']
     * @var int       $params ['score']
     * @var bool|null $params ['force']
     * @var bool|null $params ['disable_edit_message']
     * @var int|null  $params ['chat_id']
     * @var int|null  $params ['message_id']
     * @var int|null  $params ['inline_message_id']
     *
     * @throws TelegramSDKException
     *
     * @return Message
     */
    public function setGameScore(array $params = [])
    {
        $response = $this->post('setGameScore', $params);

        return new Message($response->getDecodedBody());
    }

    /**
     * Set a Webhook to receive incoming updates via an outgoing webhook.
     *
     * <code>
     * $params = [
     *   'url'             => '',
     *   'certificate'     => '',
     *   'max_connections' => 40,
     *   'allowed_updates' => [],
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#setwebhook
     *
     * @param array      $params
     *
     * @var string       $params ['url']         HTTPS url to send updates to.
     * @var string       $params ['certificate'] Upload your public key certificate so that the root certificate in
     *                   use can be checked.
     * @var int          $params ['max_connections'] Maximum allowed number of simultaneous HTTPS connections to the webhook for update delivery, 1-100.
     * @var string|array $params ['allowed_updates']        List the types of updates you want your bot to receive.
     *
     * @throws TelegramSDKException
     *
     * @return TelegramResponse
     */
    public function setWebhook(array $params)
    {
        if (filter_var($params[ 'url' ], FILTER_VALIDATE_URL) === false) {
            throw new TelegramSDKException('Invalid URL Provided');
        }

        if (parse_url($params[ 'url' ], PHP_URL_SCHEME) !== 'https') {
            throw new TelegramSDKException('Invalid URL, should be a HTTPS url.');
        }

        return $this->uploadFile('setWebhook', $params);
    }

    /**
     * Use this method to get current webhook status. Requires no parameters.
     * On success, returns a WebhookInfo object. If the bot is using getUpdates,
     * will return an object with the url field empty.
     *
     *
     * @link https://core.telegram.org/bots/api#setwebhook
     *
     * @throws TelegramSDKException
     *
     * @return WebhookInfo
     */
    public function getWebhookInfo()
    {
        $response = $this->post('getWebhookInfo');

        return new WebhookInfo($response->getDecodedBody());
    }

    /**
     *  Remove webhook integration if you decide to switch back to getUpdates.
     *
     *
     * @link https://core.telegram.org/bots/api#deletewebhook
     *
     * @throws TelegramSDKException
     *
     * @return TelegramResponse
     */
    public function deleteWebhook()
    {
        return $this->uploadFile('deleteWebhook');
    }

    /**
     * Removes the outgoing webhook (if any).
     *
     * @throws TelegramSDKException
     *
     * @return TelegramResponse
     */
    public function removeWebhook()
    {
        $url = '';

        return $this->post('setWebhook', compact('url'));
    }

    /**
     * Processes Inbound Commands.
     *
     * @param bool $webhook
     *
     * @return Update|Update[]
     */
    public function commandsHandler($webhook = false)
    {
        if ($webhook) {
            $update = $this->getWebhookUpdate();
            $this->processCommand($update);
            $this->processCallback($update);

            return $update;
        }

        $updates   = $this->getUpdates();
        $highestId = -1;

        foreach ($updates as $update) {
            $highestId = $update->getUpdateId();
            $this->processCommand($update);
            $this->processCallback($update);
        }

        //An update is considered confirmed as soon as getUpdates is called with an offset higher than its update_id.
        if ($highestId != -1) {
            $params             = [];
            $params[ 'offset' ] = $highestId + 1;
            $params[ 'limit' ]  = 1;
            $this->markUpdateAsRead($params);
        }

        return $updates;
    }

    /**
     * Returns a webhook update sent by Telegram.
     * Works only if you set a webhook.
     *
     * @see setWebhook
     *
     * @return Update
     */
    public function getWebhookUpdate()
    {
        $body = json_decode(file_get_contents('php://input'), true);

        return new Update($body);

    }

    /**
     * Check update object for a command and process.
     *
     * @param Update $update
     */
    public function processCommand(Update $update)
    {
        $message = $update->getMessage();

        if ($message !== null && $message->has('text')) {
            $this->getCommandBus()->handler($message->getText(), $update);
        }
    }

    /**
     * Returns SDK's Command Bus.
     *
     * @return CommandBus
     */
    public function getCommandBus()
    {
        return $this->commandBus;
    }

    /**
     * Check update object for a command and process.
     *
     * @param Update $update
     */
    public function processCallback(Update $update)
    {
        $message = $update->getCallbackQuery();

        if ($message !== null) {
            $this->getCallbackBus()->handler($message->getData(), $update);
        }
    }

    /**
     * Returns SDK's Callback Command Bus.
     *
     * @return CallbackCommandBus
     */
    public function getCallbackBus()
    {
        return $this->callbackBus;
    }

    /**
     * Use this method to receive incoming updates using long polling.
     *
     * <code>
     * $params = [
     *   'offset'  => '',
     *   'limit'   => '',
     *   'timeout' => '',
     *   'allowed_updates'=> ''
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#getupdates
     *
     * @param array    $params
     *
     * @var int|null   $params ['offset']
     * @var int|null   $params ['limit']
     * @var int|null   $params ['timeout']
     * @var array|null $params ['allowed_updates']
     *
     * @throws TelegramSDKException
     *
     * @return Update[]
     */
    public function getUpdates(array $params = [])
    {
        $response = $this->post('getUpdates', $params);

        return collect($response->getResult())
            ->map(
                function ($data) {
                    return new Update($data);
                }
            )
            ->all();
    }


    /**
     * Informs a user that some of the Telegram Passport elements they provided contains errors.
     *
     * <code>
     * $params = [
     *   'user_id'  => '',
     *   'errors'   => '',
     * ];
     * </code>
     *
     * @link https://core.telegram.org/bots/api#setpassportdataerrors
     *
     * @param array    $params
     *
     * @var int    $params ['user_id']
     * @var array  $params ['errors']
     *
     * @throws TelegramSDKException
     *
     * @return bool
     */
    public function setPassportDataErrors(array $params)
    {

        $this->post('setPassportDataErrors', $params);

        return true;
    }

    /**
     * An alias for getUpdates that helps readability.
     *
     * @param $params
     *
     * @return Objects\Update[]
     */
    protected function markUpdateAsRead($params)
    {
        return $this->getUpdates($params, false);
    }

    /**
     * Helper to Trigger Commands.
     *
     * @param string $name   Command Name
     * @param Update $update Update Object
     *
     * @return mixed
     */
    public function triggerCommand($name, Update $update)
    {
        return $this->getCommandBus()->execute($name, $update->getMessage()->getText(), $update);
    }

    /**
     * Magic method to process any "get" requests.
     *
     * @param $method
     * @param $arguments
     *
     * @throws TelegramSDKException
     *
     * @return bool|TelegramResponse|UnknownObject
     */
    public function __call($method, $arguments)
    {
        if (preg_match('/^\w+Commands?/', $method, $matches)) {
            return call_user_func_array([$this->getCommandBus(), $matches[ 0 ]], $arguments);
        }

        $action = substr($method, 0, 3);
        if ($action === 'get') {
            /* @noinspection PhpUndefinedFunctionInspection */
            $class_name = studly_case(substr($method, 3));
            $class      = 'Telegram\Bot\Objects\\' . $class_name;
            $response   = $this->post($method, $arguments[ 0 ] ?: []);

            if (class_exists($class)) {
                return new $class($response->getDecodedBody());
            }

            return $response;
        }
        $response = $this->post($method, $arguments[ 0 ]);

        return new UnknownObject($response->getDecodedBody());
    }

    /**
     * Get the IoC Container.
     *
     * @return Container
     */
    public function getContainer()
    {
        return self::$container;
    }

    /**
     * Check if IoC Container has been set.
     *
     * @return bool
     */
    public function hasContainer()
    {
        return self::$container !== null;
    }

    /**
     * Sends a GET request to Telegram Bot API and returns the result.
     *
     * @param string $endpoint
     * @param array  $params
     *
     * @throws TelegramSDKException
     *
     * @return TelegramResponse
     */
    protected function get($endpoint, $params = [])
    {
        if (array_key_exists('reply_markup', $params)) {
            $params[ 'reply_markup' ] = (string)$params[ 'reply_markup' ];
        }

        return $this->sendRequest(
            'GET',
            $endpoint,
            $params
        );
    }
}
