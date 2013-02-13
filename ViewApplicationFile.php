<?php
/**
 * User: rhmiller
 * Date: 12/8/12
 */

#Include functions
include_once "Header.php";

# If you're already authenticated, go to index.php
if ((!hasPermission("E")) && (!hasPermission("B"))) {
    header(redirect('index.php', '?warning=' . urlencode("Sorry but you are not allowed access to that page")));
    exit;
}

if(!isset($_GET['resourceType'])){
    header(redirect('Application.php', '?warning=' . urlencode("Sorry but this link appears to be broken")));
    exit;
}

if ($_GET['resourceType'] == "cv"){
    $type = "CV";
} else if ($_GET['resourceType'] == "coverLetter"){
    $type = "COVER_LETTER";
} else {
    $type = "RESEARCH_STATEMENT";
}

if(!isset($_GET['appId'])){
    $sql = "select $type from APPLICATION where NON_UI_PERSON_ID = '" . getPersonId() . "'";

} else {
    $sql = "select $type from APPLICATION where APP_ID = '" . $_GET['appId'] . "'";
}


$result = nextRow(queryDB($sql));
$file = stripslashes($result[$type]);


header('Content-disposition: inline; filename="letter.pdf"');
header('Content-type: application/pdf');

echo $file;