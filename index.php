<?php
error_reporting(-1); 
require_once 'controller.php';

$controler = new MainController();
$action = $_GET['action'] ?? '';
$controler->StartAction($action);