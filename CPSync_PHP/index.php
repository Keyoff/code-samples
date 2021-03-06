<?php
session_start();
/**
 * Created by JetBrains PhpStorm.
 * User: ferron
 * Date: 1/9/13
 * Time: 12:41 PM
 * To change this template use File | Settings | File Templates.
 */


require 'vendor/autoload.php';
require 'vendor/lncd/Oauth2/src/OAuth2/Client/Provider/CarePass.php';

use \Slim\Slim as Slim;
use \Slim\Extras\Views\Twig as TwigView;

$app = new Slim(array(
    'view' => new TwigView(),
    'debug' => true
));

$client = new CarePass(array(
    'clientId' => '2me8aqm3q5nqjtkus6yk4mxp',
    'clientSecret' => 'AkyAXqFptvSgFEVr9c9JUvH3',
    'redirectUri' => 'http://localhost/CPSync_PHP/callback',
    'scopes' => array('IDENTITY','FAMILY','INSURANCE','LIFESTYLE','ACTIVITY','APPOINTMENT','FITNESS')
));


$app->get('/', function ()  use($app) {
    return $app->render('index.twig');
});

$app->get('/login', function ()  use($client) {
    $client->authorize();
});

$app->get('/callback', function () use($client, $app) {
    $auth_code = $app->request()->get('code');
    if (isset($auth_code)) {
        $access_token = $client->getAccessToken($auth_code, array(
        'response_type' => 'code'
        ));

        if(isset($access_token)) {
            $_SESSION['access_token'] = serialize($access_token);
            $app->redirect('user');
        }
    }
});

$app->get('/user', function ()  use($client, $app) {

    $access_token = $_SESSION['access_token'];
    if(isset($access_token)) {
        $token = unserialize($access_token);
        $user =  $client->getUserDetails($token);
        return $app->render('results.twig', $user);
    }

});

$app->get('/sample', function ()  use($app) {
    return $app->render('sample.twig', array(
        'name' => 'ferron smith',
        'date' => date('Y-m-d H:i:s')
    ));
});

$app->error(function (\OAuth2\Client\IDPException $e) use ($app) {
    $app->render('error.twig');
});


$app->run();