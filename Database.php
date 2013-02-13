<?php

include_once 'etc/config.php';


# DB connection function.
function connectDB()
{
    global $DB, $Host, $User, $Passwd;
    $connection = mysql_connect($Host, $User, $Passwd);
    if (!isset($connection))
        punt("Can't connect to MySQL server $Host");
    if (!mysql_select_db($DB))
        punt("Can't connect to MySQL database $DB on $Host");
}

#This function is used for submitting queries to the database
function queryDB($query)
{
    $result = mysql_query($query . ";");
    if (!$result) {
        punt('Error in submitting the query in the queryDB() fucntion', $query);
    }
    return ($result);
}

#This function is used for submitting updates to the database
function updateDB($updateSql)
{
    $result = mysql_query($updateSql . ";");
    if (!$result) {
        punt('Error in submitting the update to the database in the updateDB fucntion', $updateSql);
        return (false);
    }
    return (true);
}

#This fucntion takes in a query result object
#This fucntion returns the next row in associative array format
function nextRow($result)
{
    if (!$result) {
        punt('Bad result object passed into the nextRow() fucntion');
    }

    return (mysql_fetch_array($result));
}

#This fucntion takes in a query result object
#This fucntion returns the number of rows from a query
function numRows($result)
{
    if (!$result) {
        punt('Unable to find the number of rows in the numRows() fucntion');
    }

    return (mysql_num_rows($result));
}

connectDB();
?>