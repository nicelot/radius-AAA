<?php

/*
* Project     : Radius 
* Author      : Akhil Lawrence
* Description : Radius authentication server
*/

/* Configure error reporting */
error_reporting( E_ERROR | E_PARSE );
 
/* Import required files */
require "configurations.php";
require "dictionary.php";
require "functions.php";

/* Create socket for authentication */
if ( ( $authSocket = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP ) ) === false ) {

    $errorcode = socket_last_error();
    $errormsg  = socket_strerror( $errorcode );
    die("Couldn't create authentication socket: [$errorcode] $errormsg \r\n");

}

/* Bind socket for authentication */
if ( socket_bind( $authSocket, $authHost, $authPort ) === false ) {

    $errorcode = socket_last_error();
    $errormsg  = socket_strerror($errorcode);     
    die("Couldn't bind authentication socket: [$errorcode] $errormsg \r\n");

}

/* Set socket timeout options */
socket_set_option($authSocket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $socketTimeoutSeconds, 'usec' => $socketTimeoutMicroSeconds));

/* Show startup message */
echo "RADIUS authentication server started in {$authHost}:{$authPort}....\r\n";

while( $noOfChilds > 0 ) {

    /* Fork server */
    $pid = pcntl_fork();
     
    if ( $pid == 0 ) {

        try {

            $pid = getmypid();
            $shutdown = false;        /* Flag for graceful shutdown */
            $rotatefiles = true;      /* Flag for rotating files */
            $callCounter = 0;         /* No. of calls handled */
            $refTime = 0;
     
            /* Infinite loop for handling requests */
            while( 1 ) {

                /* Set event log flag */ 
                if( file_exists( $eventLogFlag  ) ) {

                    $writeEventLog = true;

                } else {

                    $writeEventLog = false;

                }

                /* Set call log flag */ 
                if ( file_exists( $callLogFlag ) ) {

                    $writeCallLog = true;

                } else {

                    $writeCallLog = false;

                }

                /* Source file missing */ 
                $fileInfo = pathinfo( __FILE__ );
                if ( !file_exists( $fileInfo['basename'] ) ) {

                    fwrite( $eventlog, "[Time : " . strftime( "%Y-%m-%d %H:%M:%S" ) . ", Pid : " . $pid . "] Graceful shutdown acknowledged \r\n" );
                    $shutdown = true;

                }

                /* Graceful shutdown */
                if ( $shutdown ) {

                    fwrite( $eventlog, "[Time : " . strftime( "%Y-%m-%d %H:%M:%S" ) . ", Pid : " . $pid . "] Exiting infinite call handling loop \r\n" );
                    break;

                }

                /* Rotate files on exceeding timelimit */
                $currentTime = time();
                if ( ( $currentTime - $refTime ) >= $rotateFileEvery ) {

                    $refTime = $currentTime;
                    $rotatefiles = true;

                }

                /* Rotate files */
                if ( $rotatefiles ) {

                    /* Close open files */
                    fclose( $calllog );
                    fclose( $eventlog );

                    /* Rename existing files */
                    rename(
                        $pathToCallLog . "_AUTH.CALLLOG." . $clientName . "-" . $pid . "." . $time . "." . $logFilesExtension,
                        $pathToCallLog . "AUTH.CALLLOG." . $clientName . "-" . $pid . "." . $time . "." . $logFilesExtension
                    );
                    rename(
                        $pathToEventLog . "_AUTH.EVENTLOG." . $clientName . "-" . $pid . "." . $time . "." . $logFilesExtension,
                        $pathToEventLog . "AUTH.EVENTLOG." . $clientName . "-" . $pid . "." . $time . "." . $logFilesExtension
                    );
        
                    /* Create new files */        
                    $time = strftime( "%Y%m%d%H%M%S" );
                    $calllog = fopen( $pathToCallLog . "_AUTH.CALLLOG." . $clientName . "-" . $pid . "." . $time . "." . $logFilesExtension, "a+");
                    $eventlog = fopen( $pathToEventLog . "_AUTH.EVENTLOG." . $clientName . "-" . $pid . "." . $time . "." . $logFilesExtension, "a+");                         
                
                    /* Reset rotate file flag and reference time */
                    $rotatefiles = false;
                    $refTime = time();
                }
             
                /* Expected attributes */
                $expectedAttributes = array(
                    'Calling-Station-Id' => '',   /* A number */
                    'Called-Station-Id' => '',    /* B number */
                    'Crosstel-Called-NOA' => '',  /* 3 for national calls & 4 for international calls */
                    'h323-conf-id' => '',         /* unique session id */
                    'User-Name' => ''             /* incoming trunk id */
                );

                /* Read from socket */
                $authBuffer = null;
                socket_recvfrom( $authSocket, $authBuffer, 4096, 0, $authHost, $authPort );

                /* Parse Request */
                $request = null;
                $request = unpack( 'Ccode/Cidentifier/nlength', $authBuffer );
                $request['authenticator'] = substr( $authBuffer, 4, 16 );
                $request['attributes'] = substr( $authBuffer, 20 );
    
                /* Access-Request */
                if ( $request['code'] == 1 ) {

      
                    /* Extract attributes */
                    while( strlen( $request['attributes'] ) > 0 ) {

                        $attribute = $request['attributes'];
                     
                        /*Attribute info */
                        $attributeInfo = array(
                            'type' => '',          /* vsa or std */
                            'vendor' => '',        /* vendor name, if the attribute is vsa */
                            'assignment' => '',    /* name of the attribute */
                            'value' => '',         /* value of the attribute */
                        );
            
                        /* Attribute type */
                        $type = null;
                        $type = unpack( 'Ctype', $attribute );
                        $type = $type['type'];
                     
                        /* Extract vsa */
                        if ( $type == 26 ) {

                            $vsa = null;
                            $vsa = unpack( 'Ctype/Clength/NvendorId/CvendorType/CvendorLength', $attribute );
                
                            /* Fetch data type */
                            $vsaInfo = getVsaInfo( $vsa['vendorId'], $vsa['vendorType'] );
                                
                            /* Unpack string data */
                            if ( $vsaInfo['unpackformat'] == 'String' ) {

                                $vsaInfo['value'] = unpack( 'a*', substr( $attribute, 8, ($vsa['length'] - 8) ) );
                                $vsaInfo['value'] = $vsaInfo['value'][1];

                            }
                                
                            /* Unpack integer data */
                            if ( $vsaInfo['unpackformat'] == 'Integer' ) {

                                $vsaInfo['value'] = unpackInteger( substr( $attribute, 8, ( $vsa['length'] - 8) ) );

                            }
                
                            /* Unpack ipaddress */
                            if ( $vsaInfo['unpackformat'] == 'Ipaddress' ) {

                                $vsaInfo['value'] = inet_ntop( substr( $attribute, 8, ($vsa['length'] - 8) ) );

                            }
                
                            /* Delete data read */
                            $attribute = substr( $attribute, $vsa['length'] );
                            $request['attributes'] = $attribute;
                
                            /* Collect attribute info */
                            $attributeInfo['type'] = 'vsa';
                            $attributeInfo['vendor'] = $vsaInfo['vendor'];
                            $attributeInfo['assignment'] = $vsaInfo['assignment'];
                            $attributeInfo['value'] = $vsaInfo['value'];
                        }    
                     
                        /* Extract std */
                        else {

                            $std = unpack( 'Ctype/Clength' , $attribute);
                
                            /* Fetch data type */
                            $stdInfo = getStdaInfo( $std['type'] );
                
                            /* Unpack string data */
                            if ( $stdInfo['unpackformat'] == 'String' ) {

                                $stdInfo['value'] = unpack( 'a*value', substr($attribute, 2, ($std['length'] - 2) ) );
                                $stdInfo['value'] = $stdInfo['value']['value'];

                            }
                
                            /* Unpack integer data */
                            if ( $stdInfo['unpackformat'] == 'Integer' ) {

                                $stdInfo['value'] = unpackInteger( substr( $attribute, 2, ($std['length'] - 2) ) );

                            }
                
                            /* Unpack ipaddress */
                            if( $stdInfo['unpackformat'] == 'Ipaddress' ) {

                                $stdInfo['value'] = inet_ntop( substr( $attribute, 2, ($std['length'] - 2) ) );

                            }
                
                            /* Delete data read */
                            $attribute = substr( $attribute, $std['length'] );
                            $request['attributes'] = $attribute;
                
                            /* Collect attribute info */
                            $attributeInfo['type'] = 'std';
                            $attributeInfo['assignment'] = $stdInfo['assignment'];
                            $attributeInfo['value'] = $stdInfo['value'];

                        }
                                  
                        /* Save all expected attributes present in request */
                        if ( array_key_exists( $attributeInfo['assignment'], $expectedAttributes ) ) {

                            $expectedAttributes[$attributeInfo['assignment']] = trim( $attributeInfo['value'] );

                        }

                    }

                    /* Write call log */
                    if ( $writeCallLog ) {

                        fwrite($calllog, "\r\n\r\n----- Incoming Authentication Request [" . strftime( "%Y-%m-%d %H:%M:%S" ) . "] -----\r\n\r\n");
                        fwrite($calllog, "h323-conf-id : " . $expectedAttributes["h323-conf-id"] . "\r\n");   
                        fwrite($calllog, "User-Name : " . $expectedAttributes["User-Name"] . "\r\n");
                        fwrite($calllog, "Calling-Station-Id : " . $expectedAttributes["Calling-Station-Id"] . "\r\n");
                        fwrite($calllog, "Called-Station-Id : " . $expectedAttributes["Called-Station-Id"] . "\r\n");

                    }

                    /* Do what you want to do with the attributes */

                    
                    /* Generate response with desired attributes */
                    
                    
                    /* Add attribute : h323-conf-id */      
                    $value  = $expectedAttributes['h323-conf-id'];
                    $length = strlen( $value );
                    $attributes  = pack( 'CCNCC', 26, ($length + 8), 9, 24, ($length + 2) );
                    $attributes .= pack( 'a*', $value ); 
                  
                    /* Add attribute : User-Name */      
                    $value  = $expectedAttributes['User-Name'];
                    $length = strlen( $value );
                    $attributes .= pack( 'CC', 1, ($length + 2) );
                    $attributes .= pack( 'a*', $value );
                  
                    /* Add attribute : Called-Station-Id */      
                    $value  = $expectedAttributes['Called-Station-Id'];
                    $length = strlen( $value );
                    $attributes .= pack( 'CC', 30, ($length + 2) );
                    $attributes .= pack( 'a*', $value );
                  
                    /* Add attribute : Calling-Station-Id */      
                    $value  = $expectedAttributes['Calling-Station-Id'];
                    $length = strlen( $value );
                    $attributes .= pack( 'CC', 31, ($length + 2) );
                    $attributes .= pack( 'a*', $value );
                                          
                    /* Prepare response header with code Access-Accept */
                    $header = pack( 'CCn', 2, $request['identifier'], (strlen($attributes) + 20) );
                                    
                    /* Prepare response body */      
                    $response = md5( $header . $request['authenticator'] . $attributes . $authKey );
                    $response = pack('H*', $response);
                  
                    /* Prepare response packet */
                    $response = $header . $response . $attributes;
                  
                    /* Send response */
                    socket_sendto($authSocket, $response, strlen( $response ), 0, $authHost, $authPort);

                    /* Write call log */
                    if ( $writeCallLog ) {

                        fwrite($calllog, "\r\n\r\n----- Response to Authentication Request [" . strftime( "%Y-%m-%d %H:%M:%S" ) . "] -----\r\n\r\n");
                        fwrite($calllog, "h323-conf-id : ".$expectedAttributes["h323-conf-id"]."\r\n");   
                        fwrite($calllog, "User-Name : ".$expectedAttributes["User-Name"]."\r\n");

                    }

                }    

            }
            
            /* close and rename files in use */
            fclose( $calllog );
            fclose( $eventlog );

            rename(
                $pathToCallLog . "_AUTH.CALLLOG." . $clientName . "-" . $pid . "." . $time . "." . $logFilesExtension,
                $pathToCallLog . "AUTH.CALLLOG." . $clientName . "-" . $pid . "." . $time . "." . $logFilesExtension
            );
            rename(
                $pathToEventLog . "_AUTH.EVENTLOG." . $clientName . "-" . $pid . "." . $time . "." . $logFilesExtension,
                $pathToEventLog . "AUTH.EVENTLOG." . $clientName . "-" . $pid . "." . $time . "." . $logFilesExtension
            );            
         
            /* Kill child on graceful shutdown */
            posix_kill($pid, 9);

        } catch( Exception $e ) {

            fwrite( $eventlog, "[Time : " . strftime( "%Y-%m-%d %H:%M:%S" ) . ", Pid : " . $pid . "] Exception occured, " . $e->getMessage() . " \r\n" );

        }

    }

    $noOfChilds--;

}

/* wait untill all childs exit */
pcntl_wait( $status );

/* close sockets used */  
socket_close( $authSocket );

?>
