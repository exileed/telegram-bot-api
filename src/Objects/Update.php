<?php

namespace Telegram\Bot\Objects;

/**
 * Class Update.
 *
 *
 * @method int                  getUpdateId()               The update's unique identifier. Update identifiers start from a certain positive number and increase sequentially.
 * @method Message              getMessage()                (Optional). New incoming message of any kind - text, photo, sticker, etc.
 * @method Message              getEditedMessage()          (Optional). New version of a message that is known to the bot and was edited.
 * @method Message              getChannelPost()            (Optional). New incoming channel post of any kind — text, photo, sticker, etc.
 * @method Message              getEditedChannelPost()      (Optional). New version of a channel post that is known to the bot and was edited.
 * @method InlineQuery          getInlineQuery()            (Optional). New incoming inline query.
 * @method ChosenInlineResult   getChosenInlineResult()     (Optional). A result of an inline query that was chosen by the user and sent to their chat partner.
 * @method CallbackQuery        getCallbackQuery()          (Optional). Incoming callback query.
 * @method ShippingQuery        getShippingQuery()          (Optional). New incoming shipping query.
 * @method PreCheckoutQuery     getPreCheckoutQuery()       (Optional). New incoming pre-checkout query.
 *
 * @link https://core.telegram.org/bots/api#update
 */
class Update extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [
            'message'              => Message::class,
            'edited_message'       => EditedMessage::class,
            'channel_post'         => Message::class,
            'edited_channel_post'  => EditedMessage::class,
            'inline_query'         => InlineQuery::class,
            'chosen_inline_result' => ChosenInlineResult::class,
            'callback_query'       => CallbackQuery::class,
            'shipping_query'       => ShippingQuery::class,
            'pre_checkout_query'   => PreCheckoutQuery::class,
        ];
    }

    /**
     * Get recent message.
     *
     * @return Update
     */
    public function recentMessage()
    {
        return new static($this->last());
    }

    /**
     * Determine if the update is of given type.
     *
     * @param string $type
     *
     * @return bool
     */
    public function isType($type)
    {
        if ($this->has(strtolower($type))) {
            return true;
        }

        return $this->detectType() === $type;
    }

    /**
     * Detect type based on properties.
     *
     * @return string|null
     */
    public function detectType()
    {
        $types = [
            'message',
            'edited_message',
            'channel_post',
            'edited_channel_post',
            'inline_query',
            'chosen_inline_result',
            'callback_query',
        ];

        return $this->keys()
                    ->intersect($types)
                    ->pop();
    }

    /**
     * Return the related message.
     *
     * @return null|Message
     */
    public function getRelatedMessage()
    {
        if ($this->has('message')) {
            return $this->getMessage();
        } elseif ($this->has('edited_message')) {
            return $this->getEditedMessage();
        } elseif ($this->has('callback_query')) {
            return $this->getCallbackQuery()->getMessage();
        } elseif ($this->has('channel_post')) {
            return $this->getChannelPost();
        } elseif ($this->has('edited_channel_post')) {
            return $this->getEditedChannelPost();
        }
    }

    /**
     * Get message object (if exists).
     *
     * @return null|Chat
     */
    public function getChat()
    {
        switch ($this->detectType()) {
            case 'message':
                return $this->getMessage()->getChat();
            case 'edited_message':
                return $this->getEditedMessage()->getChat();
            case 'channel_post':
                return $this->getChannelPost()->getChat();
            case 'callback_query':
                return $this->getCallbackQuery()->getMessage()->getChat();
            default:
                // nothing to return
                return;
        }
    }

    /**
     * Return the related user that created the update.
     *
     * @return null|User
     */
    public function getFrom()
    {
        if ($this->has('message')) {
            return $this->getMessage()->getFrom();
        } elseif ($this->has('edited_message')) {
            return $this->getEditedMessage()->getFrom();
        } elseif ($this->has('inline_query')) {
            return $this->getInlineQuery()->getFrom();
        } elseif ($this->has('chosen_inline_result')) {
            return $this->getChosenInlineResult()->getFrom();
        } elseif ($this->has('callback_query')) {
            return $this->getCallbackQuery()->getFrom();
        } elseif ($this->has('channel_post')) {
            return $this->getChannelPost()->getFrom();
        } elseif ($this->has('edited_channel_post')) {
            return $this->getEditedChannelPost()->getFrom();
        }
    }
}
