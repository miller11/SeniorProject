<?php
include_once 'Header.php';

/*
 * User: Ross
 * Date: 12/11/12
 */


if(isset($_POST['submit'])){
    $sql = "select SALT from NON_UI_PERSON where EMAIL = '" . $_POST['email'] . "'";

    if($emailResult = nextRow(queryDB($sql))){


        $to = $_POST['email'];
        $subject = "University of Iowa Faculty";
        $message = "Hello, \n This a password reset from the University of Iowa Faculty Hiring CS website. \n \n";
        $from = "ross-h-miller@uiowa.edu";
        $headers = "From:" . $from;

        $password = randomString(10);
        $message = $message . "Your new login information: \n User Name:  " . $to
            . " \n Password: " . $password . "\n";
        $insertSql = "update NON_UI_PERSON set PASSWORD = '" . hashString($password . $emailResult['SALT']) . "', RESET_PASS = '1' where EMAIL = '" . $to . "'";
        updateDB($insertSql);

        $message = $message . "Login in at: http://webdev.cs.uiowa.edu/~rhmiller/ChangePassword.php?email=" . $to;
        mail($to,$subject,$message,$headers);
        header(redirect('ChangePassword.php','?email=' . urlencode($to) . "&success=" . urlencode("Your password has been reset please check your email.")));
        exit;
    } else {
       header(refresh("?error=") . urlencode("Email provided was not found in our system"));
        exit;
    }
}

?>

<div class="container">
    <div class="row">
        <h2>Forgot Password</h2>
    </div>
    <div class="row">
        <div class="span8">
            <form class="form-horizontal" method="POST" action="<?php echo myURL();?>">
                <div class="control-group">
                    <label class="control-label" for="email">Email</label>

                    <div class="controls">
                        <input type="text" id="email" name="email" placeholder="Email">
                    </div>
                </div>
                <div class="control-group">
                    <div class="controls">
                        <button type="submit" name="submit" class="btn btn-success">Send</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>