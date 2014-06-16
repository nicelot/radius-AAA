<?php

/*
 * Project     : Radius 
 * Author      : Akhil Lawrence
 * Description : This file holds utility functions used by the radius server
 */
 
/* Configure error reporting */
error_reporting( E_ERROR | E_PARSE );

/* 
 * Method unpackInteger
 * returns integer ( fixes integer pack/unpack issue due to php platform dependency )
 */
function unpackInteger( $bin ) { 

    if ( PHP_INT_SIZE <= 4 ) {

        list( , $h, $l ) = unpack( 'n*', $bin );
        return ( $l + ( $h * 0x010000 ) ); 

    } else {

        list( , $int ) = unpack( 'N' , $bin ); 
        return $int; 

    } 

}

?>
