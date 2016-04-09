<?php

use Slim\Http\Environment;
use Elchroy\Lemogis\LemogisApp as App;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Elchroy\Lemogis\Models\LemogisModel as Model;
use Firebase\JWT\JWT;

class LemogisAppTest extends \PHPUnit_Framework_TestCase {

    private $app;
    private $response;

    public function setUp()
    {
        $_SESSION = array();
        $this->app = new App();
        $this->response = new \Slim\Http\Response();
    }

    // public function request($method, $path, $options = array())
    // {
    //     // Capture STDOUT
    //     ob_start();
    //     // Prepare a mock environment
    //     Environment::mock(array_merge(array(
    //         'REQUEST_METHOD' => $method,
    //         'PATH_INFO' => $path,
    //         'SERVER_NAME' => 'slim-test.dev',
    //     ), $options));
    //     $app = new App();
    //     $this->app = $app;
    //     $this->request = $app->request($);
    //     $this->response = $app->response();
    //     // Return STDOUT
    //     return ob_get_clean();
    // }

    // public function get($path, $options = array())
    // {
    //     $this->request('GET', $path, $options);
    // }

    // public function testIndex()
    // {
    //     $this->get('/');
    //     $this->assertEquals('200', $this->response->status());
    // }

    public function notestGetRequestReturnsEcho()
    {
        // instantiate action
        $action = new App();

        // We need a request and response object to invoke the action
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/echo',
            'QUERY_STRING'=>'foo=bar']
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();

