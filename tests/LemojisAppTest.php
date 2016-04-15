<?php

use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use Elchroy\Lemojis\Connections\Connection;
use Elchroy\Lemojis\LemojisApp as App;
use Elchroy\Lemojis\Models\LemojisModel as Emoji;
use Elchroy\Lemojis\Models\LemojisUser as User;
use Firebase\JWT\JWT;
use org\bovigo\vfs\vfsStream;

class LemojisAppTest extends \PHPUnit_Framework_TestCase
{
    private $app;
    private $app2;
    private $response;
    private $root;
    private $configFile1;
    private $configFile2;

    public function setUp()
    {
        $this->root = vfsStream::setup('home');
        $this->configFile1 = vfsStream::url('home/config.ini');
        $file = fopen($this->configFile1, 'a');
        $configData1 = [
                    'driver = sqlite',
                    'database = test.sqlite',
        ];
        foreach ($configData1 as $cfg) {
            fwrite($file, $cfg."\n");
        }
        fclose($file);

        $this->app = new App(new Connection($this->configFile1));
        $this->response = new \Slim\Http\Response();
    }

    public function testFirstTest()
    {
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => '/', ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $this->assertSame('Welcome to Lemoji - A Simple Naija Emoji Service.', $result);
    }

    public function testGetAll()
    {
        Emoji::truncate();
        $this->populateEmoji();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => '/emojis', ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"OK","data":[{"id":1,"name":"smile","chars":"s","keywords":"smile","category":"expressions","date_created":"2016-03-12 17:04:18","date_modified":"2016-03-12 17:04:30","created_by":"roy"},{"id":2,"name":"smiley","chars":"sly","keywords":"smilely","category":"expressions","date_created":"2016-02-12 17:04:20","date_modified":"2016-02-12 17:05:18","created_by":"roy"}]}';
        $this->assertSame($expected, $result);
    }

    public function testGetAllReturnsMessageWhenNoemojiIsFound()
    {
        Emoji::truncate();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => '/emojis', ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"There are no emojis loaded. Register and Login to create an emoji.","data":null}';
        $this->assertSame($expected, $result);
    }

    public function testGetOneEmoji()
    {
        Emoji::truncate();
        $this->populateEmoji();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => '/emojis/2', ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"OK","data":{"id":2,"name":"smiley","chars":"sly","keywords":"smilely","category":"expressions","date_created":"2016-02-12 17:04:20","date_modified":"2016-02-12 17:05:18","created_by":"roy"}}';
        $this->assertSame($expected, $result);
    }

    public function testGetOneEmojiNotAvailable()
    {
        Emoji::truncate();
        $this->populateEmoji();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => '/emojis/30', ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"Cannot find the emoji","data":null}';
        $this->assertSame($expected, $result);
    }

    private function populateEmoji()
    {
        Emoji::create([
            'name'          => 'smile',
            'chars'         => 's',
            'keywords'      => 'smile',
            'category'      => 'expressions',
            'date_created'  => '2016-03-12 17:04:18',
            'date_modified' => '2016-03-12 17:04:30',
            'created_by'    => 'roy',
        ]);
        Emoji::create([
            'name'          => 'smiley',
            'chars'         => 'sly',
            'keywords'      => 'smilely',
            'category'      => 'expressions',
            'date_created'  => '2016-02-12 17:04:20',
            'date_modified' => '2016-02-12 17:05:18',
            'created_by'    => 'roy',
        ]);
    }

    private function populateUser()
    {
        User::create([
            'username' => 'roy',
            'password' => password_hash('ceejay', PASSWORD_DEFAULT),
            'tokenID'  => null,
        ]);
        User::create([
            'username' => 'royz',
            'password' => 'ceejay',
            'tokenID'  => null,
        ]);
        User::create([
            'username' => 'royally',
            'password' => 'ceejay',
            'tokenID'  => 'HasToken',
        ]);
    }

    public function testPostToCreateOneEmoji()
    {
        $token = $this->createToken('roy');
        // First delete all the entried inside the datatabase;
        Emoji::truncate();
        User::truncate();
        $this->populateUser();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD'     => 'POST',
            'REQUEST_URI'        => '/emojis',
            'HTTP_AUTHORIZATION' => $token,
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $request = $request->withParsedBody([
            'name'     => 'smile',
            'chars'    => 's',
            'keywords' => 'These are some of the keywords. I,.,)( &*^%96 I realy liked',
            'category' => 'expressions',
        ]);
        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"The new emoji has been created successfully.","data":null}';
        $this->assertSame($expected, $result);
    }

    public function testDeleteEmoji()
    {
        $token = $this->createToken('roy');
        Emoji::truncate();
        $this->populateEmoji();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD'     => 'DELETE',
            'REQUEST_URI'        => '/emojis/2',
            'HTTP_AUTHORIZATION' => $token,
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"The emoji has been deleted.","data":null}';
        $this->assertSame($expected, $result);
    }

    public function testDeleteEmojiFailsForNoID()
    {
        $token = $this->createToken('roy');
        Emoji::truncate();
        $this->populateEmoji();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD'     => 'DELETE',
            'REQUEST_URI'        => '/emojis/50',
            'HTTP_AUTHORIZATION' => $token,
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"Cannot find the emoji to delete.","data":null}';
        $this->assertSame($expected, $result);
    }

    public function testRegister()
    {
        $token = $this->createToken('roy');
        User::truncate();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/auth/register',
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $request = $request->withParsedBody([
            'username' => 'roy',
            'password' => 'ceejay',
        ]);
        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"New user has been created successfully.","data":null}';
        $this->assertSame($expected, $result);
    }

    public function testRegisterFailsforDuplicateUsers()
    {
        $token = $this->createToken('roy');
        User::truncate();
        $this->populateUser();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/auth/register',
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $request = $request->withParsedBody([
            'username' => 'roy',
            'password' => 'ceejay',
        ]);
        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"Username already exists.","data":null}';
        $this->assertSame($expected, $result);
    }

    public function testLogin()
    {
        $token = $this->createToken('roy');
        User::truncate();
        $this->populateUser();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/auth/login',
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $request = $request->withParsedBody([
            'username' => 'roy',
            'password' => 'ceejay',
        ]);

        $request = $request->withAttribute('TokenTime', 1440302375);

        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"Logged In Successfully","token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NDAzMDIzNzUsImp0aSI6Ik1UUTBNRE13TWpNM05RPT0iLCJuYmYiOjE0NDAzMDIzODUsImV4cCI6MTQ0MDMwNDM4NSwiZGF0YSI6eyJ1c2VybmFtZSI6InJveSJ9fQ.fr0N3p3QCjfSHtrW5HjodUTAgoP-m8tx-dRkBvsa0YS6FFSYXdi0yRzG1jtgzRjIAs9odwSEq_woBUkQfisysQ"}';
        $this->assertSame($expected, $result);
    }

    public function testLoginFailsForNoUser()
    {
        $token = $this->createToken('roy');
        User::truncate();
        $this->populateUser();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI'    => '/auth/login',
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $request = $request->withParsedBody([
            'username' => 'UnavailableUser',
            'password' => 'Password',
        ]);

        $request = $request->withAttribute('TokenTime', 1440302375);

        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"Incorrect username or password","data":null}';
        $this->assertSame($expected, $result);
    }

    public function testLogout()
    {
        $token = $this->createToken('roy');
        Emoji::truncate();
        $this->populateEmoji();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD'     => 'GET',
            'REQUEST_URI'        => '/auth/logout',
            'HTTP_AUTHORIZATION' => $token,
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"Successfully Logged Out","data":null}';
        $this->assertSame($expected, $result);
    }

    public function testPutUpdates()
    {
        $token = $this->createToken('roy');
        Emoji::truncate();
        $this->populateEmoji();
        User::truncate();
        $this->populateUser();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD'     => 'PUT',
            'REQUEST_URI'        => '/emojis/2',
            'HTTP_AUTHORIZATION' => $token,
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $request = $request->withParsedBody([
            'name'     => 'frownie',
            'chars'    => 'f',
            'keywords' => 'f frown frownie',
            'category' => 'facial expressions',
        ]);
        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"The emoji has been updated successfully.","data":null}';
        $this->assertSame($expected, $result);
    }

    public function testPatchUpdates()
    {
        $token = $this->createToken('roy');
        Emoji::truncate();
        $this->populateEmoji();
        User::truncate();
        $this->populateUser();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD'     => 'PATCH',
            'REQUEST_URI'        => '/emojis/2',
            'HTTP_AUTHORIZATION' => $token,
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $request = $request->withParsedBody([
            'name'     => 'frownie',
            'keywords' => 'f frown frownie',
        ]);
        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"The emoji has been updated successfully.","data":null}';
        $this->assertSame($expected, $result);
    }

    public function testPatchUpdatesFails()
    {
        $token = $this->createToken('roy');
        Emoji::truncate();
        $this->populateEmoji();
        User::truncate();
        $this->populateUser();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD'     => 'PATCH',
            'REQUEST_URI'        => '/emojis/50',
            'HTTP_AUTHORIZATION' => $token,
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $request = $request->withParsedBody([
            'name'     => 'frownie',
            'keywords' => 'f frown frownie',
        ]);
        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"Cannot find the emoji to update.","data":null}';
        $this->assertSame($expected, $result);
    }

    /**
     * @expectedException Elchroy\Lemojis\Exceptions\WrongConfigurationException
     * @expectedExceptionMessage Ensure that the config.ini file has been created at the root directory of your application.
     */
    public function testExceptionForNoConfigFile()
    {
        $UnavailableConfigFile = '';
        $conn = new Connection($UnavailableConfigFile);
        $config = $conn->loadConfiguration($UnavailableConfigFile);
        // $this->assertEquals($config, $expConfigdata);
    }

    public function testConnectionClass()
    {
        $configFile2 = vfsStream::url('home/config2.ini');
        $file = fopen($configFile2, 'a');
        $configData = [
                    'driver = mysql',
                    'host = localhost',
                    'database = elchroy',
                    'username = root',
                    'password =',
                    'charset = utf8',
                    'collation = utf8_unicode_ci',
                    'prefix =',
        ];
        foreach ($configData as $cfg) {
            fwrite($file, $cfg."\n");
        }
        fclose($file);

        $expConfigdata = parse_ini_file($configFile2);
        $conn = new Connection($configFile2);
        $config = $conn->loadConfiguration($configFile2);
        $this->assertEquals($config, $expConfigdata);
    }

    /**
     * @expectedException Elchroy\Lemojis\Exceptions\WrongConfigurationException
     * @expectedExceptionMessage Only SQLite and MySQL database are supported at the moment.
     */
    public function testConnectionClassThrowsException()
    {
        $configFile2 = vfsStream::url('home/config2.ini');
        $file = fopen($configFile2, 'a');
        $configData = [
                    'driver = WrongDriver',
                    'host = localhost',
                    'database = elchroy',
                    'username = root',
                    'password =',
                    'charset = utf8',
                    'collation = utf8_unicode_ci',
                    'prefix =',
        ];
        foreach ($configData as $cfg) {
            fwrite($file, $cfg."\n");
        }
        fclose($file);

        $expConfigdata = parse_ini_file($configFile2);
        $conn = new Connection($configFile2);
        $config = $conn->loadConfiguration($configFile2);
    }

    public function testDecodingFails()
    {
        $token = $this->createToken('roy');
        // $token = substr_replace($token,'*',-1);
        Emoji::truncate();
        $this->populateEmoji();
        User::truncate();
        $this->populateUser();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD'     => 'PUT',
            'REQUEST_URI'        => '/emojis/2',
            'HTTP_AUTHORIZATION' => $token,
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $request = $request->withParsedBody([
            'name'     => 'frownie',
            'keywords' => 'f frown frownie',
        ]);
        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"The emoji has been updated successfully.","data":null}';
        $this->assertSame($expected, $result);
    }

    public function testPutFailsToUpdatesForUnavailableemoji()
    {
        $token = $this->createToken('roy');
        Emoji::truncate();
        $this->populateEmoji();
        User::truncate();
        $this->populateUser();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD'     => 'PUT',
            'REQUEST_URI'        => '/emojis/55',
            'HTTP_AUTHORIZATION' => $token,
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $request = $request->withParsedBody([
            'name'     => 'frownie',
            'chars'    => 'f',
            'keywords' => 'f frown frownie',
            'category' => 'facial expressions',
        ]);
        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"Cannot find the emoji to update.","data":null}';
        $this->assertSame($expected, $result);
    }

    public function testCheckExpiredToken()
    {
        $token = $this->createToken('roy', time() - 10000);
        Emoji::truncate();
        $this->populateEmoji();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD'     => 'PUT',
            'REQUEST_URI'        => '/emojis/2',
            'HTTP_AUTHORIZATION' => $token,
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $request = $request->withParsedBody([
            'name'     => 'frownie',
            'chars'    => 'f',
            'keywords' => 'f frown frownie',
            'category' => 'facial expressions',
        ]);
        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"Token is Expired. Please re-login.","data":null}';
        $this->assertSame($expected, $result);
    }

    public function testForAuthorisationHeader()
    {
        Emoji::truncate();
        $this->populateEmoji();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD' => 'PUT',
            'REQUEST_URI'    => '/emojis/2',
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $request = $request->withParsedBody([
            'name'     => 'frownie',
            'chars'    => 'f',
            'keywords' => 'f frown frownie',
            'category' => 'facial expressions',
        ]);
        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"Bad Request - Token not found in request. Please Login","data":null}';
        $this->assertSame($expected, $result);
    }

    public function testForTokenInAuthorisationHeader()
    {
        Emoji::truncate();
        $this->populateEmoji();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD'     => 'PUT',
            'REQUEST_URI'        => '/emojis/2',
            'HTTP_AUTHORIZATION' => '',
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $request = $request->withParsedBody([
            'name'     => 'frownie',
            'chars'    => 'f',
            'keywords' => 'f frown frownie',
            'category' => 'facial expressions',
        ]);
        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"Please Provide Token From Login","data":null}';
        $this->assertSame($expected, $result);
    }

    public function testUserIsLoggedOut()
    {
        $token = $this->createToken('roy');
        Emoji::truncate();
        $this->populateEmoji();
        $user = User::where('username', 'roy')->first();
        $user->tokenID = 'MTQ2MDI0MjE4MA==';
        $user->save();
        $environment = \Slim\Http\Environment::mock([
            'REQUEST_METHOD'     => 'POST',
            'REQUEST_URI'        => '/emojis',
            'HTTP_AUTHORIZATION' => $token,
            ]
        );
        $request = \Slim\Http\Request::createFromEnvironment($environment);
        $request = $request->withParsedBody([
            'name'     => 'frownie',
            'chars'    => 'f',
            'keywords' => 'f frown frownie',
            'category' => 'facial expressions',
        ]);
        $response = new \Slim\Http\Response();
        $app = $this->app;
        $response = $app($request, $response, []);

        $result = ((string) $response->getBody());
        $expected = '{"message":"Please Re-login.","data":null}';
        $this->assertSame($expected, $result);
    }

    private function createToken($username, $time = null)
    {
        $time = $time === null ? (time() - 10) : $time;
        $tokenId = base64_encode('roy');
        $issuedAt = $time;
        $notBefore = $issuedAt + 10;
        $expire = $notBefore + 2000;
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
}
