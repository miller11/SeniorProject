<?php
include_once 'Header.php';
#checks if user has proper permissions to access this page
if (!haspermission("B")) {
    header(redirect("index.php", "?warning=" . urlencode("You do not have access to this page")));
}
?>


<?php

#builds a query addition for searches run on this page
function getSearchAddition() {

    if (isset($_GET['searchType']) && isset($_GET['searchAddition'])) {
         if ($_GET['searchType'] == 'rating') {
            $result = nextRow(queryDB("select APPLICATION_APP_ID from RATING where RATING  = '" . $_GET['searchAddition'] . "'"));

            return "left outer join RATING
                    on RATING.APPLICATION_APP_ID = APPLICATION.APP_ID
                    where RATING.APPLICATION_APP_ID = '" . $result['ID'] . "'";
        }
        else {
            return "where UPPER(APPLICATION.FIRST_NAME) = UPPER('" . $_GET['searchAddition'] . "')
                    or UPPER(APPLICATION.LAST_NAME) = UPPER('" . $_GET['searchAddition'] . "')";
        }
    }

    else {
        return '';
    }
}

#function to sort the table
function getLink($columnName, $databaseColumn) {
    if (isset($_GET['startIndex'])) {
        $startIndex = $_GET['startIndex'];
    }
    else{
        $startIndex = 0;
    }
    if (isset($_GET['sortDirection'])) {
        if ($_GET['sortDirection'] == "asc") {
            $sortDirection = "desc";
            $icon = "<i class='icon-chevron-up'></i>";
        }
        else {
            $sortDirection = "asc";
            $icon = "<i class='icon-chevron-down'></i>";
        }
    }
    else {
        $sortDirection = "desc";
        $icon = "<i class='icon-chevron-up'></i>";
    }
    if (isset($_GET['searchType']) && isset($_GET['searchAddition'])) {
        $searchAddition = "&searchType=" . $_GET['searchType'] . "&searchAddition=" . $_GET['searchAddition'];
    }
    else {
        $searchAddition = '';
    }
    return "<a href='FacultyAppView.php?startIndex=" . $startIndex . "&sortColumn=" . $databaseColumn .
        "&sortDirection=" . $sortDirection . $searchAddition . "'>" . $columnName . " " . $icon . "</a>";
}

#function returns the status of an application as complete or not complete
function getApplicationStatus($appID){
    $tt = queryDB("select count(*)
    from LETTER_OF_REC
    where APPLICATION_APP_ID = '" . $appID . "'
    and LETTER is not null");
    if ($status = nextRow($tt)){
        return $status['count(*)'];
    }
    else{
        return 0;
    }
}

#function returns average rating for an applicant
function getRating($appID){
    $rating = "select avg(RATING) as APP_RATING
    from RATING
    left outer join APPLICATION
    on RATING.APPLICATION_APP_ID = APPLICATION.APP_ID
    where RATING.APPLICATION_APP_ID =  '" . $appID . "'";
    $applicationRating = queryDB($rating);
    return $applicationRating;
}

#function to assist with populating the table and sorting the data
function buildQuery($searchAddition) {
    if (isset($_GET['startIndex'])) {
        $startIndex = $_GET['startIndex'];
    }
    else {
        $startIndex = 0;
    }
    if (isset($_GET['sortColumn'])) {
        $sortColumn = $_GET['sortColumn'];
    }
    else {
        $sortColumn = "APPLICATION.LAST_NAME";
    }
    if (isset($_GET['sortDirection'])) {
        $sortDirection = $_GET['sortDirection'];
    }
    else {
        $sortDirection = "asc";
    }

    $query = "select APPLICATION.FIRST_NAME, APPLICATION.LAST_NAME, NON_UI_PERSON.EMAIL, APPLICATION.PHD_DATE
                from APPLICATION
                left outer join NON_UI_PERSON on APPLICATION.NON_UI_PERSON_ID = NON_UI_PERSON.ID " . $searchAddition .
        " order by " . $sortColumn . " " . $sortDirection .
        " limit " . $startIndex . ", " . ($startIndex + 25);
    return $query;
}

?>


<div class="container">

    <br />

    <h3>Submitted Applications</h3>
    <!-- search applications by a parameter -->
    <div class="row">
        <div class="span7">
            <form class="form-inline" method="GET" action="<?php echo myURL(); ?>">
                <label>
                    <select name="searchType">
                        <option value="name">Name</option>
                        <option value="rating">Average Rating</option>
                    </select>
                </label>
                <label for="searchAddition"><input style="line-height: 200%;" type="text" id="searchAddition"
                                                   class="input" name="searchAddition"</label>
                &nbsp;&nbsp;&nbsp;

                <button type="submit" name="submit" class="btn btn-success">Search</button>
            </form>
        </div>
    </div>

    <!-- table containing the applicants and their pertinent information -->
    <div class="row">
        <div class="span12">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th><?php echo getLink("Name", "APPLICATION.LAST_NAME"); ?></th>
                    <th>Applicant Email</th>
                    <th>PhD Date</th>
                    <th>Application Status</th>
                    <th>Average Rating</th>
                </tr>
                </thead>
                <tbody>
                <?php
                #query fetches pertinent applicant information and populates the table with this information
                $results = queryDB(buildQuery(getSearchAddition()));
                $personResults = "select APPLICATION.APP_ID, APPLICATION.FIRST_NAME, APPLICATION.LAST_NAME, NON_UI_PERSON.EMAIL, APPLICATION.PHD_DATE
                from APPLICATION
                left outer join NON_UI_PERSON on APPLICATION.NON_UI_PERSON_ID = NON_UI_PERSON.ID
                order by APPLICATION.LAST_NAME asc";


                $queryResults = queryDB($personResults);

                while(($queryResult = nextRow($queryResults))){
                    $phdResults = "select year(PHD_DATE) as PHD from APPLICATION where APP_ID='" . $queryResult['APP_ID'] . "'";
                    $queryPhdResults = nextRow(queryDB($phdResults));
                    echo "<tr> \n";
                        echo "<td><a href='RatingComment.php?appId=" . $queryResult['APP_ID'] . "'>" . $queryResult['LAST_NAME'] . ", " . $queryResult['FIRST_NAME'] . "</a></td> \n";
                        echo "<td>" . $queryResult['EMAIL'] . "</td> \n";
                        echo "<td>" . $queryPhdResults['PHD'] . "</td> \n";

                        $applicationStatus = getApplicationStatus($queryResult['APP_ID']);
                        if ($applicationStatus == 3) {
                            echo "<td><span class='label label-success'>Complete</span></td> \n";
                        }
                        else {
                            echo "<td><span class='label label-important'>Incomplete</span></td> \n";
                        }
                        $applicantRating = getRating($queryResult['APP_ID']);
                        echo "<td>" . $applicantRating["APP_RATING"] . "</td> \n";
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>