        // run the controller action and test it
        $response = $action($request, $response, []);
        $this->assertSame((string)$response->getBody(), '{"foo":"bar"}');
    }

    public function testFirstTest()
    {
        $action = new App();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/hello/roy']
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $response = $action($request, $response, []);

        $result = ((string) $response->getBody());
        $this->assertSame('Hello, roy', $result);
    }

    public function testGetAll()
    {
        Model::truncate();
        $this->fakePopulate();
        $action = new App();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/emogis']
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $response = $action($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '[{"id":1,"name":"smile","chars":"s","keywords":"smile","category":"expressions","date_created":"2016-03-12 17:04:18","date_modified":"2016-03-12 17:04:30","created_by":"roy"},{"id":2,"name":"smiley","chars":"sly","keywords":"smilely","category":"expressions","date_created":"2016-02-12 17:04:20","date_modified":"2016-02-12 17:05:18","created_by":"roy"}]';
        $this->assertSame($expected, $result);
    }

    public function testGetOneEmoji()
    {
        Model::truncate();
        $this->fakePopulate();
        $action = new App();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/emogis/2']
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $response = $action($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"OK","data":{"id":2,"name":"smiley","chars":"sly","keywords":"smilely","category":"expressions","date_created":"2016-02-12 17:04:20","date_modified":"2016-02-12 17:05:18","created_by":"roy"}}';
        $this->assertSame($expected, $result);
    }

    private function fakePopulate()
    {
        Model::create([
            'name' => 'smile',
            'chars' => 's',
            'keywords' => 'smile',
            'category' => 'expressions',
            'date_created' => '2016-03-12 17:04:18',
            'date_modified' => '2016-03-12 17:04:30',
            'created_by' => 'roy',
        ]);
        Model::create([
            'name' => 'smiley',
            'chars' => 'sly',
            'keywords' => 'smilely',
            'category' => 'expressions',
            'date_created' => '2016-02-12 17:04:20',
            'date_modified' => '2016-02-12 17:05:18',
            'created_by' => 'roy',
        ]);
    }

    public function testPostToCreateOneEmoji()
    {
        $token = $this->createToken('roy');
        // First delete all the entried inside the datatabase;
        Model::truncate();
        $action = new App();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => '/emogis',
            'HTTP_AUTHORIZATION' => $token,
            'slim.input' => 'username=world',
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        // $req = Request::createFromEnvironment($env);
        $request = $request->withParsedBody([
            'name' => 'smile',
            'chars' => 's',
            'keywords' => 'smile',
            'category' => 'expressions'
        ]);
        $response = new \Slim\Http\Response();
        $response = $action($request, $response);

        $result = ((string) $response->getBody());
        $expected = '{"message":"The new emoji has been created successfully.","data":null}}';
        $this->assertSame($expected, $result);
    }

    public function testDeleteEmoji()
    {
        $token = $this->createToken('roy');
        Model::truncate();
        $this->fakePopulate();
        $action = new App();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'DELETE',
            'REQUEST_URI' => '/emogis/2',
            'HTTP_AUTHORIZATION' => $token,
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $response = $action($request, $response);

        $result = ((string) $response->getBody());
        $expected = '{"message":"The Emogi has been deleted.","data":null}gin.","data":null}';
        $this->assertSame($expected, $result);
    }

    private function createToken($username)
    {
        $tokenId = base64_encode('roy');
        $issuedAt = time() - 10;
        $notBefore  = $issuedAt + 10;
        $expire     = $notBefore + 2000;
        $secretKey = base64_decode('sampleSecret'); // or get the app key from the config file.
        $JWTToken = [
            'iat'  => $issuedAt,
            'jti'  => $tokenId,
            'nbf'  => $notBefore,
            'exp'  => $expire,
            'data' => ['username' => $username],
        ];

        $jwt = JWT::encode(
            $JWTToken,      //Data to be encoded in the JWT
            $secretKey, // The signing key
            'HS512'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
        );

        return $jwt;
    }


    // public function testHome() {
    //     $env = Environment::mock([
    //             'REQUEST_METHOD' => 'post',
    //             'REQUEST_URI' => '/',
    //             'QUERY_STRING' => '',
    //             'SERVER_NAME' => 'example.com',
    //             'CONTENT_TYPE' => 'application/json;charset=utf8',
    //             'CONTENT_LENGTH' => 15
    //     ]);
    //     // Environment::mock(array(
    //     //     'PATH_INFO' => '/'
    //     // ));
    //     $response = $this->app->run();
    //     dd($response);
    //     // $response = $this->app->invoke();

    //     $this->assertContains('home', $response->getBody());
    // }

    // public function testHello() {
    //     Environment::mock(array(
    //         'PATH_INFO' => '/hello/world'
    //     ));
    //     $response = $this->app->invoke();

    //     $this->assertTrue($response->isOk());
    //     $this->assertContains('hello world', $response->getBody());
    // }

    // public function testNotFound() {
    //     Environment::mock(array(
    //         'PATH_INFO' => '/not-exists'
    //     ));
    //     $response = $this->app->invoke();

    //     $this->assertTrue($response->isNotFound());
    // }

    // public function testLogin() {
    //     Environment::mock(array(
    //         'PATH_INFO' => '/login'
    //     ));
    //     $response = $this->app->invoke();

    //     $this->assertTrue($response->isRedirect());
    //     $this->assertEquals('Wrong login', $_SESSION['slim.flash']['error']);
    //     $this->assertEquals('/', $response->headers()->get('Location'));
    // }

    // public function testPostLogin() {
    //     Environment::mock(array(
    //         'REQUEST_METHOD' => 'POST',
    //         'PATH_INFO' => '/login',
    //         'slim.input' => 'login=world'
    //     ));
    //     $response = $this->app->invoke();

    //     $this->assertTrue($response->isRedirect());
    //     $this->assertEquals('Successfully logged in', $_SESSION['slim.flash']['success']);
    //     $this->assertEquals('/hello/world', $response->headers()->get('Location'));
    // }

    // public function testGetLogin() {
    //     Environment::mock(array(
    //         'PATH_INFO' => '/login',
    //         'QUERY_STRING' => 'login=world'
    //     ));
    //     $response = $this->app->invoke();

    //     $this->assertTrue($response->isRedirect());
    //     $this->assertEquals('Successfully logged in', $_SESSION['slim.flash']['success']);
    //     $this->assertEquals('/hello/world', $response->headers()->get('Location'));
    // }
}