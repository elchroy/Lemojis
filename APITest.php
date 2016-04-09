<?php

class ApiTest extends PHPUnit_Framework_TestCase
{
    protected $api_url = "http://localhost";

    //create a function that will allow you to call API endpoints at-will.
    private function loadEndpoint($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return array(
          'body' => $output,
          'info' => $info
        );
    }

    //this allows you to write messages in the test output
    private function printToConsole($statement)
    {
        fwrite(STDOUT, $statement."\n");
    }

    //this will test the actual body of the response against something expected.
    public function testGetUserResponse() {
      $this->printToConsole(__METHOD__);
      $url = $this->api_url."/users/124";
      $response = $this->loadEndpoint($url);

      $expected = '[{"name":"John Smith","email":"john@acme.com"}]';

      $this->assertEquals($response['body'], $expected);
    }

}
