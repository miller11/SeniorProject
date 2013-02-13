<?php

include_once 'Header.php';
#checks if user has proper permissions to access this page
if (!haspermission("B")) {
    header(redirect("index.php", "?warning=" . urlencode("You do not have access to this page")));
}

# Check to make sure there was an application id passed into the page
if (!isset($_GET['appId'])) {
    header(redirect("FacultyAppView.php", "?warning=" . urlencode("Must select an applicant to submit rating/comments for")));
}

# Function to get the name of applicant
function getApplicantName()
{
    $results = queryDB("select FIRST_NAME, LAST_NAME from APPLICATION where APP_ID = '" . $_GET['appId'] . "'");
    $result = nextRow($results);
    return $result['LAST_NAME'] . ", " . $result['FIRST_NAME'];
}

?>

<div class = container>
    <div class="row">
        <!-- This provides simple breadcrumbs so users can page back and forth faster-->
        <ul class="breadcrumb">
            <li><a href="lgehrt/index.php">Home</a> <span class="divider">/</span></li>
            <li><a href="lgehrt/FindApplicants.php">Applicant List</a><span class="divider">/</span></li>
            <li class="active"><?php echo getApplicantName(); ?></li>
        </ul>
    </div>

    <!--    Header for the page giving the applicant name-->
    <div class="row">
        <div class="span10">
            <h3>Submit Rating and Comments for: <strong><?php echo getApplicantName(); ?></strong></h3>
        </div>
    </div>

?>

<div class="row">
    <div class="span12">
        <table class="table table-striped">

            <?php

            #fill in applicant's information in the applicant tab

            #queries to collect necessary information for filling in table
            $applicantResults = "select * from APPLICATION";
            $referenceResults = "select * from LETTER_OF_REC
                                 left outer join APPLICATION on APPLICATION.APP_ID = LETTER_OF_REC.APPLICATION_APP_ID";
            $queryResults = queryDB($applicantResults);
            $queryRefResults = queryDB($referenceResults);

            while (($queryResult = nextRow($queryResults))) {
                if (($queryResult['APP_ID']) == ($_GET['appId'])) {
                    echo "<tr>";
                        echo "<th scope='row'>Name:</th>";
                            echo "<td>" . $queryResult['LAST_NAME'] . ", " . $queryResult['FIRST_NAME'] . "</td> \n";
                    echo "</tr>";
                    echo "<tr>";
                        echo "<th scope='row'>PhD Date:</th>";
                            echo "<td>" . $queryResult['PHD_DATE'] . "</td> \n";
                    echo "</tr>";
                        echo "<th scope='row'>Average Rating:</th>";
                            echo "<td> </td>"; #average rating here
                    echo "</tr>";
                    echo "<tr>";
                        echo "<th scope='row'>Personal Website:</th>";
                            echo "<td>" . $queryResult['WEBSITE'] . "</td> \n";
                    echo "</tr>";
                    echo "<tr>";
                        echo "<th scope='row'>References:</th>";
                    echo "</tr>";
                    echo "<tr>";
                        echo "<th scope='row'>Cover Letter:</th>";
                            echo "<td>" . $queryResult['COVER_LETTER'] . "</td> \n";
                    echo "</tr>";
                    echo "<tr>";
                        echo "<th scope='row'>Curriculum Vitae:</th>";
                            echo "<td>" . $queryResult['CV'] . "</td> \n";
                    echo "</tr>";
                    echo "<tr>";
                        echo "<th scope='row'>Teaching/Research Statement:</th>";
                            echo "<td>" . $queryResult['RESEARCH_STATEMENT'] . "</td> \n";
                    echo "</tr>";
                    echo "<tr>";
                            echo "<th scope='row'>Research Publications:</th>";
                    echo "</tr>";
                    echo "</li>";
                }
                else{
                    return '';
                }
            }
?>
            </table>
        </div>
    </div>