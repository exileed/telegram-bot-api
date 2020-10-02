<?php

namespace Telegram\Bot\Objects;

/**
 * Class StickerSet.
 *
 *
 * @method string    getName()          Sticker set name.
 * @method string    getTitle()         Sticker set title.
 * @method bool      getIsAnimated()    True, if the sticker is animated.
 * @method bool      getContainsMasks() (Optional). True, if the sticker set contains masks.
 * @method Sticker[] getStickers()      List of all set stickers.
 * @method PhotoSize getThumb()       (Optional). Sticker set thumbnail in the .WEBP or .TGS format
 */
class StickerSet extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'stickers' => Sticker::class,
            'thumb' => PhotoSize::class,
        ];
    }
}
