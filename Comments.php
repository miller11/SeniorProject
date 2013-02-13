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

<div class = container>
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
    <li>
        <a href='RatingComment.php?appId=<?php echo $_GET['appId'] ?>'>Applicant Info</a>
    </li>
    <li class="active">
        <a href="#">Comments</a>
    </li>
</ul>
    <div class="row">
        <div class="span12">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Committee Member</th>
                    <th>Comment</th>
                    <th>Date Posted</th>
                </tr>
                </thead>
                <tbody>
                <?php
                #query to fetch faculty and comment information to populate the table

                $commentResults = "select UI_PERSON.NAME, COMMENT.TEXT, COMMENT.COMMENT_DATE
                                   from RATING
                                   left outer join COMMENT on RATING.RATING_ID = COMMENT.RATING_RATING_ID
                                   left outer join UI_PERSON on RATING.UI_PERSON_ID = UI_PERSON.ID
                                   order by COMMENT_DATE desc";

                $queryResults = queryDB($commentResults);
                while(($queryResult = nextRow($queryResults))){
                    $commentDateResults = "select  date_format(COMMENT_DATE, '%W, %M, %Y')as COMMENTDATE from COMMENT where RATING_RATING_ID='" . getRatingId() . "'";
                    $queryCommentDate = nextRow(queryDB($commentDateResults));
                    echo "<tr> \n";
                        echo "<td>" . $queryResult['NAME'] . "</td> \n";
                        echo "<td>" . $queryResult['TEXT'] . "</td> \n";
                        echo "<td>" . $queryCommentDate['COMMENTDATE'] . "</td> \n";
                    echo "</tr>";
                }
                ?>
                </tbody>

            </table>
            <?php
            #get the rating and comments that have already been submitted by the logged in faculty member
            $findFacRating = "select RATING, RATING_ID from RATING where APPLICATION_APP_ID = '" . $_GET['appId'] . "' and UI_PERSON_ID = '" . getPersonId() . "'";
            $queryFacRating = nextRow(queryDB($findFacRating));

            $findComment = "select TEXT from COMMENT where RATING_RATING_ID = '" . $queryFacRating['RATING_ID'] . "'";
            $queryComment = nextRow(queryDB($findComment));

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
?>
            <table class="table">
                <?php
                $ratingSql = "select RATING from RATING where RATING_ID = '" . getRatingId() . "'";
                if($ratingResult = nextRow(queryDB($ratingSql))){
                    $ratingResult = $ratingResult['RATING'];
                } else {
                    $ratingResult = null;
                }

                ?>
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
                            <label for="comment">Add/Update Comment:</label><br/>
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
    <?php


?>


