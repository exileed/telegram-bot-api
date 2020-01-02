<?php

namespace Telegram\Bot\Objects;

/**
 * Class PhotoSize.
 *
 *
 * @method string   getFileId()        Unique identifier for this file.
 * @method string   getFileUniqueId()  Unique identifier for this file, which is supposed to be the same over time and for different bots.
 * @method int      getWidth()         Photo width.
 * @method int      getHeight()        Photo height.
 * @method int      getFileSize()      (Optional). File size.
 */
class PhotoSize extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [];
    }
}
