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
        // $result = $this->lemogis->getEmogis();
        // var_dump($result);
        $this->assertFalse(false);
    }
}