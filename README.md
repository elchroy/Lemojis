# Lemojis

[![Coverage Status](https://coveralls.io/repos/github/andela-celisha-wigwe/Lemojis/badge.svg?branch=develop)](https://coveralls.io/github/andela-celisha-wigwe/Lemojis?branch=develop)
[![StyleCI](https://styleci.io/repos/55158494/shield)](https://styleci.io/repos/55158494)
[![Build Status](https://travis-ci.org/andela-celisha-wigwe/Lemojis.svg?branch=develop)](https://travis-ci.org/andela-celisha-wigwe/Lemojis)

A Simple PHP Naija-Emoji Service.

### Installation

To run this package, you must have PHP 5.5+ and Composer installed.

First download the package.

```
composer require elchroy/lemojis dev-develop
```

Install Composer.

```
$ composer install
```

To use the appplication you have to setup a host and database where to fetch the emojis from.

- Create a file named `config.ini` in root directory of you application.
- The package uses Laravel's `Illuminate/Database` package. Ensure your config file has `collation` and `charset` like so:

```config.ini
driver      = mysql
database    = naija
host        = localhost
username    = root
password    =
charset     = utf8
collation   = utf8_unicode_ci
prefix      =
```

### Usage

Create an `index.php` file, preferrable from the root of your application and run your server.

```PHP
// index.php

// Require the autoload the vendor folder.
require_once 'vendor/autoload.php';

// Make a new instance of the application.
$app = new Elchroy\Lemojis\LemojisApp();

// Run the application.
$app->run();
```

You can run the server using PHP from terminal like so:
```
php -S localhost:8000

PHP 7.0.2 Development Server started at Fri Apr 15 11:59:42 2016
Listening on http://localhost:8000
Document root is /Users/user/Code/CP3/Lemojis/public
Press Ctrl-C to quit.
```

 - To Illustrate the usage of this package I will use `curl` to handle http request.
 - Note that you can use tools like Postman or DHC.


#### The Home route

From the terminal, run the following `curl` command.
```
curl -i -X GET -H 'Content-Type: application/json' http://localhost:8000/
```
Response:

```
HTTP/1.1 200 OK
Host: localhost:8000
Connection: close
X-Powered-By: PHP/7.0.2
Content-Type: text/html; charset=UTF-8
Content-Length: 49

Welcome to Lemoji - A Simple Naija Emoji Service.
```

#### Get all emojis

```
curl -i -X GET -H 'Content-Type: application/json' http://localhost:8000/emojis
```

Response:

```
HTTP/1.1 200 OK
Host: localhost:8000
Connection: close
X-Powered-By: PHP/7.0.2
Content-Type: application/json;charset=utf-8
Content-Length: 378

{"message":"OK","data":[{"id":1,"name":"smile","chars":"s","keywords":"smile","category":"expressions","date_created":"2016-03-12 17:04:18","date_modified":"2016-03-12 17:04:30","created_by":"roy"},{"id":2,"name":"smiley","chars":"sly","keywords":"smilely","category":"expressions","date_created":"2016-02-12 17:04:20","date_modified":"2016-02-12 17:05:18","created_by":"roy"}]}
```

#### Get one emoji given the ID

```
curl -i -X GET -H 'Content-Type: application/json' http://localhost:8000/emojis/2
```

Response:

```
HTTP/1.1 200 OK
Host: localhost:8000
Connection: close
X-Powered-By: PHP/7.0.2
Content-Type: application/json;charset=utf-8
Content-Length: 202

{"message":"OK","data":{"id":2,"name":"smiley","chars":"sly","keywords":"smilely","category":"expressions","date_created":"2016-02-12 17:04:20","date_modified":"2016-02-12 17:05:18","created_by":"roy"}}
```

#### Get an unavailable emoji

There is a `404` response when the emoji is not found, or when there is no emoji in the database table.

```
curl -i -X GET -H 'Content-Type: application/json' http://localhost:8000/emojis/200
```

Response:

```
HTTP/1.1 404 Not Found
Host: localhost:8000
Connection: close
X-Powered-By: PHP/7.0.2
Content-Type: application/json;charset=utf-8
Content-Length: 47

{"message":"Cannot find the emoji","data":null}
```

#### Register as a user

To register and use this API use only need a username and a password.

```
curl -i -X POST -H 'Content-Type: application/json' -d '{"username" : "elchroy", "password" : "yorhcle"}' http://localhost:8000/auth/register
```

Response:

```
HTTP/1.1 201 Created
Host: localhost:8000
Connection: close
X-Powered-By: PHP/7.0.2
Content-Type: application/json;charset=utf-8
Content-Length: 65

{"message":"New user has been created successfully.","data":null}
```

#### Login

To login and use this API use need the username and password used to register.
If the login details are valid, you will be issued a token.

```
curl -i -X POST -H 'Content-Type: application/json' -d '{"username" : "elchroy", "password" : "yorhcle"}' http://localhost:8000/auth/login
```

Response:

```
HTTP/1.1 200 OK
Host: localhost:8000
Connection: close
X-Powered-By: PHP/7.0.2
Content-Type: application/json;charset=utf-8
Content-Length: 279

{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjA3MjIwNjgsImp0aSI6Ik1UUTJNRGN5TWpBMk9BPT0iLCJuYmYiOjE0NjA3MjIwNzgsImV4cCI6MTQ2MDcyNDA3OCwiZGF0YSI6eyJ1c2VybmFtZSI6ImVsY2hyb3kifX0.cNyDaqIITjdFgoS5axWbVOy5lwUJrP9KKN1RZ8H2XP4cQfSofcj1yUDLJRqUCS3GZo16rmn3Fs7uoUdqbd55Nw"}
```

#### Login failure

When the username or password provided are not valid, login is unsuccessful.

```
curl -i -X POST -H 'Content-Type: application/json' -d '{"username" : "elchroy", "password" : "incorrectpassword"}' http://localhost:8000/auth/login
```

Response:

```
HTTP/1.1 404 Not Found
Host: localhost:8000
Connection: close
X-Powered-By: PHP/7.0.2
Content-Type: application/json;charset=utf-8
Content-Length: 56

{"message":"Incorrect username or password","data":null}
```

Only authorized users (users with a token) have access to make request to `create` an emoji, `update` an emoji or `delete` an emoji.

#### Create an emoji

```
curl -i -X POST -H 'Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjA3MjYzNzIsImp0aSI6Ik1UUTJNRGN5TmpNM01nPT0iLCJuYmYiOjE0NjA3MjYzODIsImV4cCI6MTQ2MDcyODM4MiwiZGF0YSI6eyJ1c2VybmFtZSI6ImVsY2hyb3kifX0.z22I-1QZwolyVKxE7UwoBUx0UmUJ4qd-ueRMPgNA50WhDCUHGYLFa1Kfw7mQss2SUJoGE5LPAKj_Kk6fkKvMdw' -d '{"name" : "Aunty!", "chars" : "o", "keywords" : "raise hands woman girl", "category" : "people"}' http://localhost:8000/emojis
```

Response:

```
HTTP/1.1 201 OK
Host: localhost:8000
Connection: close
X-Powered-By: PHP/7.0.2
Content-Type: application/json;charset=utf-8
Content-Length: 57

{"message":"The new emoji has been created successfully.","data":null}
```

#### Update an emoji

This is the case where you want to update all the attributes of an emoji.

```
curl -i -X PUT -H 'Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjA3MjYzNzIsImp0aSI6Ik1UUTJNRGN5TmpNM01nPT0iLCJuYmYiOjE0NjA3MjYzODIsImV4cCI6MTQ2MDcyODM4MiwiZGF0YSI6eyJ1c2VybmFtZSI6ImVsY2hyb3kifX0.z22I-1QZwolyVKxE7UwoBUx0UmUJ4qd-ueRMPgNA50WhDCUHGYLFa1Kfw7mQss2SUJoGE5LPAKj_Kk6fkKvMdw' -d '{"name" : "Aunties!", "chars" : "Au", "keywords" : "praise pretty women ladies", "category" : "people"}' http://localhost:8000/emojis/2
```

Response:

```
HTTP/1.1 200 OK
Host: localhost:8000
Connection: close
X-Powered-By: PHP/7.0.2
Content-Type: application/json;charset=utf-8
Content-Length: 57

{"message":"The emoji has been updated successfully.","data":null}
```

#### Update an emoji (partially)

This is the case where you want to update only some attributes of an emoji.

```
curl -i -X PATCH -H 'Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjA3MjYzNzIsImp0aSI6Ik1UUTJNRGN5TmpNM01nPT0iLCJuYmYiOjE0NjA3MjYzODIsImV4cCI6MTQ2MDcyODM4MiwiZGF0YSI6eyJ1c2VybmFtZSI6ImVsY2hyb3kifX0.z22I-1QZwolyVKxE7UwoBUx0UmUJ4qd-ueRMPgNA50WhDCUHGYLFa1Kfw7mQss2SUJoGE5LPAKj_Kk6fkKvMdw' -d '{"name" : "Aunties!", "chars" : "Au"}' http://localhost:8000/emojis/2
```

Response:

```
HTTP/1.1 200 OK
Host: localhost:8000
Connection: close
X-Powered-By: PHP/7.0.2
Content-Type: application/json;charset=utf-8
Content-Length: 57

{"message":"The emoji has been updated successfully.","data":null}
```

#### Delete an emoji

```
curl -i -X DELETE -H 'Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjA3MjYzNzIsImp0aSI6Ik1UUTJNRGN5TmpNM01nPT0iLCJuYmYiOjE0NjA3MjYzODIsImV4cCI6MTQ2MDcyODM4MiwiZGF0YSI6eyJ1c2VybmFtZSI6ImVsY2hyb3kifX0.z22I-1QZwolyVKxE7UwoBUx0UmUJ4qd-ueRMPgNA50WhDCUHGYLFa1Kfw7mQss2SUJoGE5LPAKj_Kk6fkKvMdw' http://localhost:8000/emojis/2
```

Response:

```
HTTP/1.1 200 OK
Host: localhost:8000
Connection: close
X-Powered-By: PHP/7.0.2
Content-Type: application/json;charset=utf-8
Content-Length: 53

{"message":"The emoji has been deleted.","data":null}
```

#### Update/Delete - When the emoji cannot be found

For a request that is needed to create or delete an emoji that cannot be found,
a `404` response is returned to the user.

```
curl -i -X DELETE -H 'Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjA3MjYzNzIsImp0aSI6Ik1UUTJNRGN5TmpNM01nPT0iLCJuYmYiOjE0NjA3MjYzODIsImV4cCI6MTQ2MDcyODM4MiwiZGF0YSI6eyJ1c2VybmFtZSI6ImVsY2hyb3kifX0.z22I-1QZwolyVKxE7UwoBUx0UmUJ4qd-ueRMPgNA50WhDCUHGYLFa1Kfw7mQss2SUJoGE5LPAKj_Kk6fkKvMdw' http://localhost:8000/emojis/30000
```

Response:

```
HTTP/1.1 404 Not Found
Host: localhost:8000
Connection: close
X-Powered-By: PHP/7.0.2
Content-Type: application/json;charset=utf-8
Content-Length: 53

{"message":"Cannot find the emoji to update.","data":null}
```

#### Unauthorized access to private routes

Any request to a private route (CREATE, DELETE, UPDATE), with out appropriate validation in the token will return a response to the user.

*For a case where there is not token provided in the request header.*

```
curl -i -X DELETE -H 'Content-Type: application/json' http://localhost:8000/emojis/2
```

Response:

```
HTTP/1.1 400 Bad Request
Host: localhost:8000
Connection: close
X-Powered-By: PHP/7.0.2
Content-Type: application/json;charset=utf-8
Content-Length: 80

{"message":"Bad Request - Token not found in request. Please Login","data":null}
```

*For a case where the token in the header is expired.*

```
curl -i -X DELETE -H 'Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjA3MjYzNzIsImp0aSI6Ik1UUTJNRGN5TmpNM01nPT0iLCJuYmYiOjE0NjA3MjYzODIsImV4cCI6MTQ2MDcyODM4MiwiZGF0YSI6eyJ1c2VybmFtZSI6ImVsY2hyb3kifX0.z22I-1QZwolyVKxE7UwoBUx0UmUJ4qd-ueRMPgNA50WhDCUHGYLFa1Kfw7mQss2SUJoGE5LPAKj_Kk6fkKvMdw' http://localhost:8000/emojis/2
```

Response:

```
HTTP/1.1 405 Method Not Allowed
Host: localhost:8000
Connection: close
X-Powered-By: PHP/7.0.2
Content-Type: application/json;charset=utf-8
Content-Length: 80

{"message":"Token is Expired. Please re-login.","data":null}
```

#### Logout

Only users that have already logged in can logout.

```
curl -i -X GET -H 'Authorization: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0NjA3MjYzNzIsImp0aSI6Ik1UUTJNRGN5TmpNM01nPT0iLCJuYmYiOjE0NjA3MjYzODIsImV4cCI6MTQ2MDcyODM4MiwiZGF0YSI6eyJ1c2VybmFtZSI6ImVsY2hyb3kifX0.z22I-1QZwolyVKxE7UwoBUx0UmUJ4qd-ueRMPgNA50WhDCUHGYLFa1Kfw7mQss2SUJoGE5LPAKj_Kk6fkKvMdw' http://localhost:8000/auth/logout
```

Response:

```
HTTP/1.1 200 OK
Host: localhost:8000
Connection: close
X-Powered-By: PHP/7.0.2
Content-Type: application/json;charset=utf-8
Content-Length: 49

{"message":"Successfully Logged Out","data":null}
```

### Test

To test this package, you can use [PHPUnit](https://phpunit.de/), from command line (WindowsOS) or terminal(MacOS).

**Note: Ensure to `cd` to root directory of the application.**

`$ phpunit`
