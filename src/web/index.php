<?php
require_once '../../vendor/autoload.php';
require_once '../dispatcher.php';
require_once '../routing.php';
require_once '../controllers.php';

session_start();

$action_url = "/";
if(isset($_GET['action']))
{
    $action_url = $_GET['action'];
}

dispatch($routing, $action_url);