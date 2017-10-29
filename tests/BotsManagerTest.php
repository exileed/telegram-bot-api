<?php


use PHPUnit\Framework\TestCase;
use Telegram\Bot\BotsManager;

/**
 * Class ApiTest
 */
class BotsManagerTest extends TestCase {


	/**
	 * @var BotsManager
	 */
	protected $manager;

	public function setUp()
	{
		$this->manager = new BotsManager('token');
	}

}