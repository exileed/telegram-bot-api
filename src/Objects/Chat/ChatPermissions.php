<?php

namespace Telegram\Bot\Objects\Chat;

use Telegram\Bot\Objects\BaseObject;

/**
 * Class ChatPermissions.
 *
 *
 * @method bool   getCanSendMessages()        (Optional) True, if the user is allowed to send text messages,
 * contacts, locations and venues.
 * @method bool   getCanSendMediaMessages()   (Optional) True, if the user is allowed to send audios, documents,
 * photos, videos, video notes and voice notes.
 * @method bool   getCanSendPools()           (Optional) True, if the user is allowed to send polls.
 * @method bool   getCanSendOtherMessages()   (Optional) True, if the user is allowed to send animations, games,
 * stickers and use inline bots.
 * @method bool   getCanSendWebPagePreviews() (Optional) True, if the user is allowed to add web page previews to
 * their messages.
 * @method bool   getCanChangeInfo()          (Optional) True, if the user is allowed to change the chat title, photo
 * and other settings. Ignored in public supergroups.
 * @method bool   getCanInviteUsers()         (Optional) True, if the user is allowed to invite new users to the chat.
 * @method bool   getCanPinMessages()         (Optional) True, if the user is allowed to pin messages. Ignored in
 * public supergroups.
 *
 */
class ChatPermissions extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [];
    }
}
