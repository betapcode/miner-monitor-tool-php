<?php
/**
 * @author      Betapcode <betapcode@gmail.com>
 * @project		miner api tool
 * @created     17/01/2018
 * @version     1.0.0
 */
require 'vendor/autoload.php';
require_once 'libs/helper.php';

$app = new \Slim\Slim(array("debug" => true));

$app->helper        = new Helper();

$app->get('/', function() use($app) {

    $totalArr = array();

    $ipMiner = "127.0.0.1"; // put ip address
    $arrMiner = $app->helper->getDataInfoMiner($ipMiner);
    $totalArr[] = $arrMiner;
    // Response json 
    // ==========================================================================================
    $app->response->setStatus(200);
    $app->response()->headers->set('Content-Type', 'application/json');
    echo json_encode($totalArr);
}); 

function tt_filter($var)
{
    return($var != 0);
}

$app->run();


