<?php
    require 'constants.php';
    $things_to_do = null;

    // Create Connection
    $connection = new mysqli( HOST, USER, PASSWORD, DATABASE );
    if( $connection->connect_errno ){
        die( 'Connection failed:' . $connection->connect_error );
    }
    
    // Select From the Table
    $sql = "SELECT * FROM thingstodo";
    
    // Get the Result query Object
    $result = $connection->query($sql);
    if( !$result ){
        echo "something went wrong with the query";
        exit();
    }

    // Check for Number of rows
    if( $result->num_rows === 0 ){
        $things_to_do = "<tr><td colspan='5'>There is no Active Task</td><tr>";    
    } else {
        while( $row = $result->fetch_assoc() ){
            echo print_r($row);
            $things_to_do .= sprintf('  
                <tr>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>
                        <a href="complete.php?task_id=%d">Complete</a> | 
                        <a href="delete.php?task_id=%d">Delete</a>
                    </td>
                </tr>
                ',
                $row['TaskCategory'],
                $row['Task'],
                $row['DueDate'],
                $row['ThingstodoID'],
                $row['ThingstodoID']                
                
            );
        }
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
        <?php echo $things_to_do; ?>
        
    </table>
    
</body>
</html>