<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ross
 * Date: 10/5/12
 */
include_once 'Header.php';

echo '<div class="container"><br/> <br/>';
echo '<form class="form-horizontal" method="POST" action="' . myURL() . '">' ?>


<div class="control-group">
    <label class="control-label" for="email">Email</label>

    <div class="controls">
        <input type="text" id="email" name="email" placeholder="Email">
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="password">Password</label>

    <div class="controls">
        <input type="password" id="password" name="password" placeholder="Password">
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="password2">Confirm Password</label>

    <div class="controls">
        <input type="password" id="password2" name="password2" placeholder="Confirm Password">
    </div>
</div>
<div class="control-group">
    <div class="controls">
        <button type="submit" name="submit" class="btn btn-success">Create Account</button>
    </div>
</div>
</div>
</form>
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
        header(refresh('?warning=' . urlencode('Please enter a valid email.')));
        exit;
    }

    #Checks to see if the password is filled out
    if ($_POST['password'] == '') {
        header(refresh('?warning=' . urlencode('Please fill in the password blank.')));
        exit;
    }

    #Checks to see if the second password is filled out
    if ($_POST['password2'] == '') {
        header(refresh('?warning=' . urlencode('Please enter a matching password.')));
        exit;
    }

    #Checks to see if the passwords match
    if ($_POST['password2'] != $_POST['password']) {
        header(refresh('?warning=' . urlencode('Please enter a matching password.')));
        exit;
    }

    #Checks to see if a user exists for the given email.
    $res = queryDB("select * from NON_UI_PERSON where EMAIL = '" . $_POST['email'] . "'");
    if ($result = nextRow($res)) {
        header(refresh('?warning=' . urlencode('User already exists for this email.')));
        exit;
    }

    #################### End Validation ########################

    #################### Begin signing up user #################
    $email = $_POST['email'];
    $salt = randomString(9);
    $passwordHash = hashString($_POST['password'] . $salt);
    $ip = $_SERVER['REMOTE_ADDR'];
    $nonce = randomString(45);

    if (updateDB("insert into NON_UI_PERSON (EMAIL, PASSWORD, IP_ADDRESS, NONCE, SALT) VALUES ('"
        . $email . "' , '" . $passwordHash . "' , '" . $ip . "' , '" . $nonce . "' , '" . $salt . "')")
    ) {
        $result = nextRow(queryDB("select ID from NON_UI_PERSON where EMAIL = '" . $email . "'"));

        updateDB("insert into NON_UI_PERSON_LOOKUP (NON_UI_PERSON_ID, PERMISSIONS_PERMISSION) values ('"
                . $result['ID'] . "', 'E')");
        

        #After creating the account we log them in.
        #Now lets make some cookies (nom nom nom)
        setcookie('pid', $result['ID'], time() +60*60*2);
        setcookie('creds', hashString($result['ID'] . $nonce), time() +60*60*2);

        #Redirects them to the home page with a success message.
        header(redirect("index.php", '?success=Your account has been Created.'));
        exit;
    } else {
        header(refresh('?error=' . urlencode('There was a problem creating your account.')));
        exit;
    }
}
?>