<?php

namespace Telegram\Bot\Objects;

/**
 * Class Sticker.
 *
 *
 * @method string       getFileId()       Unique identifier for this file.
 * @method int          getWidth()        Sticker width.
 * @method int          getHeight()       Sticker height.
 * @method PhotoSize    getThumb()        (Optional). Sticker thumbnail in .webp or .jpg format.
 * @method string       getEmoji()        (Optional). Emoji associated with the sticker
 * @method string       getSetName()      (Optional). Name of the sticker set to which the sticker belongs
 * @method MaskPosition getMaskPosition() (Optional). Optional. For mask stickers, the position where the mask should be placed
 * @method int          getFileSize()     (Optional). File size.
 */
class Sticker extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'thumb'         => PhotoSize::class,
            'mask_position' => MaskPosition::class,
        ];
    }
}
