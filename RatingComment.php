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

if(isset($_GET['add'])){
    #saves the new rating or updates the rating

    $ratingId = getRatingId();
    if ($ratingId == null) {
        $sql = "insert into RATING (RATING, APPLICATION_APP_ID, UI_PERSON_ID)
                    values ('" . $_GET['rating'] . "', '" . $_GET['appId'] . "', '" . getPersonId() . "')";
        updateDB($sql);


    }

    else {
        $sql = "update RATING set
                    RATING = '" . $_GET['rating'] . "',
                    APPLICATION_APP_ID = '" . $_GET['appId'] . "',
                    UI_PERSON_ID = '" . getPersonId() . "'";

        updateDB($sql);
    }
    header(refresh('?success=' . urlencode('Your rating has been recorded') . '&appId=' . $_GET['appId'])); exit;
}

if(isset($_GET['comment'])){
    #saves the new comment or updates the comment

    $commentId = getCommentId();
    if ($commentId == null) {
        $sql = "insert into COMMENT (TEXT, RATING_RATING_ID, COMMENT_DATE)
                    values ('" . $_GET['applicationComment'] . "', '" . getRatingId() . "'NOW())";
        updateDB($sql);

    }

    else {
        $sql = "update COMMENT set
                    TEXT = '" . $_GET['applicationComment'] . "',
                    RATING_RATING_ID = '" . getRatingId() . "',
                    COMMENT_DATE = NOW()";

        updateDB($sql);
    }
    header(refresh('?success=' . urlencode('Your comment has been recorded') . '&appId=' . $_GET['appId'])); exit;
}

# Function to get the name of applicant
function getApplicantName()
{
    $results = queryDB("select FIRST_NAME, LAST_NAME from APPLICATION where APP_ID = '" . $_GET['appId'] . "'");
    $result = nextRow($results);
    return $result['LAST_NAME'] . ", " . $result['FIRST_NAME'];
}

?>

