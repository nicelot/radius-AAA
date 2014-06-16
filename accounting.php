<?php

/*
* Project     : Radius 
* Author      : Akhil Lawrence
* Description : Radius accounting server
*/

/* Configure error reporting */
error_reporting( E_ERROR | E_PARSE );
 
/* Import required files */
require "configurations.php";
require "dictionary.php";
require "functions.php";
require "responsecodes.php";
 
/* Create socket for Accounting */
if ( ($acctSocket = socket_create( AF_INET, SOCK_DGRAM, SOL_UDP ) ) === false ) { 
	
    $errorcode = socket_last_error();
    $errormsg  = socket_strerror($errorcode);     
    die("Couldn't create accounting socket: [$errorcode] $errormsg \r\n");	
    
}

/* Bind socket for Accounting */
if ( socket_bind( $acctSocket, $authHost, $acctPort ) === false ) { 
	
    $errorcode = socket_last_error();
    $errormsg  = socket_strerror($errorcode);     
    die("Couldn't bind accounting socket: [$errorcode] $errormsg \r\n");
    	
}
 
/* Set socket timeout options */
socket_set_option( $acctSocket, SOL_SOCKET, SO_RCVTIMEO, array( 'sec' => $socketTimeoutSeconds, 'usec' => $socketTimeoutMicroSeconds ) );

/* Show startup message */
echo "RADIUS accounting server started in {$authHost}:{$acctPort}....\r\n";

