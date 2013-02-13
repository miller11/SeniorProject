<?php

include_once 'Include.php';
if (!hasPermission("A")) {
    header(redirect("index.php", "?warning=" . urlencode("You do not have access to this page")));
}


#following code is from http://code.stephenmorley.org/php/creating-downloadable-csv-files/
// output headers so that the file is downloaded rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=applicantData.csv');

// create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// output the column headings
fputcsv($output, array('Application ID', 'First Name', 'Last Name', 'PhD Date', 'Submission Date', 'Gender'));

// fetch the data

$rows = queryDB('SELECT APP_ID, FIRST_NAME, LAST_NAME, PHD_DATE, SUBMISSION_DATE, GENDER FROM APPLICATION');

// loop over the rows, outputting them
while ($row = nextRow($rows)) {
    $arr[0] = $row['APP_ID'];
    $arr[1] = $row['FIRST_NAME'];
    $arr[2] = $row['LAST_NAME'];
    $arr[3] = $row['PHD_DATE'];
    $arr[4] = $row['SUBMISSION_DATE'];
    $arr[5] = $row['GENDER'];

    fputcsv($output, $arr);
}
?>