<?php
include_once 'Header.php';
# Check to make sure they have the correct permission
if ((!hasPermission("C")) || (!hasPermission("A"))) {
    header(redirect("index.php", "?warning=" . urlencode("You do not have access to this page")));
}
?>
<div class="container">

    <!--    This is the form that allows users to select a search parameter and then search by either email or by name-->
    <div class="row">
        <div class="span7">
            <form class="form-inline" method="GET" action="<?php echo myURL(); ?>">
                <label>
                    <select name="searchType">
                        <option value="name">Name</option>
                        <option value="email">Reference Email</option>
                    </select>
                </label>

                <label for="searchAddition"><input style="line-height: 200%;" type="text" id="searchAddition"
                                                   class="input" name="searchAddition"></label>
                &nbsp;&nbsp;&nbsp;

                <button type="submit" name="submit" class="btn btn-success">Search</button>
            </form>
        </div>
    </div>

    <!--    This is the table that displays the data and allows it to be sorted by last name. -->
    <div class="row">
        <div class="span12">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th><?php echo getLink("Name", "APPLICATION.LAST_NAME"); ?></th>
                    <th>Email</th>
                    <th>First Reference</th>
                    <th>Second Reference</th>
                    <th>Third Reference</th>
                    <th>Letters Uploaded</th>
                </tr>
                </thead>
                <tbody>
                <?php

                #These functions help build
                $results = queryDB(buildQuery(getSearchAddition()));

                # This section of code gets the info to fill the table headings above
                while (($result = nextRow($results))) {
                    echo "<tr> \n";
                    echo "<td><a href='UploadLetter.php?appId=" . $result['APP_ID'] . "'>" .
                        $result['LAST_NAME'] . ", " . $result['FIRST_NAME'] . "</a></td> \n";
                    echo "<td>" . $result['EMAIL'] . "</td> \n";
                    $letterWriters = getLetterWriters($result['APP_ID']);
                    while (count($letterWriters) != 0) {
                        echo "<td>" . array_pop($letterWriters) . "</td> \n";
                    }
                    echo "<td>" . getLettersCount($result['APP_ID']) . "</td> \n";
                    echo "</tr> \n";
                }

                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<?php
# This function builds a query addition for any searches run on the page
function getSearchAddition()
{
    if (isset($_GET['searchType']) && isset($_GET['searchAddition'])) {
        if ($_GET['searchType'] == 'email') {
            $result = nextRow(queryDB("select ID from NON_UI_PERSON where EMAIL = '" . $_GET['searchAddition'] . "'"));

            return " left join LETTER_OF_REC
                    on APPLICATION.APP_ID = LETTER_OF_REC.APPLICATION_APP_ID
                    where LETTER_OF_REC.NON_UI_PERSON_ID = '" . $result['ID'] . "'";
        } else {
            return "where UPPER(APPLICATION.FIRST_NAME) = UPPER('" . $_GET['searchAddition'] . "')
                    or UPPER(APPLICATION.LAST_NAME) = UPPER('" . $_GET['searchAddition'] . "')";
        }
    } else {
        return '';
    }
}

# This function helps build a link that allows users to sort the table
function getLink($columnName, $databaseColumn)
{
    if (isset($_GET['startIndex'])) {
        $startIndex = $_GET['startIndex'];
    } else {
        $startIndex = 0;
    }

    if (isset($_GET['sortDirection'])) {
        if ($_GET['sortDirection'] == "asc") {
            $sortDirection = "desc";
            $icon = "<i class='icon-chevron-up'></i>";
        } else {
            $sortDirection = "asc";
            $icon = "<i class='icon-chevron-down'></i>";
        }
    } else {
        $sortDirection = "desc";
        $icon = "<i class='icon-chevron-up'></i>";
    }

    if (isset($_GET['searchType']) && isset($_GET['searchAddition'])) {
        $searchAddition = "&searchType=" . $_GET['searchType'] . "&searchAddition=" . $_GET['searchAddition'];
    } else {
        $searchAddition = '';
    }

    return "<a href='FindApplicants.php?startIndex=" . $startIndex . "&sortColumn=" . $databaseColumn .
        "&sortDirection=" . $sortDirection . $searchAddition . "'>" . $columnName . " " . $icon . "</a>";
}

# This function gets the number of letters that have been uploaded
function getLettersCount($appID)
{
    $results1 = queryDB("select count(*) from LETTER_OF_REC where APPLICATION_APP_ID = '" . $appID . "' and LETTER is not null");
    if ($result1 = nextRow($results1)) {
        return $result1['count(*)'];
    } else {
        return 0;
    }
}

# This function returns the two recommendation writers 
function getLetterWriters($appID)
{
    $sql = "select NON_UI_PERSON.EMAIL
            from NON_UI_PERSON left join LETTER_OF_REC on
            NON_UI_PERSON.ID = NON_UI_PERSON_ID
            where LETTER_OF_REC.APPLICATION_APP_ID = '" . $appID . "'";

    $results = queryDB($sql);
    $writers = array();

    for ($i = 0; $i < numRows($results); $i++) {
        $result = nextRow($results);
        $writers[$i] = $result['EMAIL'];
    }

    return $writers;
}

# This function builds the query that displays all the data on the page.
# This function is basically a helper so that we can sort on the columns easily.
function buildQuery($searchAddition)
{
    if (isset($_GET['startIndex'])) {
        $startIndex = $_GET['startIndex'];
    } else {
        $startIndex = 0;
    }

    if (isset($_GET['sortColumn'])) {
        $sortColumn = $_GET['sortColumn'];
    } else {
        $sortColumn = "APPLICATION.LAST_NAME";
    }

    if (isset($_GET['sortDirection'])) {
        $sortDirection = $_GET['sortDirection'];
    } else {
        $sortDirection = "asc";
    }

    $query = "select APPLICATION.APP_ID, APPLICATION.FIRST_NAME, APPLICATION.LAST_NAME, NON_UI_PERSON.EMAIL
                        from APPLICATION left join NON_UI_PERSON on
                        APPLICATION.NON_UI_PERSON_ID = NON_UI_PERSON.ID " . $searchAddition .
        " order by " . $sortColumn . " " . $sortDirection .
        " limit " . $startIndex . ", " . ($startIndex + 25);

    return $query;
}

?>