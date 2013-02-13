<?php
include_once 'Header.php';
?>

<div class="container"><br/> <br/>

    <div class="span7">

        <!--This is the form for having the user log in-->
        <?php
        echo '<form class="form-horizontal" method="POST" action="' . myURL() . '">'
        ?>
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
            <div class="controls">

                <button type="submit" name="submit" class="btn btn-success">Sign in</button>
            </div>
        </div>
        <br/>

        <div class="span2"><a href="HawkIdLogin.php">Admin Login</a></div>
        <div class="span2"><a href="ForgotPassword.php">Forgot Password</a></div>
        <div class="span2"><a href="NewUser.php">Create Account</a></div>

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

    #Checks to see if the email is filled out
    if ($_POST['email'] == '') {
        header(refresh('?warning=' . urlencode('Please enter a valid email.')));
        exit;
    }

    #Checks to make sure the password is filled in
    if ($_POST['password'] == '') {
        header(refresh('?warning=' . urlencode('Please fill in the password blank.')));
        exit;
    }

    #Checks to see if a user exists for the given email.
    $results = queryDB("select * from NON_UI_PERSON where EMAIL = '" . $_POST['email'] . "'");
    if (!($result = nextRow($results))) {
        header(refresh('?warning=' . urlencode('No user exists with this email.')));
        exit;
    }

    #If everything is good up until here authenticate.
    if ($result['PASSWORD'] == hashString($_POST['password'] . $result['SALT'])) {
        if($result['RESET_PASS'] == '1'){
            header(redirect('ChangePassword.php?email=' . $result['EMAIL'], "&warning=" . urlencode('Please reset your password before trying to log in')));
            exit;
        }


        #Good Login. Now set a new nonce
        $nonce = randomString(45);
        updateDB("update NON_UI_PERSON set NONCE = '" . $nonce . "' where ID = '" . $result["ID"] . "'");

        #Now lets make some cookies (nom nom nom)
        setcookie('pid', $result['ID'], time() + 60 * 60 * 2);
        setcookie('creds', hashString($result['ID'] . $nonce), time() + 60 * 60 * 2);

        #redirect them to the home page
        header(redirect("index.php"));
        exit;
    } else {
        #Bad Login allow them to try again
        header(refresh('?error=' . urlencode('Email or password were incorrect. Please try again.')));
        exit;
    }
}
?>