<div class=container xmlns="http://www.w3.org/1999/html">
    <div class="row">
        <!-- This provides simple breadcrumbs so users can page back and forth faster-->
        <ul class="breadcrumb">
            <li><a href="index.php">Home</a> <span class="divider">/</span></li>
            <li><a href="FacultyAppView.php">Applicant List</a><span class="divider">/</span></li>
            <li class="active"><?php echo getApplicantName(); ?></li>
        </ul>
    </div>

    <!--    Header for the page giving the applicant name   -->
    <div class="row">
        <div class="span10">
            <h3>Submit Rating and Comments for: <strong><?php echo getApplicantName(); ?></strong></h3>
        </div>
    </div>

    <!--    code to create tabs -->
    <ul class="nav nav-pills">
        <li class="active">
            <a href="#">Applicant Info</a>
        </li>
        <li>
            <a href='Comments.php?appId=<?php echo $_GET['appId'] ?>'>Comments</a>
        </li>
    </ul>

    <div class="row">
        <div class="span12">
            <table class="table table-striped">
    <?php
    #queries to collect necessary information for filling in table
    $applicantResults = "select * from APPLICATION where APP_ID ='" . $_GET['appId'] . "'";
    $phdResults = "select year(PHD_DATE) as PHD from APPLICATION where APP_ID='" . $_GET['appId'] . "'";
    $referenceResults = "select * from LETTER_OF_REC
                         left outer join NON_UI_PERSON on LETTER_OF_REC.NON_UI_PERSON_ID = NON_UI_PERSON.ID
                         where APPLICATION_APP_ID = '" . $_GET['appId'] . "'";
    $ratingResults = "select avg(RATING) as AVGRATING from RATING
                      where APPLICATION_APP_ID = '" . $_GET['appId'] . "'";
    $researchPubResults = "select * from RESEARCH_PUBLICATION where APPLICATION_APP_ID='" . $_GET['appId'] . "'";

    $queryResults = queryDB($applicantResults);
    $queryPhdResults = nextRow(queryDB($phdResults));
    $queryRefResults = queryDB($referenceResults);
    $queryRatingResults = nextRow(queryDB($ratingResults));
    $queryResearchResults = nextRow(queryDB($researchPubResults));

                #find the rating id
                function getRatingId(){
                    $sql = "select RATING_ID from RATING where APPLICATION_APP_ID = '" . $_GET['appId'] . "' and UI_PERSON_ID = '" . getPersonId() . "'";
                    $results = queryDB($sql);
                    if ($result = nextRow($results)){
                        return $result['RATING_ID'];
                    }
                    else{
                        return null;
                    }
                }

                #find the comment id
                function getCommentId(){
                    $sql = "select COMMENT_ID from COMMENT where RATING_RATING_ID = '" . getRatingId() . "'";
                    $results = queryDB($sql);
                    if ($result = nextRow($results)){
                        return $result['COMMENT_ID'];
                    }
                    else{
                        return null;
                    }
                }


    if($queryResult = nextRow($queryResults)){
            echo "<tr>";
                echo "<th scope='row'>Name:</th>";
                    echo "<td>" . $queryResult['LAST_NAME'] . ", " . $queryResult['FIRST_NAME'] . "</td> \n";
            echo "</tr>";
            echo "<tr>";
                echo "<th scope='row'>PhD Date:</th>";
                    echo "<td>" . $queryPhdResults['PHD'] . "</td> \n";
            echo "</tr>";
            echo "<tr>";
                echo "<th scope='row'>Average Rating:</th>";
                    echo "<td>" . $queryRatingResults['AVGRATING'] .  "</td>";
            echo "</tr>";
            echo "<tr>";
                echo "<th scope='row'>Personal Website:</th>";

                    echo "<td>" . $queryResult['WEBSITE'] . "</td> \n";
            echo "</tr>";
            echo "<tr>";
            $line = nextRow($queryRefResults);
                echo "<th scope='row'>Reference 1 - submitted by: &nbsp &nbsp " . $queryRefResults['EMAIL'] . "</th>";
                    echo "<td><a class='btn btn-primary' href='LettersView.php?letterId=" . $line['LETTER_ID'] . "' target='_blank'>View</a></td> \n";
            echo "</tr>";
            echo "<tr>";
            $line = nextRow($queryRefResults);
                echo "<th scope='row'>Reference 2 - submitted by: &nbsp &nbsp " . $queryRefResults['EMAIL'] . "</th>";
                    echo "<td><a class='btn btn-primary' href='LettersView.php?letterId=" . $line['LETTER_ID'] . "' target='_blank'>View</a></td> \n";
            echo "</tr>";
            echo "<tr>";
            $line = nextRow($queryRefResults);
                echo "<th scope='row'>Reference 3 - submitted by: &nbsp &nbsp " . $queryRefResults['EMAIL'] . "</th>";
                    echo "<td><a class='btn btn-primary' href='LettersView.php?letterId=" . $line['LETTER_ID'] . "' target='_blank'>View</a></td> \n";
            echo "<tr>";
                echo "<th scope='row'>Cover Letter:</th>";
                    echo "<td><a class='btn btn-primary' href='ViewApplicationFile.php?resourceType=coverLetter?appId=" . $_GET['appId'] . "' target='_blank'>View</a></td> \n";
            echo "</tr>";
            echo "<tr>";
                echo "<th scope='row'>Curriculum Vitae:</th>";
                    echo "<td><a class='btn btn-primary' href='ViewApplicationFile.php?resourceType=cv?appId=" . $_GET['appId'] . "' target='_blank'>View</a></td> \n";
            echo "</tr>";
            echo "<tr>";
                echo "<th scope='row'>Teaching/Research Statement:</th>";
                    echo "<td><a class='btn btn-primary' href='ViewApplicationFile.php?resourceType=researchStatement?appId=" . $_GET['appId'] . "' target='_blank'>View</a></td> \n";
            echo "</tr>";
            echo "<tr>";
                echo "<th scope='row'>Research Publication:</th>";
                    echo "<td><a class='btn btn-primary' href='ViewApplicationFile.php?resourceType=researchPublication?appId=" . $_GET['appId'] . "' target='_blank'>View</a></td> \n";
            echo "</tr>";
            echo "<tr>";

            echo "</li>";
    }
    else{
        return '';
    }

    ?>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="span12">
            <?php
            $ratingSql = "select RATING from RATING where RATING_ID = '" . getRatingId() . "'";
            if($ratingResult = nextRow(queryDB($ratingSql))){
                $ratingResult = $ratingResult['RATING'];
            } else {
                $ratingResult = null;
            }

            ?>

            <table class="table">
                <tbody>
                <tr>
                    <td>
                        <!-- radio buttons for rating an applicant -->
                        <form class="form-inline" method="GET" action="<?php echo myURL(); ?>">
                            <input <?php if($ratingResult != null){if($ratingResult == '1'){echo "checked";}} ?> type="radio" name="rating" id="1" value="1" />
                            <label for="1">1 (Abysmal)</label><br/>
                            <input <?php if($ratingResult != null){if($ratingResult == '2'){echo "checked";}} ?> type="radio" name="rating" id="2" value="2" />
                            <label for="2">2 (Sub-Par)</label><br/>
                            <input <?php if($ratingResult != null){if($ratingResult == '3'){echo "checked";}} ?> type="radio" name="rating" id="3" value="3" />
                            <label for="3">3 (Mediocre)</label><br/>
                            <input <?php if($ratingResult != null){if($ratingResult == '4'){echo "checked";}} ?> type="radio" name="rating" id="4" value="4" />
                            <label for="4">4 (Not Too Shabby)</label><br/>
                            <input <?php if($ratingResult != null){if($ratingResult == '5'){echo "checked";}} ?> type="radio" name="rating" id="5" value="5" />
                            <label for="5">5 (Perfection)</label><br/><br/><br/>

                            <input type="hidden" name="appId" value="<?php echo $_GET['appId']?>" />
                            <!-- <input type="hidden" name="currentRatingValue" value="<?php #echo $queryFacRating['RATING']?>"> -->
                            <button type="submit" name="add" class="btn btn-success">Submit Rating</button>
                        </form>
                    </td>
                    <td>
                        <!-- input box for leaving a comment -->
                        <form>
                            <label for="comment">Add Comment:</label><br/>
                            <input type="text" name="applicationComment" id="comment" style="width:300px; height:110px;"><br>
                            <input type="hidden" name="appId" value ="<?php echo $_GET['appId']?>" />
                            <!-- <input type="hidden" name="currentComment" value="<?php #echo $queryComment['TEXT']?>"> -->
                            <button type="submit" name="comment" class="btn btn-success">Submit Comment</button>
                        </form>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
