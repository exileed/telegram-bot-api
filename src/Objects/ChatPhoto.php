<?php

namespace Telegram\Bot\Objects;

/**
 * Class ChatPhoto.
 *
 *
 * @method string   getSmallFileId()       Unique file identifier of small (160x160) chat photo.
 * @method string   getSmallFileUniqueId() Unique file identifier of small (160x160) chat photo, which is supposed to
 * be the same over time and for different bots.
 * @method string   getBigFileId()         Unique file identifier of big (640x640) chat photo.
 * @method string   getBigFileUniqueId()   Unique file identifier of small (640x640) chat photo, which is supposed to be
 * the same over time and for different bots.
 */
class ChatPhoto extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [];
    }
}
