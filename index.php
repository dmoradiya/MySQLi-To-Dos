<?php
    require 'constants.php';

    // Create Connection
    $connection = new mysqli( HOST, USER, PASSWORD, DATABASE );
    if( $connection->connect_errno ){
        die( 'Connection failed:' . $connection->connect_error );
    }


    $connection->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My ToDo List</title>
</head>
<body>
    <h1>My ToDo List</h1>


    <h2>Thing to do<h2>
    <table>
        <tr>
            <th>TaskCategory</th>    
            <th>Task</th>
            <th>DueDate</th>
            <th>Complete</th>
            <th>Delete</th>
        </tr>
        <!-- <?php echo $staff_members; ?> -->
        
    </table>
    
</body>
</html>