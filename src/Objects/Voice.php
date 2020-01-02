<?php

namespace Telegram\Bot\Objects;

/**
 * Class Voice.
 *
 *
 * @method string   getFileId()        Unique identifier for this file.
 * @method string   getFileUniqueId()  Unique identifier for this file, which is supposed to be the same over time and for different bots.
 * @method int      getDuration()      Duration of the audio in seconds as defined by sender.
 * @method string   getMimeType()      (Optional). MIME type of the file as defined by sender.
 * @method int      getFileSize()      (Optional). File size.
 */
class Voice extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [];
    }
}
