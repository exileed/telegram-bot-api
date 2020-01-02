<?php

namespace Telegram\Bot\Objects;

/**
 * Class Animation.
 *
 * @method string      getFileId()         Unique file identifier.
 * @method string      getFileUniqueId()   Unique identifier for this file, which is supposed to be the same over time and for different bots.
 * @method PhotoSize[] getThumb()          (Optional). Animation thumbnail as defined by sender.
 * @method string      getFileName()       (Optional). Original animation filename as defined by sender.
 * @method string      getMimeType()       (Optional). MIME type of the file as defined by sender.
 * @method int         getFileSize()       (Optional). File size.
 */
class Animation extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'thumb' => PhotoSize::class,
        ];
    }
}
