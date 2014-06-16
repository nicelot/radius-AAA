<?php

/*
 * Project     : Radius 
 * Author      : Akhil Lawrence
 * Description : This file holds the attribute value pairs ( dictionary ) used by the radius server
 */
 
/* Configure error reporting */
error_reporting( E_ERROR | E_PARSE );
 
/* List of vendors */
$vendors = array(
    9     => 'Cisco',
    21776 => 'Telcobridges',
    50000 => 'Adenis',
    50001 => 'Crosstel'
);
 
/* Vendor specific attributes */
$vsas = array(
    /* cisco */
    9 => array(
        2   => array('Cisco-NAS-Port', 'String'),
        24  => array('h323-conf-id', 'String'),
        25  => array('h323-setup-time', 'String'),
        26  => array('h323-call-origin', 'String'),
        27  => array('h323-call-type', 'String'),
        28  => array('h323-connect-time', 'String'),
        29  => array('h323-disconnect-time', 'String'),
        30  => array('h323-disconnect-cause', 'String'),
        35  => array('h323-incoming-conf-id', 'String'),
        115 => array('release-source', 'String'),
        141 => array('call-id', 'String'),
    ),
    /* Telcobridges */
    21776 => array(
        9  => array('Telcob-ChargeIndicator', 'String'),
        10 => array('Telcob-Protocol', 'String'),
        11 => array('Telcob-Codec', 'String'),
        12 => array('Telcob-RemoteMediaIP', 'Ipaddress'),
        13 => array('Telcob-RemoteMediaPort', 'Integer'),
        14 => array('Telcob-TrunkName', 'String'),
        15 => array('Telcob-TimeslotNumber', 'Integer'),
        16 => array('Telcob-MediaInfo', 'String'),
        17 => array('Telcob-StartTime', 'String'),
        18 => array('Telcob-ConnectedTime', 'String'),
        19 => array('Telcob-EndTime', 'String'),
        20 => array('Telcob-TerminationCause', 'Integer'),
        21 => array('Telcob-Other-Leg-Id', 'Integer'),
        22 => array('Telcob-TerminationCauseString', 'String'),
        23 => array('Telcob-TerminationSource', 'String'),
        24 => array('Telcob-LocalSipIP', 'Ipaddress'),
        25 => array('Telcob-LocalSipPort', 'Integer'),
        26 => array('Telcob-LocalMediaIP', 'Ipaddress'),
        27 => array('Telcob-LocalMediaPort', 'Integer'),
        28 => array('Telcob-LocalMediaInfo', 'String'),
        29 => array('Telcob-RemoteMediaInfo    ', 'String'),
        30 => array('Telcob-Alert-Time', 'String'),
        31 => array('Telcob-Redirecting-Number', 'String'),
        32 => array('Telcob-Original-Called-Number', 'String'),
        33 => array('Telcob-Calling-Presentation', 'String'),
        34 => array('Telcob-CallingSubscriberNumber', 'String'),
        35 => array('Telcob-OriginalCause', 'String'),
        36 => array('Telcob-CustomCdrValue', 'String'),
        100 => array('Telcob-RtpRxPackets', 'Integer'),
        101 => array('Telcob-RtpRxVoiceBytes', 'Integer'),
        102 => array('Telcob-RtpRxVoiceDuration', 'Integer'),
        103 => array('Telcob-RtpRxMaxPlayoutDelay', 'Integer'),
        120 => array('Telcob-RtpRxErrors', 'Integer'),
        121 => array('Telcob-RtpRxLostPackets', 'Integer'),
        130 => array('Telcob-RtpTxPackets', 'Integer'),
        132 => array('Telcob-RtpTxVoiceDuration', 'Integer'),
        150 => array('Telcob-RtpTxErrors', 'Integer'),
        151 => array('Telcob-RtpTxLostPackets', 'Integer'),
        152 => array('Telcob-RtpTxArpFailure', 'Integer'),
        160 => array('Telcob-RtcpJitter', 'Integer')
    ),
    /* Adenis */
    50000 => array(
        1  => array('Adenis-AVPair', 'String'),
        2  => array('Adenis-NOA', 'String'),
        3  => array('Adenis-OriginalCalledNumber', 'String')
    )
 );

