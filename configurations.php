<?php

/*
 * Project     : Radius 
 * Author      : Akhil Lawrence
 * Description : This file holds all the configurations related with the radius server
 */
 
/* Configure error reporting */
error_reporting( E_ERROR | E_PARSE );
 
/* Installation path */
$rootPath = dirname( __FILE__ );

/* Server settings */
$authHost = "127.0.0.1";
$authPort = 1645;
$acctPort = 1646;
$authKey  = "secret key";
$timeZone = "America/Denver";
 
/* Socket Time out settings */ 
$socketTimeoutSeconds      = 1;
$socketTimeoutMicroSeconds = 0; 

/* No.of childs or forks to be made */
$noOfChilds = 9;

/* General application settings */  
$clientName                = "radius";
$rotateFileEvery           = 300; /* seconds */
$tempFilesExtension        = "csv";

/* Log settings */
$pathToCallLog     = $rootPath . "/logs/calls/";
$pathToEventLog    = $rootPath . "/logs/event/";
$logFilesExtension = "txt";

/* Apply configurations */
date_default_timezone_set( $timeZone );

?>
