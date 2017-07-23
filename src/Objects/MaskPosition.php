<?php

namespace Telegram\Bot\Objects;

/**
 * Class MaskPosition.
 *
 *
 * @method string   getPoint()   The part of the face relative to which the mask should be placed. One of “forehead”, “eyes”, “mouth”, or “chin”.
 * @method float    getXShift()  Shift by X-axis measured in widths of the mask scaled to the face size, from left to right.
 * @method float    getYShift()  Shift by Y-axis measured in heights of the mask scaled to the face size, from top to bottom.
 * @method float    getZoom()    Mask scaling coefficient. For example, 2.0 means double size.
 */
class MaskPosition extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [];
    }
}
