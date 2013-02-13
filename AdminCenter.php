<?php
include_once 'Header.php';
if (!hasPermission("A")) {
    header(redirect("index.php", "?warning=" . urlencode("You do not have access to this page")));
}
?>
<div class="container">

    <br/>

    <div class="row">
        <div class="span5">
            <form class="form-inline" method="POST" action="<?php echo myURL(); ?>">


                <input style="line-height: 200%;" type="text" id="hawkid" class="input-small" name="hawkid"
                       placeholder="HawkID">
                &nbsp;&nbsp;&nbsp;

                <button type="submit" name="submit" class="btn btn-success">Add</button>

            </form>
        </div>
        <div class="span2 offset1">
            <a href="CSVdownload.php" class="btn btn-inverse">Download CSV File</a>
        </div>
    </div>


    <div class="row">
        <div class="span8">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>ID#</th>
                    <th>HawkID</th>
                    <th>Permissions</th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $results = queryDB("select ID, HAWK_ID from UI_PERSON");
                while ($result = nextRow($results)) {
                    echo "<tr> \n";
                    echo "<td>" . $result['ID'] . "</td> \n";
                    echo "<td>" . $result['HAWK_ID'] . "</td> \n";
                    echo "<td>" . getAllPermissions($result['ID']) . "</td> \n";
                    echo "<td><a href='" . myURL("?delete=1&did=" . $result['ID']) . "' name='delete' class='btn btn-danger''>Remove</a></td>";
                    echo "<td><a href='EditAdmins.php?eid=" . $result['ID'] . "' name='edit' class='btn btn-primary''>Edit Permissions</a></td>";
                    echo "</tr> \n";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php

# This user
if (isset($_POST['submit'])) {
    if (updateDB("insert into UI_PERSON (HAWK_ID, NONCE) values ('" . $_POST['hawkid'] . "', '" . randomString(45) . "')")) {
        header(refresh("?success=" . urlencode($_POST['hawkid'] . " was added to the system.")));
        exit;
    } else {
        header(refresh("?error=" . urlencode("Unable to add " . $_POST['hawkid'] . " the system.")));
        exit;
    }
}

# This function deletes an admin user from the list
if (isset($_GET['delete']) && isset($_GET['did'])) {
    if ($_GET['did'] != '1') {
        if (updateDB("delete from UI_PERSON where ID = '" . $_GET['did'] . "'")) {
            header(refresh("?info=" . urlencode("The user was deleted from the system.")));
            exit;
        } else {
            header(refresh("?error=" . urlencode("Unable to delete the user from the system.")));
            exit;
        }
    } else {
        header(refresh("?error=" . urlencode("You cannot delete this user from the system.")));
        exit;
    }
}


function getAllPermissions($userID)
{
    $resultSet = queryDB("select * from UI_PERSON_LOOKUP where UI_PERSON_ID = '" . $userID . "'");
    $permissions = " ";

    while ($row = nextRow($resultSet)) {
        $permissions = $permissions . $row['PERMISSIONS_PERMISSION'] . ",";
    }
    return substr($permissions, 0, -1);
}

?>