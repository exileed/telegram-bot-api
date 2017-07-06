<?php

namespace Telegram\Bot\Objects;

/**
 * Class ChatPhoto.
 *
 *
 * @method string           getSmallFileId()      Unique file identifier of small (160x160) chat photo.
 * @method string           getBigFileId()   	  Unique file identifier of big (640x640) chat photo.
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
