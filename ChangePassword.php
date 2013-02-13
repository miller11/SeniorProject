<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ross
 * Date: 10/5/12
 */
include_once 'Header.php';

echo '<div class="container"><br/> <br/>';
?>

<form class="form-horizontal" method="POST" action="<?php echo myURL(); ?>">


    <div class="control-group">
        <label class="control-label" for="email">Email</label>

        <div class="controls">
            <input type="text" id="email" name="email" placeholder="Email" value="<?php if (isset($_GET['email'])) {
                echo $_GET['email'];
            } ?>">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="passwordOrig">Old Password</label>

        <div class="controls">
            <input type="password" id="passwordOrig" name="passwordOrig" placeholder="Old Password">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="password">New Password</label>

        <div class="controls">
            <input type="password" id="password" name="password" placeholder="New Password">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="password2">Confirm New Password</label>

        <div class="controls">
            <input type="password" id="password2" name="password2" placeholder="Confirm New Password">
        </div>
    </div>
    <div class="control-group">
        <div class="controls">
            <button type="submit" name="submit" class="btn btn-success">Change Password</button>
        </div>
    </div>

</form>
</div>
</html>

<?php
if (isset($_POST['submit'])) {

    # Just clearing the form; resubmit.
    if ($_POST['submit'] == 'Clear') {
        # Go back to home.
        header(refresh());
        exit;
    }

    ################## Begin Validation ######################

    #Checks to see if the email is filled out
    if ($_POST['email'] == '') {
        header(refresh('?warning=' . urlencode('Please enter a valid email.') . '&email=' . urldecode($_POST['email'])));
        exit;
    }

    #Checks to see if the password is filled out
    if ($_POST['passwordOrig'] == '') {
        header(refresh('?warning=' . urlencode('Please fill in the original password blank.') . '&email=' . urldecode($_POST['email'])));
        exit;
    }

    #Checks to see if the password is filled out
    if ($_POST['password'] == '') {
        header(refresh('?warning=' . urlencode('Please fill in the password blank.') . '&email=' . urldecode($_POST['email'])));
        exit;
    }

    #Checks to see if the second password is filled out
    if ($_POST['password2'] == '') {
        header(refresh('?warning=' . urlencode('Please enter a matching password.') . '&email=' . urldecode($_POST['email'])));
        exit;
    }

    #Checks to see if the passwords match
    if ($_POST['password2'] != $_POST['password']) {
        header(refresh('?warning=' . urlencode('Please enter a matching password.') . '&email=' . urldecode($_POST['email'])));
        exit;
    }

    #Checks to see if their old password matches
    $res = nextRow(queryDB("select PASSWORD, SALT from NON_UI_PERSON where EMAIL = '" . $_POST['email'] . "'"));
    if (hashString($res['SALT'] . $_POST['passwordOrig']) == $res['PASSWORD']) {
        header(refresh('?warning=' . urlencode('Old Password was incorrect') . '&email=' . urldecode($_POST['email'])));
        exit;
    }

    #################### End Validation ########################
    #################### Begin signing up user #################
    $email = $_POST['email'];
    $salt = randomString(9);
    $passwordHash = hashString($_POST['password'] . $salt);
    $nonce = randomString(45);

    if (updateDB("update NON_UI_PERSON set PASSWORD =  '" . $passwordHash . "', NONCE = '" . $nonce . "',
                    SALT = '" . $salt . "' , RESET_PASS = '0' where EMAIL = '" . $_POST['email'] . "'")
    ) {
        $result = nextRow(queryDB("select ID from NON_UI_PERSON where EMAIL = '" . $email . "'"));


        #After creating the account we log them in.
        #Now lets make some cookies (nom nom nom)
        setcookie('pid', $result['ID'], time() + 60 * 60 * 2);
        setcookie('creds', hashString($result['ID'] . $nonce), time() + 60 * 60 * 2);

        #Redirects them to the home page with a success message.
        header(redirect("index.php", '?success=Your password has been updated.'));
    } else {
        header(refresh('?error=' . urlencode('There was a problem updating your account.')));
    }
}
?>