<?php
include_once 'Include.php';

# Check to make sure the person is logged in and has the correct permission
if ((!hasPermission("C")) && (!hasPermission("D")) && (!hasPermission("B"))) {
    header(redirect("index.php", "?warning=" . urlencode("You do not have access to this page")));
}

# Check to make sure there was an application id passed into the page
if (!isset($_GET['letterId'])) {
    header(redirect("FindApplicants.php", "?warning=" . urlencode("Must select an application to upload for")));
}

$sql = "select LETTER from LETTER_OF_REC where LETTER_ID = '" . $_GET['letterId'] . "'";
$result = nextRow(queryDB($sql));
$file = stripslashes($result['LETTER']);


header('Content-disposition: inline; filename="letter.pdf"');
header('Content-type: application/pdf');

echo $file;

?>

