<?php
/**
 * User: rhmiller
 * Date: 12/3/12
 */
include_once 'Header.php';
# Check to make sure the person is logged in and has the correct permission
if (!hasPermission("D")) {
    header(redirect("index.php", "?warning=" . urlencode("You do not have access to this page")));
    exit;
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
        "' where LETTER_ID = '" . $_POST['letterId'] . "'")
    ) {
        header(refresh('?success=' . urlencode("Letter has been uploaded.")));
        exit;
    } else {
        header(refresh("?error=" . urlencode("There was a problem uploading the file.")));
        exit;
    }
}

# Deletes a letter from the database.
if (isset($_GET['delete'])) {
    $sql = "update LETTER_OF_REC set LETTER = null where LETTER_ID = '" . $_GET['letterId'] . "'";
    if (updateDB($sql)) {
        header(refresh('?success=' . urlencode("Letter has been deleted.")));
        exit;
    } else {
        header(refresh("?error=" . urlencode("There was a problem deleting the file.")));
        exit;
    }
}

?>

<div class="container">
    <!--    Form for uploading letters of recommendation -->
    <div class="row">
        <div class="span8">
            <form method="post" enctype="multipart/form-data" action="<?php echo myURL(); ?>">
                <label for="letterId">
                    <select name="letterId" id="letterId">
                        <?php
                        # This gets the names of the recommendees for a recommendation writer
                        $sql = "select AP.LAST_NAME, AP.FIRST_NAME, LOR.LETTER_ID
                                from APPLICATION AP
                                left outer join NON_UI_PERSON NUP on AP.NON_UI_PERSON_ID = NUP.ID
                                left outer join LETTER_OF_REC LOR on AP.APP_ID = LOR.APPLICATION_APP_ID
                                where LOR.NON_UI_PERSON_ID = '" . getPersonId() ."'
                                and LOR.LETTER is null";
                        $results = queryDB($sql);

                        # Inserts the options for the drop-down menu.
                        while ($result = nextRow($results)) {
                            echo "<option value='" . $result['LETTER_ID'] . "'>" . $result['LAST_NAME'] . ", " . $result['FIRST_NAME'] . "</option> \n";
                        }
                        ?>
                    </select>
                </label>
                <input name="userfile" type="file" id="userfile"/> &nbsp;&nbsp;&nbsp;
                <input name="upload" type="submit" class="btn btn-success" id="upload" value=" Upload "/>

            </form>
        </div>
    </div>

    <div class="row">
        <div class="offset1">
            <h3>Applicants Requesting Letters From You:</h3>
        </div>
    </div>

    <!--    This is the table that shows what letters have already been uploaded-->
    <div class="row">
        <div class="span8">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Letter Uploaded</th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $q = "select AP.LAST_NAME, AP.FIRST_NAME, ISNULL(LOR.LETTER), LOR.LETTER_ID
                        from APPLICATION AP
                        left outer join NON_UI_PERSON NUP on AP.NON_UI_PERSON_ID = NUP.ID
                        left outer join LETTER_OF_REC LOR on AP.APP_ID = LOR.APPLICATION_APP_ID
                        where LOR.NON_UI_PERSON_ID = '" . getPersonId() . "'";

                $list = queryDB($q);
                while ($line = nextRow($list)) {
                    echo "<tr> \n";
                    echo "<td>" . $line['LAST_NAME'] . ", " . $line['FIRST_NAME'] . "</td> \n";
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