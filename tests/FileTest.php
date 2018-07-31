<?php

namespace Telegram\Bot\Tests;

use Telegram\Bot\Objects\File;

class FileTest extends \PHPUnit\Framework\TestCase
{
    public function test_get_url()
    {
        $file = new File([
            'file_id'   => 'someRandomString',
            'file_size' => '2054',
            'file_path' => '<file_path>',
        ]);
        $this->assertEquals('https://api.telegram.org/file/bot<token>/<file_path>', $file->getUrl('<token>'));
    }
}
