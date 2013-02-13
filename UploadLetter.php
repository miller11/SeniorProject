<?php
include_once 'Header.php';
# Check to make sure the person is logged in and has the correct permission
if (!hasPermission("C")) {
    header(redirect("index.php", "?warning=" . urlencode("You do not have access to this page")));
    exit;
} else {

# Check to make sure there was an application id passed into the page
    if (!isset($_GET['appId'])) {
        header(redirect("FindApplicants.php", "?warning=" . urlencode("Must select an application to upload for")));
    }

# This takes care of setting the letter of recommendation
    if (isset($_POST['upload']) && $_FILES['userfile']['size'] > 0) {
        $fileName = $_FILES['userfile']['name'];
        $tmpName = $_FILES['userfile']['tmp_name'];
        $fileSize = $_FILES['userfile']['size'];
        $fileType = $_FILES['userfile']['type'];

        # Check to make sure the file size is not too big for the database.
        if ($fileSize >= 64000) {
            header(refresh("?error=" . urlencode("File is too large to upload. Please choose a smaller file.") . "&appId=" . $_POST['appId']));
            exit;
        }

        # Check to make sure the file is a pdf
        if ($fileType != "application/pdf") {
            header(refresh("?warning=" . urlencode("File must be in pdf format.") . "&appId=" . $_POST['appId']));
            exit;
        }

        # Opens the file and prepares it to be saved.
        $fp = fopen($tmpName, 'r');
        $content = fread($fp, filesize($tmpName));
        $content = addslashes($content);
        fclose($fp);
        if (!get_magic_quotes_gpc()) {
            $fileName = addslashes($fileName);
        }

        # Attempts to save the file to the database and then gives a message one either success or failure
        if (updateDB("update LETTER_OF_REC set LETTER = '" . $content .
            "' where APPLICATION_APP_ID = '" . $_POST['appId'] . "' AND NON_UI_PERSON_ID = '" . $_POST['writerId'] . "'")
        ) {
            header(refresh('?appId=' . $_POST['appId'] . '&success=' . urlencode("Letter has been uploaded.")));
            exit;
        } else {
            header(refresh("?error=" . urlencode("There was a problem uploading the file.") . "&appId=" . $_POST['appId']));
            exit;
        }
    }

# Deletes a letter from the database.
    if (isset($_GET['delete'])) {
        $sql = "update LETTER_OF_REC set LETTER = null where LETTER_ID = '" . $_GET['letterId'] . "'";
        if (updateDB($sql)) {
            header(refresh('?appId=' . $_POST['appId'] . '&success=' . urlencode("Letter has been deleted.")));
            exit;
        } else {
            header(refresh("?error=" . urlencode("There was a problem deleting the file.") . "&appId=" . $_POST['appId']));
            exit;
        }
    }
}

# Function to get the name of applicant
function getApplicantName()
{
    $results = queryDB("select FIRST_NAME, LAST_NAME from APPLICATION where APP_ID = '" . $_GET['appId'] . "'");
    $result = nextRow($results);
    return $result['LAST_NAME'] . ", " . $result['FIRST_NAME'];
}

?>

<div class="container">
    <div class="row">
        <!-- This provides simple breadcrumbs so users can page back and forth faster-->
        <ul class="breadcrumb">
            <li><a href="index.php">Home</a> <span class="divider">/</span></li>
            <li><a href="FindApplicants.php">Application List</a><span class="divider">/</span></li>
            <li class="active"><?php echo getApplicantName(); ?></li>
        </ul>
    </div>

    <!--    Header for the page giving the applicant name-->
    <div class="row">
        <div class="span10">
            <h3>Uploading Letters of Recommendation for: <strong><?php echo getApplicantName(); ?></strong></h3>
        </div>
    </div>

    <!--    Placeholder row-->
    <div class="row">
    </div>

    <!--    Form for uploading letters of recommendation -->
    <div class="row">
        <div class="span8">
            <form method="post" enctype="multipart/form-data" action="<?php echo myURL(); ?>">
                <label for="writerId">
                    <select name="writerId" id="writerId">
                        <?php
                        # This gets the two email addresses of the recommendation writers
                        $sql = "select NON_UI_PERSON.EMAIL, NON_UI_PERSON.ID
                                from NON_UI_PERSON left join LETTER_OF_REC
                                on NON_UI_PERSON.ID = LETTER_OF_REC.NON_UI_PERSON_ID
                                where LETTER_OF_REC.APPLICATION_APP_ID = '" . $_GET['appId'] . "'
                                and LETTER_OF_REC.LETTER is null";
                        $results = queryDB($sql);

                        # Inserts the options for the drop-down menu.
                        while ($result = nextRow($results)) {
                            echo "<option value='" . $result['ID'] . "'>" . $result['EMAIL'] . "</option> \n";
                        }
                        ?>
                    </select>
                </label>
                <input name="userfile" type="file" id="userfile"> &nbsp;&nbsp;&nbsp;
                <input type="hidden" name="appId" id="appId" value="<?php echo $_GET['appId']; ?>">
                <input name="upload" type="submit" class="btn btn-success" id="upload" value=" Upload ">

            </form>
        </div>
    </div>

    <!--    This is the table that shows what letters have already been uploaded-->
    <div class="row">
        <div class="span8">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Reference Email</th>
                    <th>Letter Uploaded</th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $q = "select LETTER_OF_REC.LETTER_ID, NON_UI_PERSON.EMAIL, ISNULL(LETTER_OF_REC.LETTER)
                      from NON_UI_PERSON left join LETTER_OF_REC
                      on NON_UI_PERSON.ID = LETTER_OF_REC.NON_UI_PERSON_ID
                      where LETTER_OF_REC.APPLICATION_APP_ID = '" . $_GET['appId'] . "'";

                $list = queryDB($q);
                while ($line = nextRow($list)) {
                    echo "<tr> \n";
                    echo "<td>" . $line['EMAIL'] . "</td> \n";
                    if ($line[2] == '0') {
                        echo "<td><span class='label label-success'>Yes</span> </td> \n";
                        $disabled = '';
                    } else {
                        echo "<td><span class='label label-important'>No</span> </td> \n";
                        $disabled = 'disabled';
                    }
                    echo "<td><a href='LettersView.php?letterId=" . $line['LETTER_ID'] . "' target='_blank'><button " . $disabled .
                        " class='btn btn-primary'>View</button></a></td>";
                    echo "<td><a href='RecommendationLettersUpload.php?delete=1&letterId=" . $line['LETTER_ID'] . "'><button "
                        . $disabled . " class='btn btn-danger' onclick='if(!confirm(\"Are you sure you want to delete this letter?\"))
                        {return false;}'>Delete Letter</button></a></td> \n";
                    echo "</tr> \n";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>