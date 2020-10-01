<?php

namespace Telegram\Bot\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Commands\CommandBus;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\Objects\Update;
use Telegram\Bot\Tests\Mocks\MockCommand;
use Telegram\Bot\Tests\Mocks\MockCommandTwo;
use Telegram\Bot\Tests\Mocks\Mocker;

class CommandBusTest extends TestCase
{
    /**
     * @var CommandBus
     */
    protected $commandBus;

    public function setUp(): void
    {
        $this->commandBus = new CommandBus(Mocker::createApi()->reveal());
    }

    /** @test */
    public function it_adds_a_command_and_checks_commands_can_be_returned()
    {
        $this->commandBus->addCommand(MockCommand::class);
        $commands = $this->commandBus->getCommands();
        self::assertInstanceOf(MockCommand::class, $commands['mycommand']);
        self::assertCount(1, $commands);
    }

    /** @test */
    public function it_adds_multiple_commands()
    {
        $this->commandBus->addCommands([MockCommand::class, MockCommandTwo::class]);
        $commands = $this->commandBus->getCommands();

        self::assertInstanceOf(MockCommand::class, $commands['mycommand']);
        self::assertInstanceOf(MockCommandTwo::class, $commands['mycommand2']);
    }

    /** @test */
    public function it_removes_a_command()
    {
        $this->commandBus->addCommands([MockCommand::class, MockCommandTwo::class]);
        $this->commandBus->removeCommand('mycommand');

        $commands = $this->commandBus->getCommands();

        self::assertCount(1, $commands);
        self::assertArrayNotHasKey('mycommand', $commands);
        self::assertInstanceOf(MockCommandTwo::class, $commands['mycommand2']);
    }

    /** @test */
    public function it_removes_multiple_commands()
    {
        $this->commandBus->addCommands([MockCommand::class, MockCommandTwo::class]);
        $this->commandBus->removeCommands(['mycommand', 'mycommand2']);

        $commands = $this->commandBus->getCommands();

        self::assertCount(0, $commands);
        self::assertArrayNotHasKey('mycommand', $commands);
        self::assertArrayNotHasKey('mycommand2', $commands);
    }

    /**
     * @test
     */
    public function it_checks_a_supplied_command_object_is_of_the_correct_type()
    {
        $this->expectException(TelegramSDKException::class);

        $this->commandBus->addCommand(new \stdClass());
    }

    /**
     * @test
     */
    public function it_throws_exception_if_supplied_command_class_does_not_exist()
    {
        $this->expectException(TelegramSDKException::class);

        $this->commandBus->addCommand('nonexistclass');
    }

    /**
     * @test
     */
    public function it_throws_exception_if_message_is_only_blank_text()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->markTestSkipped('todo');
        $this->commandBus->parseCommand('');
    }

    /** @test */
    public function it_parses_the_commandText_correctly()
    {
        $result = $this->commandBus->parseCommand('/userCommand@botname arg1 arg2');

        self::assertEquals('userCommand', $result[1]);
        self::assertEquals('botname', $result[2]);
        self::assertEquals('arg1 arg2', $result[3]);
    }

    /** @test */
    public function it_parses_the_commandText_correctly_2()
    {
        $result = $this->commandBus->parseCommand('/userCommand arg1 arg2');

        self::assertEquals('userCommand', $result[1]);
        self::assertEmpty($result[2]);
        self::assertEquals('arg1 arg2', $result[3]);
    }

    /** @test */
    public function it_parses_the_commandText_correctly_3()
    {
        $result = $this->commandBus->parseCommand('sometext first /userCommand arg1 arg2');

        self::assertEmpty($result);
    }

    /** @test */
    public function it_returns_the_result_from_the_handle_method_on_the_command()
    {
        $command = new MockCommand();
        $this->commandBus->addCommand($command);

        $res = $this->commandBus->execute('mycommand', '', Mocker::createUpdateObject()->reveal());

        self::assertEquals('mycommand handled', $res);
    }

    /** @test */
    public function it_handles_a_command_and_returns_the_update_object_correctly()
    {
        $command = Mocker::createMockCommand('mycommand');
        $this->commandBus->addCommand($command->reveal());

        $result = $this->commandBus->handler('/mycommand', Mocker::createUpdateObject()->reveal());

        self::assertInstanceOf(Update::class, $result);
    }

    /** @test */
    public function it_checks_a_commands_dependencies_will_be_resolved_if_an_ioc_container_has_been_set()
    {
        //Make an API with an IOC container
        $this->commandBus = new CommandBus(Mocker::createApi(true)->reveal());
        $this->commandBus->addCommand('\Telegram\Bot\Tests\Mocks\MockCommandWithDependency');
        $allCommands = $this->commandBus->getCommands();

        self::assertCount(1, $allCommands);
        self::assertArrayHasKey('mycommandwithdependency', $allCommands);
        self::assertInstanceOf(Command::class, $allCommands['mycommandwithdependency']);
    }
}
