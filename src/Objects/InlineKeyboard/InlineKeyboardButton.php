<?php

namespace Telegram\Bot\Objects\InlineKeyboard;

use Telegram\Bot\Objects\BaseObject;

/**
 * Class InlineKeyboardButton
 *
 *
 * @method string       getText()                      Label text on the button.
 * @method string       getUrl()                       (Optional). HTTP or tg:// url to be opened when button is
 * pressed.
 * @method LoginUrl     getLoginUrl()                  (Optional). An HTTP URL used to automatically authorize the
 * user.
 * @method string       getSwitchInlineQuery()         (Optional) If set, pressing the button will prompt the user to
 * select one
 * of their chats, open that chat and insert the bot‘s username and the specified inline query in the input field.
 * messages to the user.
 * @method string   getSwitchInlineQueryCurrentChat()  (Optional) If set, pressing the button will insert the bot‘s username and the specified inline query in the current chat's input field.
 * @method CallbackGame getCallbackGame()              (Optional) Description of the game that will be launched when the user presses the button.
 * @method bool         getPay()                       (Optional) Specify True, to send a Pay button.
 */
class InlineKeyboardButton extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'login_url'     => LoginUrl::class,
            'callback_game' => CallbackGame::class,
        ];
    }
}
