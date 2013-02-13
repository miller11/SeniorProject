<?php

#Include functions
include_once "Header.php";
include_once "App.php";

# If you're already authenticated, go to index.php
if (!hasPermission("E")) {
    header(redirect('index.php', '?warning=' . urlencode("Please sign in to access this page.")));
    exit;
}

# Retrieves the saved application for the user if they have one.
if (getAppId() != null) {
    $result = nextRow(queryDB("select * from APPLICATION where APP_ID = '" . getAppId() . "'"));
    $app = new App($result);
    if($result['SUBMISSION_DATE'] != null){
        $disabled = "disabled";
    }else {
        $disabled = "";
    }

} else {
    $app = null;
    $disabled = "";
}

#This just saves the application without submitting it.
if (isset($_POST['save'])) {
    #Makes sure at least the first and last name fields are filled in
    if ((!isset($_POST['fname'])) || (!isset($_POST['lname']))) {
        header(refresh("?error=") . urlencode("Please fill in the name fields before saving") .
            "&fname=" . urlencode($_POST['fname']) . "&lname=" . urlencode($_POST['lname']));
        exit;
    }

    #This saves the application and gives a message about the success of the action
    if (saveApp()) {
        header(refresh("?info=") . urlencode("Your application has been saved"));
        exit;
    } else {
        header(refresh("?warning=") . urlencode("There was a problem saving your application") .
            "&fname=" . urlencode($_POST['fname']) . "&lname=" . urlencode($_POST['lname']));
        exit;
    }
}

# Submits the application to be evaluated.
if (isset($_POST['submit'])) {
    $urlAddition = null;

    #Makes sure the first and last name fields are filled in.
    if ((!isset($_POST['fname'])) || (!isset($_POST['lname']))) {
        header(refresh("?error=") . urlencode("Please fill in the name fields before saving") .
            "&fname=" . urlencode($_POST['fname']) . "&lname=" . urlencode($_POST['lname']));
        exit;
    }

    #Makes sure the website is set.
    if (!isset($_POST['website'])) {
        $urlAddition = buildAddition('website', $urlAddition);
    }

    #Makes sure the phd year and month is set.
    if ((!isset($_POST['phdYear'])) || (!isset($_POST['phdMonth']))) {
        $urlAddition = buildAddition('phdYear', $urlAddition);
        $urlAddition = buildAddition('hdMonth', $urlAddition);
    }

    # Makes sure the CV is set
    if (!isset($_POST['cv']) && !isset($_POST['cvDate'])) {
        $urlAddition = buildAddition('cv', $urlAddition);
    }

    # Makes sure the Cover Letter is set
    if (!isset($_POST['coverLetter']) && !isset($_POST['coverLetterDate'])) {
        $urlAddition = buildAddition('coverLetter', $urlAddition);
    }

    # Makes sure the references are set
    if (!isset($_POST['reference1'])) {
        $urlAddition = buildAddition('reference1', $urlAddition);
    }
    if (!isset($_POST['reference2'])) {
        $urlAddition = buildAddition('reference2', $urlAddition);
    }
    if (!isset($_POST['reference3'])) {
        $urlAddition = buildAddition('reference3', $urlAddition);
    }

    if ($urlAddition == null) {
        saveApp();



        $sql = "update APPLICATION set SUBMISSION_DATE = NOW() where NON_UI_PERSON_ID = '" . getPersonId() . "'";
        if (updateDB($sql)) {
            header(refresh("?success=" . urlencode("Your application was submitted successfully")));
            exit;
        } else {
            header(refresh("?error=" . urlencode("There was a problem submitting your application")));
            exit;
        }
    } else {
        saveApp();
        header(refresh($urlAddition . "?error=" . urlencode("Please complete all sections in red in order to submit")));
        exit;
    }
}

# Deletes the cover letter for an application
if (isset($_POST['delCover'])) {
    if (updateDB("update APPLICATION set COVER_LETTER = null where APP_ID = '" . getAppId() . "'")) {
        header(refresh("?success=" . urlencode("Your cover letter has been deleted")));
        exit;
    } else {
        header(refresh("?error=" . urlencode("There was a problem deleting your cover letter")));
        exit;
    }
}

# Deletes the cv for an application
if (isset($_POST['delCV'])) {
    if (updateDB("update APPLICATION set CV = null where APP_ID = '" . getAppId() . "'")) {
        header(refresh("?success=" . urlencode("Your Curriculum Vitae has been deleted")));
        exit;
    } else {
        header(refresh("?error=" . urlencode("There was a problem deleting your Curriculum Vitae")));
        exit;
    }
}