while( $noOfChilds > 0 ) {
	
    /* Fork server */
    $pid = pcntl_fork();
	 
    if ( $pid == 0 ) {
		
	    try {	
		
            $pid = getmypid();
            $shutdown = false;    /* graceful shutdown */
            $rotatefiles = true;    /* for temp1 files */
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

                    fwrite($eventlog, "[Time : " . strftime( "%Y-%m-%d %H:%M:%S" ) . ", Pid : " . $pid . "] Graceful shutdown acknowledged \r\n");
                    $shutdown = true;

                }

                /* Graceful shutdown */
                if ( $shutdown ) {
				
				    fwrite($eventlog, "[Time : " . strftime( "%Y-%m-%d %H:%M:%S" ) . ", Pid : " . $pid . "] Exiting infinite call handling loop.\r\n");
                    break;
                
                }

                /* Rotate files on exceeding time limit */
                $currentTime = time();
                if ( ($currentTime - $refTime) >= $rotateFileEvery ) {

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
                        $pathToCallLog . "_ACCT.CALLLOG." . $clientName . "-" . $pid . "." . $time . "." . $logFilesExtension,
                        $pathToCallLog . "ACCT.CALLLOG." . $clientName . "-" . $pid . "." . $time . "." . $logFilesExtension
                    );
                    rename(
                        $pathToEventLog . "_ACCT.EVENTLOG." . $clientName . "-" . $pid . "." . $time . "." . $logFilesExtension,
                        $pathToEventLog . "ACCT.EVENTLOG." . $clientName . "-" . $pid . "." . $time . "." . $logFilesExtension
                    );

                    /* Create new files */
                    $time = strftime("%Y%m%d%H%M%S");
                    $calllog = fopen( $pathToCallLog . "_ACCT.CALLLOG." . $clientName . "-" . $pid . "." . $time . "." . $logFilesExtension, "a+");
                    $eventlog = fopen( $pathToEventLog . "_ACCT.EVENTLOG." . $clientName . "-" . $pid . "." . $time . "." . $logFilesExtension, "a+");
                
                    /* Reset rotate file flag and reference time */
                    $rotatefiles = false;
                    $refTime = time();
                }
			 
                /* Expected attributes */
                $expectedAttributes = array(
                    'Acct-Status-Type' => '',       /* 1 => start and  2=>stop */
                    'h323-conf-id' => '',           /* h323-conf-id used for authenticating call */
                    'h323-setup-time' => '',        /* connect time ( setup ) */
                    'h323-disconnect-time' => '',   /* disconnect time */
                    'Acct-Session-Time' => '',      /* duration of call in seconds */
                    'Cisco-NAS-Port' => '',         /* outgoing trunk id */
                    'User-Name' => '',              /* incoming trunk id */
                    'h323-disconnect-cause' => '',	/* Termination Reason */
                );
	
                /* Read from socket */
                $acctBuffer = null;
                socket_recvfrom( $acctSocket, $acctBuffer, 4096, 0, $authHost, $acctPort );

                /* Parse Request */
                $request = null;
                $request = unpack( 'Ccode/Cidentifier/nlength', $acctBuffer );
                $request['authenticator'] = substr( $acctBuffer, 4, 16 );
                $request['attributes'] = substr( $acctBuffer, 20 );

                /* Accounting request */
                if ( $request['code'] == 4 ) {

                    /* Increment cdr counter */
                    $statCounter['acctrequests']++;
                    $callCounter++;
	 	 
                    /* Extract attributes */
                    while( strlen($request['attributes']) > 0 ){
					
                        $attribute = $request['attributes'];
						 
                        /* Attribute info */
                        $attributeInfo = array(
                            'type' => '', /* vsa or std */
                            'vendor' => '', /* vendor name, if the attribute is vsa */
                            'assignment' => '', /* name of the attribute */
                            'value'      => '', /* value of the attribute */
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
							
                                $vsaInfo['value'] = unpackInteger( substr( $attribute, 8, ($vsa['length'] - 8) ) );
                            
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
						
                            $std = unpack( 'Ctype/Clength', $attribute );
				
                            /* Fetch data type */
                            $stdInfo = getStdaInfo( $std['type'] );
				
                            /* Unpack string data */
                            if ( $stdInfo['unpackformat'] == 'String' ) {
							
                                $stdInfo['value'] = unpack('a*value', substr( $attribute, 2, ($std['length'] - 2) ) );
                                $stdInfo['value'] = $stdInfo['value']['value'];
                            
                            }
				
                            /* Unpack integer data */
                            if( $stdInfo['unpackformat'] == 'Integer' ) {
							
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
                        if ( array_key_exists($attributeInfo['assignment'], $expectedAttributes ) ) {    
						
                            $expectedAttributes[$attributeInfo['assignment']] = trim( $attributeInfo['value'] );
                        
                        }	

                    }
                
                    /* Process only Accounting Request with status stop */
                    if ( $expectedAttributes['Acct-Status-Type'] == 2 ) {
                        
                        /* Decode 128bit integer */
                        $expectedAttributes['h323-conf-id'] = str_replace( " ", "",  $expectedAttributes['h323-conf-id'] ); 
                        $expectedAttributes['h323-conf-id'] = rtrim( $expectedAttributes['h323-conf-id'], "0" );
                        $expectedAttributes['h323-conf-id'] = gmp_strval( gmp_init( $expectedAttributes['h323-conf-id'], 16 ) );
						
                        /* H323-setup-time */
                        $connect_time = DateTime::createFromFormat( 'H:i:s.u O D M d Y', $expectedAttributes['h323-setup-time'] );
                        $connect_time->setTimeZone(  new DateTimeZone( $timeZone ) ); 
                        $connect_time = $connect_time->format( 'Y-m-d H:i:s.u' );
				
                        /* H323-disconnect-time */
                        $disconnect_time = DateTime::createFromFormat( 'H:i:s.u O D M d Y', $expectedAttributes['h323-disconnect-time'] );
                        $disconnect_time->setTimeZone(  new DateTimeZone( $timeZone ) ); 
                        $disconnect_time = $disconnect_time->format( 'Y-m-d H:i:s.u' );
						
                        /* Call status id */
                        $pattern = "/(\()(\d{1,5})(\))/";
                        preg_match_all($pattern, $expectedAttributes['h323-disconnect-cause'], $matches);
                        $callStatusId = getMappedResponse( $matches[2][0] );

                        /* Write call log */
                        if ( $writeCallLog ) {     
					
                            fwrite($calllog, "\r\n\r\n----- Incoming Accounting request [" . strftime( "%Y-%m-%d %H:%M:%S" ) . "] -----\r\n\r\n");
                            fwrite($calllog, "h323-conf-id : ".$expectedAttributes['h323-conf-id']." \r\n");
                            fwrite($calllog, "h323-setup-time : ".$expectedAttributes['h323-setup-time']." \r\n");
                            fwrite($calllog, "h323-disconnect-time : ".$expectedAttributes['h323-disconnect-time']." \r\n");
                            fwrite($calllog, "Acct-Session-Time : ".$expectedAttributes['Acct-Session-Time']." \r\n");
                            fwrite($calllog, "Cisco-NAS-Port : ".$expectedAttributes['Cisco-NAS-Port']." \r\n");
                            fwrite($calllog, "User-Name : ".$expectedAttributes['User-Name']." \r\n");
                            fwrite($calllog, "h323-disconnect-cause : ".$expectedAttributes['h323-disconnect-cause']." \r\n\r\n");
                    
                        }
                        
                        /* Write acct requests to file */	
                        fwrite( $acct, $expectedAttributes['Acct-Status-Type'].",".$expectedAttributes['Telcob-Other-Leg-Id'].",".$connect_time.",".$disconnect_time.",".$expectedAttributes['Acct-Session-Time'].",".$expectedAttributes['Cisco-NAS-Port'].",".$expectedAttributes['User-Name'].",".$expectedAttributes['h323-disconnect-cause'].",".$expectedAttributes['h323-disconnect-cause'].",".$callStatusId.",".$switchCode."\n" );
                    
                    }
                    
                    /* Send ACK */
                    $attributes = '';				

                    /* Prepare response header with code Accounting-Response */
                    $header = pack( 'CCn', 5, $request['identifier'], (strlen($attributes) + 20) );
				  
                    /* Prepare response body */	  
                    $response = md5( $header . $request['authenticator'] . $attributes . $authKey );
                    $response = pack( 'H*', $response );
				  
                    /* Prepare response packet */
                    $response = $header . $response . $attributes;
				  
                    /* Send response */
                    socket_sendto( $acctSocket, $response, strlen($response), 0, $authHost, $acctPort ); 
                                    
                }

            }	
	
            /* Close open files */
            fclose( $calllog );
            fclose( $eventlog );
                
            /* Rename existing files */
            rename(
                $pathToCallLog . "_ACCT.CALLLOG." . $clientName . "-" . $pid . "." . $time . "." . $logFilesExtension,
                $pathToCallLog . "ACCT.CALLLOG." . $clientName . "-" . $pid . "." . $time . "." . $logFilesExtension
            );
            rename(
                $pathToEventLog . "_ACCT.EVENTLOG." . $clientName . "-" . $pid . "." . $time . "." . $logFilesExtension,
                $pathToEventLog . "ACCT.EVENTLOG." . $clientName . "-" . $pid . "." . $time . "." . $logFilesExtension
            );
	 
            /* Kill child on graceful shutdown */
            posix_kill( $pid, 9 );
        
        } catch( Exception $e ) {

            fwrite( $eventlog, "[Time : " . strftime( "%Y-%m-%d %H:%M:%S" ) . ", Pid : " . $pid . "] Exception occured, " . $e->getMessage() . " \r\n" );

        }
        
    }

    $noOfChilds--;
    
}

/* Wait untill all childs exit */
pcntl_wait( $status );

/* Close sockets used */  
socket_close( $acctSocket );

?>
