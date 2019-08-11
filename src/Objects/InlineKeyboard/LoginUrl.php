<?php

namespace Telegram\Bot\Objects\InlineKeyboard;

use Telegram\Bot\Objects\BaseObject;

/**
 * Class LoginUrl.
 *
 *
 * @method string   getUrl()                An HTTP URL to be opened with user authorization data added to the query string when the button is pressed.
 * @method int      getForwardText()        (Optional). New text of the button in forwarded messages.
 * @method string   getBotUsername()        (Optional). Username of a bot, which will be used for user authorization.
 * @method string   getRequestWriteAccess() (Optional). True to request the permission for your bot to send messages to the user.
 */
class LoginUrl extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [];
    }
}
