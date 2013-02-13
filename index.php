<?php
include_once 'Header.php';
?>
<body>
<div class="container">
    <a href="http://s1099.beta.photobucket.com/user/lauragehrt/media/iowa.jpg.html" target="_blank"><img src="http://i1099.photobucket.com/albums/g393/lauragehrt/iowa.jpg" border="0" alt="Photobucket"/></a>
    <!-- table containing the open job listings -->
    <div class="row">
        <div class="span12">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>Date Posted</th>
                    <th>Job Description</th>
                    <th> </th>
                </tr>
                </thead>
                <tbody>

    <?php


    #populate the table show date posted and job description.
        $dateResults = "select DATE_FORMAT(DATE_POSTED,'%d %b %Y') as DATE_POSTED, JOB_DESCRIPTION from JOB_LISTING where LISTING_ID = '1'";

        $dateResult = nextRow(queryDB($dateResults));
        echo "<tr> \n";
            echo "<td>" . $dateResult['DATE_POSTED'] . "</td> \n";
            echo "<td>" . $dateResult['JOB_DESCRIPTION'] . "</td> \n";
            echo "<td><a class='btn btn-primary' href='Application.php?'>Apply Here</a>";
        echo "</tr>";


    ?>


</div>
</body>
