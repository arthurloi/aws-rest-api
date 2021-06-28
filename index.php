<?php
header("Access-Control-Allow-Origin: *");
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Exception\NotFoundException;

require __DIR__ . '/vendor/autoload.php';
require 'InstanceManager.php';
require 'interfacesAWS.php';

$app = AppFactory::create();

$app->get('/running', function (Request $request, Response $response, array $args) {
    $status = array("restapi"=>"running");
    $jsonResponse = json_encode(($status));

    $response->getBody()->write($jsonResponse);
    return $response->withHeader('Content-Type', 'application/json');;
});

$app->get('/listid', function (Request $request, Response $response, array $args) {
    $query = $request->getQueryParams();
    $writer = new Writer('logs.logs');


    $region=$query['region'];
    $key=$query['key'];
    $secret=$query['secret'];

    $decodedregion = base64_decode($region);
    $decodedkey = base64_decode($key);
    $decodedsecret = base64_decode($secret);


    $ec2client = new InstanceManager($decodedregion,$decodedkey,$decodedsecret,$writer);
    $data = $ec2client->getIdInstances();

    foreach ($data as $id){
        $arr[] = array(
            "instanceid" => $id,
            "status" => $ec2client->instanceStatus($id)
        );
    }

    $jsondata = json_encode($arr);
    $response->getBody()->write($jsondata);
    return $response->withHeader('Content-Type', 'application/json');;
});

$app->get('/manage', function (Request $request, Response $response, array $args) {
    $query = $request->getQueryParams();
    $writer = new Writer('logs.logs');


    $region=$query['region'];
    $key=$query['key'];
    $secret=$query['secret'];
    $action=$query['action'];
    $id=$query['id'];

    $decodedregion = base64_decode($region);
    $decodedkey = base64_decode($key);
    $decodedsecret = base64_decode($secret);
    $decodedid = base64_decode($id);


    $ec2client = new InstanceManager($decodedregion,$decodedkey,$decodedsecret,$writer);

    if($action == 'start'){
        $result = $ec2client->start($decodedid);
    }elseif ($action == 'stop'){
        $result = $ec2client->stop($decodedid);
    }

    $response->getBody()->write($result);
    return $response->withHeader('Content-Type', 'application/json');;
});

$app->run();
