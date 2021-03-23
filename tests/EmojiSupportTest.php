<?php

namespace Telegram\Bot\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Telegram\Bot\Exceptions\TelegramEmojiMapFileNotFoundException;
use Telegram\Bot\Helpers\Emojify;

class EmojiSupportTest extends TestCase
{
    /**
     * @test
     **/
    public function it_throws_exception_when_missing_emoji_map_is_used()
    {
        self::markTestSkipped('todo');
        $this->expectException(TelegramEmojiMapFileNotFoundException::class);

        $emoji = Emojify::getInstance();

        $emoji->setEmojiMapFile('wrong_file.json');
    }

    /** @test */
    public function it_ensures_the_default_emoji_file_is_available()
    {
        $emoji = Emojify::getInstance();

        // If we can assert the instant is of the correct type, then no exception
        // was thrown during it's creation. A valid emoji file is required
        // during creation.
        self::assertInstanceOf(Emojify::class, $emoji);
    }

    /** @test */
    public function it_replaces_a_keyword_with_an_emoji()
    {
        $plainText = 'This works! :smile:';
        $emojiText = Emojify::text($plainText);

        self::assertStringContainsString('😄', $emojiText);
    }

    /** @test */
    public function it_requires_the_keyword_to_be_enclosed_with_a_delimiter()
    {
        $plainText = 'This should not work! :smile';
        $emojiText = Emojify::text($plainText);

        self::assertStringNotContainsString('😄', $emojiText);
        self::assertStringContainsString(':smile', $emojiText);
    }

    /** @test * */
    public function it_replaces_an_emoji_with_its_keyword()
    {
        $plainText = 'This works! 😄';
        $emojiText = Emojify::translate($plainText);

        self::assertStringContainsString(':smile:', $emojiText);
    }

    /** @test * */
    public function it_ensures_a_replaced_emoji_is_enclosed_with_a_delimiter()
    {
        $plainText = 'This  😄 works!';
        $emojiText = Emojify::translate($plainText);

        self::assertStringContainsString(':smile:', $emojiText);
        self::assertStringNotContainsString(' smile ', $emojiText);
    }

    /**
     * Reset the Singleton so that previous test doesn't interfere with
     * the next one.
     */
    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(Emojify::class);
        $property = $reflection->getProperty('instance');
        $property->setAccessible(true); // instance is gone
        $property->setValue(null); // now we can modify that :)
        $property->setAccessible(false); // clean up
    }
}
