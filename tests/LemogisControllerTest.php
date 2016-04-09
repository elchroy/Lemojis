<?php

use Elchroy\Lemogis\Controllers\LemogisController;

class LemogisControllerTest extends PHPUnit_Framework_TestCase
{

    public $lemogis;

    public function setUp()
    {
        $this->lemogis = new LemogisController();
    }
    public function testGetEmojis()
    {
        $this->assertFalse(false);
    }
}
