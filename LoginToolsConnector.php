<?php

/* 	================================================================= 
		LoginToolsConnector 

		LoginToolsConnector is a PHP class designed to interface with
		the UI Login Tools (http://login.uiowa.edu). UI Login Tools is
		an online service providing Hawk ID authentication to any
		random web application. The general back and forth between an
		application, let's call it Killer, and UI Login Tools is as follows:
		
		1. A User goes to Killer's login script.
		2. Killer see's that the user does not have a login ticket, and
		redirects the user to the UI Login Tools login page and sends 
		UI Login Tools the url to Killer's login script.
		3. The user logs in with Hawk id and hawk id password.
		4. UI Login Tools redirects the user back to Killer's login script
		along with a "ticket", which is a random string redeemable for a hawk id.
		5. Killer sees the ticket and submits the ticket to UI Login Tools.
		6. UI Login Tools sends back the user's Hawk ID.
		
		The typical structure for a login script would look like this:
		
		require_once( "LoginToolsConnector.php" );
		$loginTools = new LoginToolsConnector( "http://killer.app.com/Login.php" );
		
		if( !array_key_exists( LT_TICKET_KEY, $_REQUEST ) ) {
			header( "Location: " . $loginTools->loginString() );
			
		} else {
		
			if( $loginTools->checkTicket( $_REQUEST[ LT_TICKET_KEY ] ) ) {
			
				echo( "Hello " . $loginTools->hawkid() . "<br>" );
				echo( "You have successfully logged in with your Hawk id and hawk id password. <br>" );
				ob_flush();
			} else {
				// handle error
			}
		}
		
		Changelog
		- Created by dcrall (1/28/04)

	================================================================== */

// Login Tools URL
define( "LT_URL", "login.uiowa.edu/uip/" );

// Login Tools' pages
define( "LT_LOGIN", "login.page?" );
define( "LT_CHECK_TICKET", "checkticket.page?" );
define( "LT_LOGOUT", "logout.page?" );

// Login Tools' keys
define( "LT_TICKET_KEY", "uip_ticket" );
define( "LT_SERVICE_KEY", "service" );
define( "LT_HAWKID_KEY", "hawkid" );
define( "LT_ERROR_KEY", "error" );


class LoginToolsConnector {

    var $connector;
    function LoginToolsConnector( $service ) {

        $this->connector = array();
        $this->connector[ 'service' ] = $service;

    }

    function loginString() {
        return "http://" . LT_URL . LT_LOGIN . LT_SERVICE_KEY . "=" . $this->service();
    }

    function logoutString() {
        return "http://" . LT_URL . LT_LOGOUT . LT_SERVICE_KEY . "=" . $this->service();
    }

    function checkTicket( $ticket ) {

        if( $ticket == null ) return false;

        $this->setTicket( $ticket );
        $response = $this->submitTicket();
        $data = $this->parseResponse( $response );
        //print $response;
        if( array_key_exists( 'error', $data ) ) {
            $this->setError( $data[ 'error' ] );
            return false;

        } elseif( array_key_exists( 'hawkid', $data ) ) {
            $this->setHawkid( $data[ 'hawkid' ] );
            $this->setUID($data['uid']);
            return true;
        }
    }



    function checkTicketString() {
        return "http://" . LT_URL . LT_CHECK_TICKET . LT_TICKET_KEY . "=" . $this->ticket();
    }

    function submitTicket() {
        $handle = fopen( $this->checkTicketString(), "rb" );
        $contents = "";

        do {

            $data = fread( $handle, 8192 );
            if( strlen( $data ) == 0 ) {
                break;
            }

            $contents .= $data;
        } while( true );
        fclose( $handle );
        return $contents;

    }

    function parseResponse( $response ) {

        $result = array();
        // Jack thinks the expression should be \r\n
        // expression is splitting "Invalid Ticket"
        $responseInLines = preg_split( "/[\s\n]+/", $response );

        foreach( $responseInLines as $line ) {
            $temp = preg_split( "/[=]/", $line );
            $result[ $temp[0] ] = $temp[1];
        }

        return $result;
    }

    function service() {
        return $this->valueForKey( 'service' );
    }

    function setService( $service ) {
        $this->takeValueForKey( $service, 'service' );
    }

    function ticket() {
        return $this->valueForKey( 'ticket' );
    }

    function setTicket( $ticket ) {
        $this->takeValueForKey( $ticket, 'ticket' );
    }

    function hawkid() {
        return $this->valueForKey( 'hawkid' );
    }

    function setHawkid( $hawkid ) {
        $this->takeValueForKey( $hawkid, 'hawkid' );

    }
    function setUID( $uid ) {
        $this->takeValueForKey( $uid, 'uid' );
    }
    function uid() {
        return $this->valueForKey( 'uid');
    }
    function error() {
        return $this->valueForKey( 'error' );
    }

    function setError( $error ) {
        $this->takeValueForKey( $error, 'error' );
    }

    function valueForKey( $key ) {
        if( array_key_exists(  $key, $this->connector ) ) {
            return $this->connector[ $key ];
        }
        return null;
    }

    function takeValueForKey( $value, $key ) {
        if( $value != null ) {
            $this->connector[ $key ] = $value;
        }
    }

}

?>