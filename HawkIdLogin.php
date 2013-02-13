<?php
require_once 'Header.php';
require_once 'LoginToolsConnector.php';


$loginTools = new LoginToolsConnector("http://webdev.cs.uiowa.edu" . $Base . "HawkIdLogin.php");

if (!array_key_exists(LT_TICKET_KEY, $_REQUEST)) {
    header("Location: " . $loginTools->loginString());

} else {

    if ($loginTools->checkTicket($_REQUEST[LT_TICKET_KEY])) {
        #Good Login. Get hawkID then log them in.
        $hawkId = $loginTools->hawkid();

        #Checks to see if this hawkid is allowed in our system
        $results = queryDB("select * from UI_PERSON where HAWK_ID = '" . $hawkId . "'");
        if (!($result = nextRow($results))) {
            header(redirect("index.php", '?warning=' . urlencode('Sorry but you have not been signed up for this application.')));
            exit;
        }

        #Set a new nonce now.
        $nonce = randomString(45);
        updateDB("update UI_PERSON set NONCE = '" . $nonce . "' where ID = '" . $result["ID"] . "'");

        #Now lets make some cookies (nom nom nom)
        setcookie('pid', 'ad' . $result['ID'], time() + 60 * 60 * 2);
        setcookie('creds', hashString($result['ID'] . $nonce), time() + 60 * 60 * 2);

        #Now redirect to the home page
        header(redirect("index.php"));
        exit;
    } else {
        header(redirect("index.php", '?error=' . urlencode('There was a problem logging into our application with your hawkid')));
    }
}
?>