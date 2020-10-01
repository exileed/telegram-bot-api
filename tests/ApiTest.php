<?php

namespace Telegram\Bot\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Telegram\Bot\Api;
use Telegram\Bot\Commands\CommandBus;
use Telegram\Bot\Exceptions\TelegramResponseException;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\HttpClients\GuzzleHttpClient;
use Telegram\Bot\Objects\File;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Objects\User;
use Telegram\Bot\TelegramClient;
use Telegram\Bot\TelegramResponse;
use Telegram\Bot\Tests\Mocks\Mocker;
use Illuminate\Contracts\Container\Container;

class ApiTest extends TestCase
{
    /**
     * @var Api
     */
    protected $api;

    public function setUp(): void
    {
        $this->api = new Api('token');
    }

    /**
     * @test
     * @dataProvider badTypes
     *
     * @link         https://phpunit.de/manual/3.7/en/appendixes.annotations.html#appendixes.annotations.dataProvider
     *
     * @param mixed $type The item under test
     */
    public function it_only_allows_a_string_as_the_api_token($type)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->api->setAccessToken($type);
    }

    /** @test */
    public function it_checks_the_passed_api_token_is_returned()
    {
        self::assertEquals('token', $this->api->getAccessToken());
        $this->api->setAccessToken('another');
        self::assertEquals('another', $this->api->getAccessToken());
    }

    /** @test */
    public function it_checks_the_default_http_client_is_guzzle_if_not_specified()
    {
        $client = $this->api->getClient()->getHttpClientHandler();

        self::assertInstanceOf(GuzzleHttpClient::class, $client);
    }

    /** @test */
    public function it_checks_the_Client_object_is_returned()
    {
        self::assertInstanceOf(TelegramClient::class, $this->api->getClient());
    }

    /** @test */
    public function it_checks_the_commandBus_is_returned()
    {
        $this->assertInstanceOf(CommandBus::class, $this->api->getCommandBus());
    }

    /** @test */
    public function it_checks_an_ioc_container_can_be_set()
    {
        $this->api::setContainer(Mocker::createContainer()->reveal());

        self::assertInstanceOf(Container::class, $this->api->getContainer());
    }

    /** @test */
    public function it_checks_an_update_object_is_returned_when_a_command_is_handled()
    {
        $this->api = Mocker::createMessageResponse('/start');
        $updates = $this->api->commandsHandler();
        self::assertInstanceOf(Update::class, $updates[0]);
    }

    /** @test */
    public function it_checks_the_correct_command_is_handled()
    {
        $this->api = Mocker::createMessageResponse('/mycommand');
        $command = Mocker::createMockCommand('mycommand');
        $command2 = Mocker::createMockCommand('mycommand2');

        $this->api->addCommands([$command->reveal(), $command2->reveal()]);

        $this->api->commandsHandler();

        $command->make(Argument::any(), Argument::any(), Argument::any())->shouldHaveBeenCalled();
        $command2->make(Argument::any(), Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
    }

    /** @test */
    public function it_checks_the_lastResponse_property_gets_populated_after_a_request()
    {
        $this->assertEmpty($this->api->getLastResponse());

        $this->api = Mocker::createMessageResponse('/start');
        $this->api->commandsHandler();

        $lastResponse = $this->api->getLastResponse();
        $this->assertNotEmpty($lastResponse);
        $this->assertInstanceOf(TelegramResponse::class, $lastResponse);
    }

    /** @test */
    public function it_checks_the_async_property_can_be_set()
    {
        self::assertEmpty($this->api->isAsyncRequest());

        $this->api->setAsyncRequest(true);

        $isAsync = $this->api->isAsyncRequest();

        self::assertTrue($isAsync);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_the_api_response_is_not_ok()
    {
        $this->expectException(TelegramResponseException::class);

        $this->api = Mocker::createApiResponse([], false);

        $this->api->getMe();
    }

    /** @test */
    public function it_checks_a_user_object_is_returned_when_getMe_is_requested()
    {
        $this->api = Mocker::createApiResponse(
            [
                'id'         => 123456789,
                'first_name' => 'Test',
                'username'   => 'TestUsername',
            ]
        );

        /** @var User $response */
        $response = $this->api->getMe();

        self::assertInstanceOf(User::class, $response);
        self::assertEquals(123456789, $response->getId());
        self::assertEquals('Test', $response->getFirstName());
        self::assertEquals('TestUsername', $response->getUsername());
    }

    /** @test */
    public function it_checks_a_message_object_is_returned_when_sendMessage_is_sent()
    {
        $chatId = 987654321;
        $text = 'Test message';
        $this->api = Mocker::createApiResponse(
            [
                'chat' => [
                    'id' => $chatId,
                ],
                'text' => $text,
            ]
        );

        /** @var Message $response */
        $response = $this->api->sendMessage(['chat_id' => $chatId, 'text' => $text]);

        self::assertInstanceOf(Message::class, $response);
        self::assertEquals($chatId, $response->getChat()->getId());
        self::assertEquals($text, $response->getText());
    }

    /** @test */
    public function it_checks_ability_to_set_timeouts()
    {
        self::markTestSkipped('todo');
        $chatId = 987654321;
        $text = 'Test message';
        $this->api = Mocker::createApiResponse(
            [
                'chat' => [
                    'id' => $chatId,
                ],
                'text' => $text,
            ]
        );

        $this->api->setTimeOut(1);
        $this->api->setConnectTimeOut(1);

        /** @var GuzzleHttpClient $clientHandler */
        $clientHandler = $this->api->getClient()->getHttpClientHandler();
        self::assertEquals(1, $clientHandler->getTimeOut());
        self::assertEquals(1, $clientHandler->getConnectTimeOut());
    }

    /** @test */
    public function it_checks_a_message_object_is_returned_when_forwardMessage_is_sent()
    {
        $chatId = 987654321;
        $fromId = 888888888;
        $forwardFromId = 77777777;
        $messageId = 123;
        $this->api = Mocker::createApiResponse(
            [
                'message_id'   => $messageId,
                'from'         => [
                    'id' => $fromId,
                ],
                'forward_from' => [
                    'id' => $forwardFromId,
                ],
                'chat'         => [
                    'id' => $chatId,
                ],
            ]
        );

        /** @var Message $response */
        $response = $this->api->forwardMessage([
            'chat_id'      => $chatId,
            'from_chat_id' => $fromId,
            'message_id'   => $messageId,
        ]);

        self::assertInstanceOf(Message::class, $response);
        self::assertEquals($chatId, $response->getChat()->getId());
        self::assertEquals($fromId, $response->getFrom()->getId());
        self::assertEquals($messageId, $response->getMessageId());
        self::assertEquals($forwardFromId, $response->getForwardFrom()->getId());
    }

    /** @test */
    public function it_checks_a_message_object_is_returned_with_photo_information_when_sendPhoto_is_sent()
    {
        $chatId = 987654321;
        $photo = md5('test'); //A file_id from a previous sent image.
        $this->api = Mocker::createApiResponse(
            [
                'chat'  => [
                    'id' => $chatId,
                ],
                'photo' => [
                    [
                        'file_id' => $photo,
                    ],
                    [
                        'file_id' => md5('file_id2'),
                    ],
                    [
                        'file_id' => md5('file_id3'),
                    ],
                ],
            ]
        );

        /** @var Message $response */
        $response = $this->api->sendPhoto(['chat_id' => $chatId, 'photo' => $photo]);

        self::assertInstanceOf(Message::class, $response);
        self::assertTrue($response->has('photo'));
        self::assertTrue($response->getPhoto()->contains('file_id', $photo));
        self::assertGreaterThan(1, count($response->getPhoto()));
    }

    /**
     * @test
     * @dataProvider fileTypes
     *
     * @param $fileType
     */
    public function it_checks_a_message_object_is_returned_with_correct_fields_when_all_fileTypes_are_attached_to_a_message(
        $fileType
    ) {
        $chatId = 987654321;
        $fileId = md5($fileType);

        //When sending all types of multimedia/documents these fields are always required:
        $requiredFields = [
            'chat'    => [
                'id' => $chatId,
            ],
            $fileType => [
                [
                    'file_id' => $fileId,
                ],
            ],
        ];

        //Photo message is slightly different as it returns multiple file_ids in an array.
        if ($fileType === 'photo') {
            $extraFileIds = [
                [
                    'file_id' => md5('file_id2'),
                ],
                [
                    'file_id' => md5('file_id3'),
                ],
            ];
            $requiredFields[$fileType] = array_merge($requiredFields[$fileType], $extraFileIds);
        }

        $this->api = Mocker::createApiResponse($requiredFields);

        /** @var Message $response */
        $method = 'send'.ucfirst($fileType);
        $response = $this->api->$method(['chat_id' => $chatId, $fileType => $fileId]);

        self::assertInstanceOf(Message::class, $response);
        self::assertTrue($response->has($fileType));
        self::assertTrue($response->get($fileType)->contains('file_id', $fileId));

        if ($fileType === 'photo') {
            self::assertGreaterThan(1, count($response->getPhoto()));
        }
    }

    /** @test */
    public function it_checks_a_message_object_is_returned_with_correct_fields_when_sendLocation_is_sent()
    {
        $chatId = 987654321;

        $requiredFields = [
            'chat'     => [
                'id' => $chatId,
            ],
            'location' => [
                'longitude' => 10.9,
                'latitude'  => 99.9,
            ],
        ];

        $this->api = Mocker::createApiResponse($requiredFields);

        /** @var Message $response */
        $response = $this->api->sendLocation(['chat_id' => $chatId, 'longitude' => 10.9, 'latitude' => 99.9]);

        self::assertInstanceOf(Message::class, $response);
        self::assertTrue($response->has('location'));
        self::assertTrue($response->get('location')->has('longitude'));
        self::assertTrue($response->get('location')->has('latitude'));
    }

    /**
     * @test
     */
    public function it_throws_exception_if_invalid_chatAction_is_sent()
    {
        $this->expectException(TelegramSDKException::class);
        $this->api->sendChatAction(['action' => 'zzz']);
    }

    /** @test */
    public function it_returns_a_successful_response_if_a_valid_chatAction_is_sent()
    {
        $this->api = Mocker::createApiResponse([true]);

        $response = $this->api->sendChatAction(['chat_id' => 123456789, 'action' => 'typing']);

        self::assertTrue($response);
    }

    /** @test */
    public function it_returns_a_file_object_if_getFile_is_sent()
    {
        $fileId = md5('file_id');
        $this->api = Mocker::createApiResponse(
            [
                'file_id'   => $fileId,
                'file_size' => '',
                'file_path' => '',
            ]
        );

        $response = $this->api->getFile(['file_id' => $fileId]);

        self::assertInstanceOf(File::class, $response);
        self::assertEquals($fileId, $response->getFileId());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_setWebhook_url_is_not_a_url()
    {
        $this->expectException(TelegramSDKException::class);

        $this->api->setWebhook(['url' => 'string']);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_webhook_url_is_not_a_https_url()
    {
        $this->expectException(TelegramSDKException::class);

        $this->api->setWebhook(['url' => 'http://example.com']);
    }

    /** @test */
    public function it_returns_a_successful_response_object_if_correct_webhook_is_sent()
    {
        $this->api = Mocker::createApiResponse([true]);

        $response = $this->api->setWebhook(['url' => 'https://example.com']);

        self::assertInstanceOf(TelegramResponse::class, $response);
        self::assertTrue($response->getResult()[0]);
    }

    /** @test */
    public function it_returns_a_successful_response_object_when_webhook_removed()
    {
        $this->api = Mocker::createApiResponse([true]);

        $response = $this->api->removeWebhook();

        self::assertInstanceOf(TelegramResponse::class, $response);
        self::assertTrue($response->getDecodedBody()['result'][0]);
        self::assertEquals(200, $response->getHttpStatusCode());
    }

    /**
     * A list of files/attachments types that should be tested.
     *
     * @return array
     */
    public function fileTypes()
    {
        return [
            [
                'photo',
            ],
            [
                'audio',
            ],
            [
                'video',
            ],
            [
                'voice',
            ],
            [
                'sticker',
            ],
            [
                'document',
            ],
        ];
    }

    /**
     * Gets an array of arrays.
     *
     * These are types of data that should not be allowed to be used
     * as an API token.
     *
     * @return array
     */
    public function badTypes()
    {
        return [
            [
                new \stdClass(),
            ],
            [
                ['token'],
            ],
            [
                12345,
            ],
        ];
    }
}
