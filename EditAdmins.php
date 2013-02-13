<?php
include_once 'Header.php';

if (!hasPermission("A")) {
    header(redirect("index.php", "?warning=" . urlencode("You do not have access to this page")));
}
?>

<div class="container">
    <div class="row">
        <div class="span4 offset1">
            <strong>Editing Permissions For: <?php echo getHawkId();?></strong>
        </div>
    </div>

    <div class="row">
        <div class="span4">
            <form class="form-inline" method="POST" action="<?php echo myURL(); ?>">
                <label>
                    <select name="permission">
                        <?php
                        $results = queryDB("select PERMISSION from PERMISSIONS where PERMISSION not in
                                            (select PERMISSIONS_PERMISSION from UI_PERSON_LOOKUP
                                             where UI_PERSON_ID = '" . $_GET['eid'] . "')");
                        while ($result = nextRow($results)) {
                            echo "<option value='" . $result['PERMISSION'] . "'>" . $result['PERMISSION'] . "</option>";
                        }
                        ?>
                    </select>
                </label>

                <input type="hidden" name="eid" value="<?php echo $_GET['eid']?>"/>
                <button type="submit" name="add" class="btn btn-success">Add</button>

            </form>
        </div>
    </div>

    <div class="row">
        <div class="span6">
            <strong>Current Permissions:</strong>
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Permission</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $results = queryDB("select * from UI_PERSON_LOOKUP where UI_PERSON_ID = '" . $_GET['eid'] . "'");
                while ($result = nextRow($results)) {
                    echo "<tr> \n";
                    echo "<td>" . $result['PERMISSIONS_PERMISSION'] . "<td> \n";
                    echo "<td><a href='EditAdmins.php?eid=" . $_GET['eid'] . "&perm=" . $result['PERMISSIONS_PERMISSION']
                        . "&remove=1' name='remove' class='btn btn-danger btn-small'>Remove Permission</a></td>";
                    echo "<td> \n";
                }

                ?>
                </tbody>
            </table>
        </div>
    </div>


</div>

<?php
# Function that adds the permission to the ui_person_lookup table
if (isset($_POST['add'])) {
    if (updateDB("insert into UI_PERSON_LOOKUP (UI_PERSON_ID, PERMISSIONS_PERMISSION)
                values ('" . $_POST['eid'] . "', '" . $_POST['permission'] . "') ")
    ) {
        header(redirect('AdminCenter.php', '?info=' . urlencode('Permission was added')));
        exit;

    } else {
        header(refresh('?eid=' . $_POST['eid'] . '&error=' . urlencode('Unable to add this permission')));
        exit;
    }
}

# Function that removes a person's given permission
if (isset($_GET['remove'])) {
    if ($_GET['eid'] == '1' && $_GET['perm'] == 'A') {
        header(refresh('?error=' . urlencode('You cannot delete this permission for this user') . "&eid=" . $_GET['eid']));
        exit;

    } else {
        if (updateDB("delete from UI_PERSON_LOOKUP where UI_PERSON_ID = '" . $_GET['eid'] . "' and PERMISSIONS_PERMISSION = '" . $_GET['perm'] . "'")) {
            header(refresh('?info=' . urlencode('Permission Was Removed') . "&eid=" . $_GET['eid']));
            exit;
        } else {
            header(refresh('?error=' . urlencode('Unable to add this permission') . "&eid=" . $_GET['eid']));
            exit;
        }
    }
}

# Function that returns the person that's being edited hawkid.
function getHawkId()
{
    if (isset($_GET['eid'])) {
        $results = queryDB("select HAWK_ID from UI_PERSON where ID = '" . $_GET['eid'] . "'");
        if ($result = nextRow($results)) {
            return $result['HAWK_ID'];
        } else {
            header(redirect('AdminCenter.php', '?warning=' . urlencode('User not found')));
            exit;
        }
    } else {
//        header(redirect('AdminCenter.php', '?warning=' . urlencode('User not found')));
//        exit;
    }
}

?>