/* Standard Attributes - Dictionary */
$stdas = array(
    1  => array('User-Name', 'String'),
    2  => array('User-Password', 'String'),
    3  => array('CHAP-Password', 'String'),
    4  => array('NAS-IP-Address', 'Ipaddress'),
    5  => array('NAS-Port', 'Integer'),
    6  => array('Service-Type', 'Integer'),
    7  => array('Framed-Protocol', 'Integer'),
    8  => array('Framed-IP-Address', 'Ipaddress'),
    9  => array('Framed-IP-Netmask', 'Ipaddress'),
    10 => array('Framed-Routing', 'Integer'),
    11 => array('Filter-Id', 'String'),
    12 => array('Framed-MTU', 'Integer'),
    13 => array('Framed-Compression', 'Integer'),
    14 => array('Login-IP-Host', 'Ipaddress'),
    15 => array('Login-Service', 'Integer'),
    16 => array('Login-TCP-Port', 'Integer'),
    18 => array('Reply-Message', 'String'),
    19 => array('Callback-Number', 'String'),
    20 => array('Callback-Id', 'String'),
    22 => array('Framed-Route', 'String'),
    23 => array('Framed-IPX-Network', 'Integer'),
    24 => array('State', 'String'),
    25 => array('Class', 'String'),
    26 => array('Vendor-Specific', 'String'),
    27 => array('Session-Timeout', 'Integer'),
    28 => array('Idle-Timeout', 'Integer'),
    29 => array('Termination-Action', 'Integer'),
    30 => array('Called-Station-Id', 'String'),
    31 => array('Calling-Station-Id', 'String'),
    32 => array('NAS-Identifier', 'String'),
    33 => array('Proxy-State', 'String'),
    34 => array('Login-LAT-Service', 'String'),
    35 => array('Login-LAT-Node', 'String'),
    36 => array('Login-LAT-Group', 'String'),
    37 => array('Framed-AppleTalk-Link', 'Integer'),
    38 => array('Framed-AppleTalk-Network', 'Integer'),
    39 => array('Framed-AppleTalk-Zone', 'String'),
    40 => array('Acct-Status-Type', 'Integer'),
    41 => array('Acct-Delay-Time', 'Integer'),
    42 => array('Acct-Input-Octets', 'Integer'),
    43 => array('Acct-Output-Octets', 'Integer'),
    44 => array('Acct-Session-Id', 'String'),
    45 => array('Acct-Authentic', 'Integer'),
    46 => array('Acct-Session-Time', 'Integer'),
    47 => array('Acct-Input-Packets', 'Integer'),
    48 => array('Acct-Output-Packets', 'Integer'),
    49 => array('Acct-Terminate-Cause', 'Integer'),
    50 => array('Acct-Multi-Session-Id', 'String'),
    51 => array('Acct-Link-Count', 'Integer'),
    60 => array('CHAP-Challenge', 'String'),
    61 => array('NAS-Port-Type', 'Integer'),
    62 => array('Port-Limit', 'Integer'),
    63 => array('Login-LAT-Port', 'String')    
);
 
/* 
 * Method getVsaInfo
 * returns the array containing attribute assignment and data type
 */
function getVsaInfo( $vendorId, $vendorType ) {
    global $vendors;
    global $vsas;
    return array(
        'vendor'       => $vendors[$vendorId],
        'assignment'   => $vsas[$vendorId][$vendorType][0],
        'unpackformat' => $vsas[$vendorId][$vendorType][1]
    );
}
   
/* 
 * Method getStdaInfo
 * returns the array containing attribute assignment and data type
 */
function getStdaInfo($type){
    global $stdas;
    return array(
        'assignment'   => $stdas[$type][0],
        'unpackformat' => $stdas[$type][1]
    );
}
 
?>
