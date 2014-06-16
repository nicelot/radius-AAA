<?php

/*
 * Project     : Radius 
 * Author      : Akhil Lawrence
 * Description : This file holds response codes used by the radius server
 */
 
/* Configure error reporting */
error_reporting( E_ERROR | E_PARSE );

$responseCodes = array(
    /* TDM responses */
    '16' => 'Ok',
    '17' => 'Busy Here',
    '34' => 'Service Unavailable',
    
    /* 1xx—Provisional Responses */
    '100' => 'Trying',
    '180' => 'Ringing',
    '181' => 'Calls is being forwarded',
    '182' => 'Queued',
    '183' => 'Session in progress',
    '199' => 'Early dialog terminated',

    /* 2xx—Successful Responses */
    '200' => 'Ok',
    '202' => 'Accepted',
    '204' => 'No notification',

    /* 3xx—Redirection Responses */
    '300' => 'Multiple choices',
    '301' => 'Moved permanently',
    '302' => 'Moved Temporarily',
    '305' => 'Use proxy',
    '380' => 'Alternative service',

    /* 4xx—Client Failure Responses */ 
    '400' => 'Bad request',
    '401' => 'Unauthorized',
    '402' => 'Payment Required',
    '403' => 'Forbidden',
    '404' => 'Not Found',
    '405' => 'Method Not Allowed',
    '406' => 'Not Acceptable',
    '407' => 'Proxy Authentication Required',
    '408' => 'Request Timeout',
    '409' => 'Conflict',
    '410' => 'Gone',
    '411' => 'Length Required',
    '412' => 'Conditional Request Failed',
    '413' => 'Request Entity Too Large',
    '414' => 'Request-URl Too Long',
    '415' => 'Unsupported Media Type',    
    '416' => 'Unsupported URl Scheme',
    '417' => 'Unknown Resource-Priority',
    '420' => 'Bad Extension',
    '421' => 'Extension Required',
    '422' => 'Session Interval Too Small',
    '423' => 'Interval Too Brief',
    '424' => 'Bad Location Information',
    '428' => 'Use Identity Header',
    '429' => 'Provide Referrer Identity',
    '430' => 'Flow Failed',
    '433' => 'Anonymity Disallowed',
    '436' => 'Bad Identity-Info',
    '437' => 'Unsupported Certificate',
    '438' => 'Invalid Identity Header',
    '439' => 'First Hop Lacks Outbound Support',
    '470' => 'Consent Needed',
    '480' => 'Temporarily Unavailable',
    '481' => 'Call/Transaction Does Not Exist',
    '482' => 'Loop Detected',
    '483' => 'Too Many Hops',
    '484' => 'Address Incomplete',
    '485' => 'Ambiguous',
    '486' => 'Busy Here',
    '487' => 'Request Terminated',
    '488' => 'Not Acceptable Here',
    '489' => 'Bad Event',
    '491' => 'Request Pending',
    '493' => 'Undecipherable',
    '494' => 'Security Agreement Required',

    /* 5xx—Server Failure Responses */
    '500' => 'Server Internal Error',
    '501' => 'Not Implemented',
    '502' => 'Bad Gateway',
    '503' => 'Service Unavailable',
    '504' => 'Server Time-out',
    '505' => 'Version Not Supported',
    '513' => 'Message Too Large',
    '580' => 'Precondition Failure',

    /* 6xx—Global Failure Responses */
    '600' => 'Busy Everywhere',
    '603' => 'Decline',
    '604' => 'Does Not Exist Anywhere',
    '606' => 'Not Acceptable',
);

$mappedResponseCodes = array(
    /* TDM responses */
    '16' => '42',
    '17' => '44',
    '34' => '50',

    /* 1xx—Provisional Responses */
    '100' => '40',
    '180' => '40',
    '181' => '40',
    '182' => '40',
    '183' => '40',
    '199' => '40',

    /* 2xx—Successful Responses */
    '200' => '42',
    '202' => '40',
    '204' => '40',

    /* 3xx—Redirection Responses */
    '300' => '40',
    '301' => '40',
    '302' => '40',
    '305' => '40',
    '380' => '40',

    /* 4xx—Client Failure Responses */ 
    '400' => '2',
    '401' => '2',
    '402' => '2',
    '403' => '2',
    '404' => '2',
    '405' => '2',
    '406' => '2',
    '407' => '2',
    '408' => '2',
    '409' => '2',
    '410' => '2',
    '411' => '2',
    '412' => '2',
    '413' => '2',
    '414' => '2',
    '415' => '2',    
    '416' => '2',
    '417' => '2',
    '420' => '2',
    '421' => '2',
    '422' => '2',
    '423' => '2',
    '424' => '2',
    '428' => '2',
    '429' => '2',
    '430' => '2',
    '433' => '2',
    '436' => '2',
    '437' => '2',
    '438' => '2',
    '439' => '2',
    '470' => '2',
    '480' => '2',
    '481' => '2',
    '482' => '2',
    '483' => '2',
    '484' => '2',
    '485' => '2',
    '486' => '2',
    '487' => '2',
    '488' => '2',
    '489' => '2',
    '491' => '2',
    '493' => '2',
    '494' => '2',

    /* 5xx—Server Failure Responses */
    '500' => '2',
    '501' => '2',
    '502' => '2',
    '503' => '2',
    '504' => '2',
    '505' => '2',
    '513' => '2',
    '580' => '2',

    /* 6xx—Global Failure Responses */
    '600' => '2',
    '603' => '2',
    '604' => '2',
    '606' => '2',
);

/* 
 * Method getMappedResponse
 * returns Integer
 */
function getMappedResponse( $code ) {

    global $mappedResponseCodes;
    return $mappedResponseCodes[$code];

}

?>
