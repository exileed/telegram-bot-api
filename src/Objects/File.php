<?php

namespace Telegram\Bot\Objects;

/**
 * Class File.
 *
 *
 * @method string   getFileId()        Unique identifier for this file.
 * @method string   getFileUniqueId()  Unique identifier for this file, which is supposed to be the same over time and for different bots.
 * @method int      getFileSize()      (Optional). File size, if known.
 * @method string   getFilePath()      (Optional). File path. Use 'https://api.telegram
 * .org/file/bot<token>/<file_path>' to get the file.
 */
class File extends BaseObject
{
    /**
     * {@inheritdoc}
     */
    public function relations()
    {
        return [];
    }

    /**
     * @param string $token the bot token
     *
     * @return string the http url of the file
     */
    public function getUrl($token)
    {
        return 'https://api.telegram.org/file/bot' . $token . '/' . $this->getFilePath();
    }
}
