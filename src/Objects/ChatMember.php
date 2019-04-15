<?php

namespace Telegram\Bot\Objects;

/**
 * Class ChatMember.
 *
 * @method User            getUser()                                Information about the user.
 * @method string          getStatus()                  (Optional). The member's status in the chat. Can be “creator”, “administrator”, “member”, “left” or “kicked”
 * @method int             getUntilDate()               (Optional). Restictred and kicked only. Date when restrictions will be lifted for this user, unix time
 * @method bool            getCanBeEdited()             (Optional). Administrators only. True, if the bot is allowed to edit administrator privileges of that user
 * @method bool            getCanChangeInfo()           (Optional). Administrators only. True, if the administrator can change the chat title, photo and other settings
 * @method bool            getCanPostMessages()         (Optional). Administrators only. True, if the administrator can post in the channel, channels only
 * @method bool            getCanEditMessages()         (Optional). Administrators only. True, if the administrator can edit messages of other users, channels only
 * @method bool            getCanDeleteMessages()       (Optional). Administrators only. True, if the administrator can delete messages of other users
 * @method bool            getCanInviteUsers()          (Optional). Administrators only. True, if the administrator can invite new users to the chat
 * @method bool            getCanRestrictMembers()      (Optional). Administrators only. True, if the administrator can restrict, ban or unban chat members
 * @method bool            getCanPinMessages()          (Optional). Administrators only. True, if the administrator can pin messages, supergroups only
 * @method bool            getCanPromoteMembers()       (Optional). Administrators only. True, if the administrator can add new administrators with a subset of his own privileges
 * @method bool            getIsMember()                (Optional). Restricted only. True, if the user is a member of the chat at the moment of the request
 * @method bool            getCanSendMessages()         (Optional). Restricted only. True, if the user can send text messages, contacts, locations and venues
 * @method bool            getCanSendMediaMessages()    (Optional). Restricted only. True, if the user can send audios, documents, photos, videos, video notes and voice notes, implies can_send_messages
 * @method bool            getCanSendOtherMessages()    (Optional). Restricted only. True, if the user can send animations, games, stickers and use inline bots, implies can_send_media_messages
 * @method bool            getCanSendWebPagePreviews()  (Optional). Restricted only. True, if user may add web page previews to his messages, implies can_send_media_messages
 */
class ChatMember extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'user'    => User::class,
        ];
    }
}