# Deletes the research statement for an application
if (isset($_POST['delRs'])) {
    if (updateDB("update APPLICATION set RESEARCH_STATEMENT = null where APP_ID = '" . getAppId() . "'")) {
        header(refresh("?success=" . urlencode("Your Research Statement has been deleted")));
        exit;
    } else {
        header(refresh("?error=" . urlencode("There was a problem deleting your Research Statement")));
        exit;
    }
}

#This sends out account information via email to all the references for an applicant.
function sendOutReferenceAccounts(){
    $q = "select NON_UI_PERSON_ID from LETTER_OF_REC where APPLICATION_APP_ID = '" . getAppId() . "'";
    $results = queryDB($q);

    while($result = nextRow($results)){
        $permissionSql = "insert into NON_UI_PERSON_LOOKUP (PERMISSIONS_PERMISSION, NON_UI_PERSON_ID) values
                          ('D','" . $result['NON_UI_PERSON_ID'] . "');";
        updateDB($permissionSql);
        $sql = "select EMAIL, SALT, RESET_PASS from NON_UI_PERSON where ID = '" . $result['NON_UI_PERSON_ID'] . "'";
        $emailResult = nextRow(queryDB($sql));

        $to = $emailResult['EMAIL'];
        $subject = "University of Iowa Faculty";
        $message = "Hello, \n This message has been auto-generated for you because you have been asked to be a reference
        for an applicant for a Computer Science faculty position. \n \n";
        $from = "ross-h-miller@uiowa.edu";
        $headers = "From:" . $from;

        #Only sends an email if the reference hasn't already signed in with an account.
        if($emailResult['RESET_PASS'] == '1'){
            $password = randomString(10);
            $message = $message . "Your login information: \n User Name:  " . $to
                . " \n Password: ";
            $insertSql = "update NON_UI_PERSON set PASSWORD = '" . hashString($password . $emailResult['SALT']) . "' where EMAIL = '" . $to . "'";
            updateDB($insertSql);
        } else {
            $message = $message . "you already have an account in the system please log in to upload your letters. \n";
        }

        $message = $message . "Login in at: http://webdev.cs.uiowa.edu/~rhmiller/Login.php";
        mail($to,$subject,$message,$headers);
    }
}

# This is the actual sql that saves the application
function saveApp()
{
    $appId = getAppId();

    #Check to see if we are actually creating an application or just saving
    if ($appId == null) {
        # Create a new application
        $sql = "insert into APPLICATION (FIRST_NAME, LAST_NAME, WEBSITE, PHD_DATE, CV, COVER_LETTER, RESEARCH_STATEMENT,
                                     UPLOAD_DATE, JOB_LISTING_LISTING_ID, NON_UI_PERSON_ID)
            values ('" . $_POST['fname'] . "', '" . $_POST['lname'] . "', '" . $_POST['website'] . "',
            '" . $_POST['phdYear'] . "-" . $_POST['phdMonth'] . "-01' , " . getFile("cv") . ", " . getFile("coverLetter")
            . ", " . getFile("researchStatement") . ", NOW(), '1', '" . getPersonId() . "')";

        $updateStatus = updateDB($sql);

        if(isset($_POST['reference1'])){
            createReferences($_POST['reference1']);
        }
        if(isset($_POST['reference2'])){
            createReferences($_POST['reference2']);
        }
        if(isset($_POST['reference3'])){
            createReferences($_POST['reference3']);
        }

        return $updateStatus;
    } else {
        # Update an existing an application
        $addition = "";

        if(isset($_FILES['cv'])){
            $addition = "CV = " . getFile("cv") . ", ";
        }
        if(isset($_FILES['coverLetter'])){
            $addition = $addition . "COVER_LETTER = " . getFile("coverLetter") . ", ";
        }
        if(isset($_FILES['coverLetter'])){
            $addition = $addition . "RESEARCH_STATEMENT = " . getFile("researchStatement") . ", ";
        }

        $sql = "update APPLICATION set
                FIRST_NAME = '" . $_POST['fname'] . "',
                LAST_NAME = '" . $_POST['lname'] . "',
                WEBSITE = '" . $_POST['website'] . "',
                PHD_DATE = '" . $_POST['phdYear'] . "-" . $_POST['phdMonth'] . "-01', "
                . $addition . "UPLOAD_DATE = NOW() where APP_ID = '" . getAppId() . "'";

        return updateDB($sql);
    }
}

