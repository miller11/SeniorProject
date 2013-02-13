<?php
include_once "Include.php";

?>

<html>
<head>
    <title>Informatics Project</title>

    <link rel="StyleSheet" href="bootstrap/css/bootstrap.css" type="text/css" media="screen"/>
</head>

<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="brand pull-left" href="index.php">Project</a>


            <ul class="nav pull-left">
                <li><a href="#">Home</a></li>
                <?php
                if(hasPermission("E")) {
                    echo "<li><a href='Application.php'>MyApplications</a></li>";
                }

                if (hasPermission("A")) {
                    echo "<li><a href='AdminCenter.php'>Admin Center</a></li>";
                }
                if (hasPermission("B")) {
                    echo "<li><a href='FacultyAppView.php'>View Applications</a></li>";
                }

                if(hasPermission("C")) {
                    echo "<li><a href='FindApplicants.php'>Find Applicants</a></li>";
                }

                ?>
            </ul>

            <ul class="nav pull-right">
                <?php
                if (isLoggedIn()) {
                    echo "<li style=\"padding-top: 10px;\">" . getPersonUserName() . "</li>";
                    echo "<li><a name='logout' href=\"" . myURL("?logout=1") . "\">Log Out</a></li>";
                } else {
                    echo "<li><a href='NewUser.php'>Sign Up</a></li>";
                    echo "<li><a href='Login.php'>Login</a></li>";
                }
                ?>
            </ul>
        </div>
    </div>
</div>

<br/>
<br/>
<br/>

<?php
# This prints out any success status messages
if (isset($_GET['success'])) {
    echo '<div class="alert alert-success container">';
    echo '<strong>Success! </strong>';
    echo urldecode($_GET['success']);
    echo '</div>';
}

# This prints out any info status messages
if (isset($_GET['info'])) {
    echo '<div class="alert alert-info container">';
    echo '<strong>Info! </strong>';
    echo urldecode($_GET['info']);
    echo '</div>';
}

# This prints out any warnings that are generated
if (isset($_GET['warning'])) {
    echo '<div class="alert container">';
    echo '<strong>Warning! </strong>';
    echo urldecode($_GET['warning']);
    echo '</div>';
}

# This prints out any errors that are generated
if (isset($_GET['error'])) {
    echo '<div class="alert alert-error container">';
    echo '<strong>Error! </strong>';
    echo urldecode($_GET['error']);
    echo '</div>';
}

# This user logs the user out
if (isset($_GET['logout'])) {
    if (is_numeric($_COOKIE['pid'])) {
        $result = updateDB("update NON_UI_PERSON set NONCE = '" . randomString(45) . "' where ID = '" . getPersonId() . "'");
        header(refresh("?info=" . urlencode("You have been logged out.")));
        exit;
    } else {
        $result = updateDB("update UI_PERSON set NONCE = '" . randomString(45) . "' where ID = '" . getPersonId() . "'");
        header(refresh("?info=" . urlencode("You have been logged out.")));
        exit;
    }
}
?>

<script src="http://code.jquery.com/jquery-latest.js"></script>
<script src="bootstrap/js/bootstrap.js"></script>