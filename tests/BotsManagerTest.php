<?php

namespace Telegram\Bot\Tests;


use Illuminate\Contracts\Container\Container;
use Telegram\Bot\Api;
use Telegram\Bot\BotsManager;

class BotsManagerTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var BotsManager
     */
    private $manager;

    public function setUp()
    {
        $config = require __DIR__ . '/../src/Laravel/config/telegram.php';

        $this->manager = new BotsManager($config);
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function it_throws_exception_if_bot_manager_not_configured()
    {

        $manager = new BotsManager([]);
        $manager->getBotConfig('default');
    }


    /** @test */
    public function it_checks_container_return_illumitate_container()
    {
        $container = $this->manager->getContainer();

        $this->assertInstanceOf(Container::class, $container);
    }

    /** @test */
    public function it_checks_bot_return_api()
    {
        $api = $this->manager->bot('common');

        $this->assertInstanceOf(Api::class, $api);
    }

}