# Updates the references so if a user changes a reference lets make those changes.
function updateReferences($email1, $email2, $email3)
{
    if ($row = nextRow(queryDB("select NUP.ID from NON_UI_PERSON NUP
            left outer join LETTER_OF_REC LOR on LOR.NON_UI_PERSON_ID = NUP.ID
            where LOR.APPLICATION_APP_ID = '" . getAppId() . "'
            and NUP.EMAIL = '" . $email1 . "'"))
    ) {
        $emails[0] = $email1;
    }

    if ($row = nextRow(queryDB("select NUP.ID from NON_UI_PERSON NUP
            left outer join LETTER_OF_REC LOR on LOR.NON_UI_PERSON_ID = NUP.ID
            where LOR.APPLICATION_APP_ID = '" . getAppId() . "'
            and NUP.EMAIL = '" . $email2 . "'"))
    ) {
        $emails[count($emails) + 1] = $email2;
    }

    if ($row = nextRow(queryDB("select NUP.ID from NON_UI_PERSON NUP
            left outer join LETTER_OF_REC LOR on LOR.NON_UI_PERSON_ID = NUP.ID
            where LOR.APPLICATION_APP_ID = '" . getAppId() . "'
            and NUP.EMAIL = '" . $email3 . "'"))
    ) {
        $emails[count($emails) + 1] = $email3;
    }


    $sql = "select NUP.ID from NON_UI_PERSON NUP
            left outer join LETTER_OF_REC LOR on LOR.NON_UI_PERSON_ID = NUP.ID
            where LOR.APPLICATION_APP_ID = '" . getAppId() . "'
            and NUP.EMAIL not in ('" . $email1 . "', '" . $email2 . "', '" . $email3 . "')";
    $results = queryDB($sql);
    $i = 0;

    while ($result = nextRow($results)) {
        if (!empty($emails[$i])) {
            updateDB("update NON_UI_PERSON set EMAIL = '" . $emails[$i] . "' where ID = '" . $result['ID'] . "'");
        }
    }
}

# Creates a reference
function createReferences($email)
{
    $q = "select * from NON_UI_PERSON where EMAIL = '" . $email . "'";

    if ($result = nextRow(queryDB($q))) {
        $personId = $result['ID'];
    } else {
        $personId = createPerson($email);
    }

    $insert = "insert into LETTER_OF_REC (APPLICATION_APP_ID, NON_UI_PERSON_ID)
               values ('" . getAppId() . "', '" . $personId . "')";
    updateDB($insert);
}

# Helper function that creates a NON_UI_PERSON. Returns the created persons id.
function createPerson($email)
{
    if($email != null){
    $salt = randomString(9);
    $passwordHash = randomString(45);
    $ip = $_SERVER['REMOTE_ADDR'];
    $nonce = randomString(45);

    updateDB("insert into NON_UI_PERSON (EMAIL, PASSWORD, IP_ADDRESS, NONCE, SALT, RESET_PASS) VALUES ('"
        . $email . "' , '" . $passwordHash . "' , '" . $ip . "' , '" . $nonce . "' , '" . $salt . "', '1')");

    $result = nextRow(queryDB("select ID from NON_UI_PERSON where EMAIL = '" . $email . "', '1'"));

    return $result['ID'];
    } else {
        return null;
    }
}

# Helper function that returns the application id for the person who is logged in and null if no application exists
function getAppId()
{
    $sql = "select APP_ID from APPLICATION where NON_UI_PERSON_ID = '" . getPersonId() . "'";
    $results = queryDB($sql);
    if ($result = nextRow($results)) {
        return $result['APP_ID'];
    } else {
        return null;
    }
}


# Helper method to build the variables to return to the
function buildAddition($getVar, $urlAddition)
{
    if ($urlAddition == null) {
        return "?" . $getVar . "=1";
    } else {
        return $urlAddition . "&" . $getVar . "=1";
    }
}

# This is a helper function that coverts files into a blob for saving
function getFile($file)
{
    if ($_FILES[$file]['size'] > 0) {
        $tmpName = $_FILES[$file]['tmp_name'];
        $fileSize = $_FILES[$file]['size'];
        $fileType = $_FILES[$file]['type'];

        # Check to make sure the file size is not too big for the database.
        if ($fileSize >= 64000) {
            header(refresh("?error=" . urlencode("File is too large to upload. Please choose a smaller file.") .
                "&fname=" . urlencode($_POST['fname']) . "&lname=" . urlencode($_POST['lname'])));
            exit;
        }

        # Check to make sure the file is a pdf
        if ($fileType != "application/pdf") {
            header(refresh("?warning=" . urlencode("File must be in pdf format.") .
                "&fname=" . urlencode($_POST['fname']) . "&lname=" . urlencode($_POST['lname'])));
            exit;
        }

        # Opens the file and prepares it to be saved.
        $fp = fopen($tmpName, 'r');
        $content = fread($fp, filesize($tmpName));
        $content = addslashes($content);
        fclose($fp);

        return "'" . $content . "'";
    }

    return "null";
}

# Helper function that generates the month date picker
function monthDatePicker($preSelectedMonth = "05")
{
    $months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");

    echo "<select name='phdMonth' id='phdMonth'> \n";
    for ($i = 0; $i < count($months); $i++) {
        $monthNum = str_pad($i + 1, 2, "0", STR_PAD_LEFT);
        if ($monthNum == $preSelectedMonth) {
            echo "<option selected value='$monthNum'>$months[$i]</option>";
        } else {
            echo "<option value='$monthNum'>$months[$i]</option>";
        }
    }
    echo "</select>";
}

# Helper function that generates the year date picker
function yearDatePicker($preSelectedYear = '2012')
{
    echo "<select name='phdYear' id='phdYear'>";
    for ($year = 2013; $year >= 1930; $year--) {
        if ($preSelectedYear == $year) {
            echo "<option selected value='$year'>$year</option>";
        } else {
            echo "<option value='$year'>$year</option>";
        }
    }
    echo "</select>";
}

?>

<!-- Create each field for applicant to upload their information-->
<div class="container">
    <div class="row">
        <div class="span10">
            <form class="form-horizontal" style="padding-right: 2%; padding-bottom: 5%;" enctype="multipart/form-data"
                  method="POST" action="<?php echo myURL(); ?>">
                <h3>Application</h3>

                <div class="control-group<?php if (isset($_GET['fname'])) {
                    echo " error";
                } ?>">
                    <label class="control-label" for="fname">First Name *</label>

                    <div class="controls">
                        <input <?php echo $disabled;?> type="text" name="fname" id="fname" placeholder="First Name" size="45"
                               value="<?php if (isset($_GET['fname'])) {
                                   echo urldecode($_GET['fname']);
                               } else {
                                   if ($app != null) {
                                       echo $app->getFirstName();
                                   } else {
                                       echo "";
                                   }
                               } ?>">
                    </div>
                </div>
                <div class="control-group <?php if (isset($_GET['lname'])) {
                    echo "error";
                } ?>">
                    <label class="control-label" for="lname">Last Name *</label>

                    <div class="controls">
                        <input <?php echo $disabled;?> type="text" name="lname" id="lname" placeholder="Last Name" size="45"
                            <?php if (isset($_GET['lname'])) {
                            echo "value='" . urldecode($_GET['lname']) . "'";
                        } else {
                            if ($app != null) {
                                echo "value='" . $app->getLastName() . "'";
                            }
                        } ?> >
                    </div>
                </div>
                <div class="control-group <?php if (isset($_GET['website'])) {
                    echo "error";
                } ?>">
                    <label class="control-label" for="website">Website *</label>

                    <div class="controls">
                        <input <?php echo $disabled;?> type="text" name="website" id="website" placeholder="Website" size="45"
                            <?php if ($app != null) {
                            echo "value='" . $app->getWebsite() . "'";
                        } ?> >
                    </div>
                </div>
                <div class="control-group <?php if (isset($_GET['phdYear'])) {
                    echo "error";
                } ?>">
                    <label class="control-label" for="phdYear">Phd Year *</label>

                    <div class="controls">
                        <?php if ($app != null) {
                        yearDatePicker($app->getPhdYear());
                    } else {
                        yearDatePicker();
                    } ?>
                    </div>
                </div>
                <div class="control-group <?php if (isset($_GET['phdMonth'])) {
                    echo "error";
                } ?>">
                    <label class="control-label" for="phdMonth">Phd Month *</label>

                    <div class="controls">
                        <?php if ($app != null) {
                        monthDatePicker($app->getPhdMonth());
                    } else {
                        monthDatePicker();
                    } ?>
                    </div>
                </div>
                <div class="control-group <?php if (isset($_GET['reference1'])) {
                    echo "error";
                } ?>">
                    <label class="control-label" for="reference1">Reference Email #1 *</label>

                    <div class="controls">
                        <input <?php echo $disabled;?> type="text" name="reference1" id="reference1" placeholder="Reference Email #1" size="45"
                            <?php if ($app != null) {
                            echo "value='" . $app->getReference1() . "'";
                        } ?> >
                    </div>
                </div>
                <div class="control-group <?php if (isset($_GET['reference2'])) {
                    echo "error";
                } ?>">
                    <label class="control-label" for="reference2">Reference Email #2 *</label>

                    <div class="controls">
                        <input <?php echo $disabled;?> type="text" name="reference2" id="reference2" placeholder="Reference Email #2" size="45"
                            <?php if ($app != null) {
                            echo "value='" . $app->getReference2() . "'";
                        } ?> >
                    </div>
                </div>
                <div class="control-group <?php if (isset($_GET['reference3'])) {
                    echo "error";
                } ?>">
                    <label class="control-label" for="reference3">Reference Email #3 *</label>

                    <div class="controls">
                        <input <?php echo $disabled;?> type="text" name="reference3" id="reference3" placeholder="Reference Email #3" size="45"
                            <?php if ($app != null) {
                            echo "value='" . $app->getReference3() . "'";
                        } ?> >
                    </div>
                </div>
                <div class="control-group <?php if (isset($_GET['cv'])) {
                    echo "error";
                } ?>">
                    <label class="control-label" for="cv">Curriculum Vitae *</label>

                    <div class="controls">
                        <?php
                        if ($app != null) {
                            if ($app->getCvDate() == null) {
                                echo "<input $disabled type='file' name='cv' id='cv' placeholder='Curriculum Vitae'/>";
                            } else {
                                echo "<input type='hidden' name='cvDate' value='1' />";
                                echo "<span id='cv'>" . $app->getCvDate() . "</span>&nbsp;&nbsp;<br/>";
                                echo "<button $disabled class='btn btn-danger' name='delCV' type='submit'>Delete</button>&nbsp;";
                                echo "<a href='ViewApplicationFile.php?resourceType=cv' class='btn btn-primary' target='_blank'>View</a>";
                            }
                        } else {
                            echo "<input $disabled type='file' name='cv' id='cv' placeholder='Curriculum Vitae'/>";
                        } ?>
                    </div>
                </div>
                <div class="control-group <?php if (isset($_GET['coverLetter'])) {
                    echo "error";
                } ?>">
                    <label class="control-label" for="coverLetter">Cover Letter *</label>

                    <div class="controls">
                        <?php
                        if ($app != null) {
                            if ($app->getCoverLetterDate() == null) {
                                echo "<input $disabled type='file' name='coverLetter' id='coverLetter' placeholder='Cover Letter'>";
                            } else {
                                echo "<input type='hidden' name='coverLetterDate' value='1' />";
                                echo "<span id='coverLetter'>" . $app->getCoverLetterDate() . "</span>&nbsp;&nbsp;<br/>";
                                echo "<button $disabled class='btn btn-danger' name='delCover' type='submit'>Delete</button>&nbsp;";
                                echo "<a href='ViewApplicationFile.php?resourceType=coverLetter&appId=" . getAppId() . "' class='btn btn-primary' target='_blank'>View</a>";
                            }
                        } else {
                            echo "<input $disabled type='file' name='coverLetter' id='coverLetter' placeholder='Cover Letter'>";
                        } ?>
                    </div>
                </div>
                <div class="control-group <?php if (isset($_GET['researchStatement'])) {
                    echo "error";
                } ?>">
                    <label class="control-label" for="researchStatement">Research Statement</label>

                    <div class="controls">
                        <?php
                        if ($app != null) {
                            if ($app->getResearchStatementDate() == null) {
                                echo "<input $disabled type='file' name='researchStatement' id='researchStatement' placeholder='Research Statement'>";
                            } else {
                                echo "<span id='researchStatement'>" . $app->getResearchStatementDate() . "</span>&nbsp;&nbsp;<br/>";
                                echo "<button $disabled class='btn btn-danger' name='delRs' type='submit'>Delete</button>&nbsp;";
                                echo "<a href='ViewApplicationFile.php?resourceType=researchStatement' class='btn btn-primary' target='_blank'>View</a>";
                            }
                        } else {
                            echo "<input $disabled type='file' name='researchStatement' id='researchStatement' placeholder='Research Statement'>";
                        }?>
                    </div>
                </div>
                <div class="control-group">
                    <button class="btn btn-primary" <?php echo $disabled;?> name="save" type="submit">Save & Continue Later</button>
                </div>
                <div class="control-group pull-right">
                    <button class="btn btn-success" <?php echo $disabled;?> name="submit" type="submit">Submit Application</button>
                </div>
            </form>
        </div>
    </div>
</div>