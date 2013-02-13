<?php
include_once 'etc/config.php';
include_once 'Database.php';

#################### Error message function. ##################
function punt($message, $query = '')
{

    $lastPart = '';
    # Check to see if error resulted from a MySQL interaction,
    # i.e., is the $query variable set or not?
    if ($query != '') {
        $lastPart = "<br>MySQL query string:<br>  $query" .
            '<br>MySQL error number: ' . mysql_errno() .
            '<br>MySQL error description: ' . mysql_error();
    }
    die ("<br><br><b>Error: $message</b>" . $lastPart);
}

###################################################################

######################### URL Functions ###########################

# the desired page. The default URL is the base URL.
function getURL($path = '', $args = '')
{
    global $Proto, $Base, $Host;
    return $Proto . $Host . $Base . $path . $args;
}

# redirect returns a PHP redirect construct. The default URL is the base
# URL.
function redirect($path = '', $args = '')
{
    return 'Location: ' . getURL($path, $args);
}

# Analagous functions for current page (syntactic sugar).
function myURL($args = '')
{
    global $Proto, $Host;
    return $Proto . $Host . $_SERVER['PHP_SELF'] . $args;
}

function refresh($args = '')
{
    return 'Location: ' . myURL($args);
}

###################################################################

##################### Security Functions ##########################
# Function to get a random string with a specified length
function randomString($length = 127)
{
    return (substr(md5(mt_rand()), 0, ($length - 1)));
}

#Hash algorithm (probably changing our hashing type soon.)
function hashString($string)
{
    return (md5($string));
}

####################################################################

#################### Authorization Functions #######################
# Function checks to see if the person credentials are valid
function isLoggedIn()
{
    if (isset($_COOKIE['pid']) && $_COOKIE['creds']) {
        if (!isAdminId($_COOKIE['pid'])) {
            $sql = "select * from NON_UI_PERSON where ID = '" . $_COOKIE['pid'] . "'";
        } else {
            $sql = "select * from UI_PERSON where ID = '" . substr($_COOKIE['pid'], 2) . "'";
        }

        $results = queryDB($sql);
        if ($result = nextRow($results)) {
            if (hashString($result['ID'] . $result['NONCE']) == $_COOKIE['creds']) {
                return true;
            }
        }
    }

    return false;
}


# Function to get the info from a logged in person returns null if
# nobody is logged in
function getPersonUserName()
{
    if (isLoggedIn()) {
        if (!isAdminId($_COOKIE['pid'])) {
            #This person is a regular user
            $results = queryDB("select * from NON_UI_PERSON where ID = '" . $_COOKIE['pid'] . "'");
            if ($result = nextRow($results)) {
                return $result['EMAIL'];
            }
        } else {
            # This user is a UI user
            $results = queryDB("select * from UI_PERSON where ID = '" . getPersonId() . "'");
            if ($result = nextRow($results)) {
                return $result['HAWK_ID'];
            }
        }
    }
    return null;
}

# Function to first validate a user then return their id if validated otherwise null
function getPersonId()
{
    if (isLoggedIn()) {
        if (!isAdminId($_COOKIE['pid'])) {
            return $_COOKIE['pid'];
        } else {
            return substr($_COOKIE['pid'], 2);
        }
    }
    return null;
}

# Function to check permissions. Takes an argument that is required for a given page and returns true if the person
# has the given permission.
function hasPermission($permissionRequired)
{
    if (isLoggedIn()) {
        if (!isAdminId($_COOKIE['pid'])) {
            $sql = "select * from NON_UI_PERSON_LOOKUP where NON_UI_PERSON_ID = '" . getPersonId() . "' and " .
                "PERMISSIONS_PERMISSION = '" . $permissionRequired . "'";
        } else {
            $sql = "select * from UI_PERSON_LOOKUP where UI_PERSON_ID = '" . getPersonId() . "' and " .
                "PERMISSIONS_PERMISSION = '" . $permissionRequired . "'";
        }

        $results = queryDB($sql);
        if ($result = nextRow($results)) {
            return true;
        }
    }
    return false;
}

# Function to check to see if the user has an amdin id or a normal one.
function isAdminId($id)
{
    if (strlen($id) > 2) {
        if (substr($id, 0, 2) == 'ad') {
            return true;
        }
    }

    return false;
}

?>