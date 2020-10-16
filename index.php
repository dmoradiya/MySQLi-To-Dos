<?php


    require 'constants.php';

    // Create Connection
    $connection = new mysqli( HOST, USER, PASSWORD, DATABASE );
    if( $connection->connect_errno ){
        die( 'Connection failed:' . $connection->connect_error );
    }


    //  All constants
    $category_select_options = null;
    $things_to_do = null;
    $message = null;
    
   
    
    // Select only TaskCategoryID and TaskCategory From the Thingstodo Table
    $category_sql = "SELECT * FROM taskcategory";
    
    // Get the Result query Object
    $category_result = $connection->query($category_sql);
    if( !$category_result ){
        echo "something went wrong with the query";
        exit();
    }

    // Check for Number of rows if no Row found then display message
    if( $category_result->num_rows > 0 ){
            while( $category = $category_result->fetch_assoc() ) {
                $category_select_options .= sprintf('<option value="%d">%s</option>',
                    $category['TaskCategoryID'],
                    $category['TaskCategory']
                );
            }
    } 

    
    
    // Select From the thingstodo Table
    $sql = "SELECT * FROM thingstodo";
    
    // Get the Result query Object
    $result = $connection->query($sql);
    if( !$result ){
        echo "something went wrong with the query";
        exit();
    }

    // Check for Number of rows if no Row found then display message
    if( $result->num_rows === 0 ){
        $things_to_do = "<tr><td colspan='5'>There is no Active Task</td><tr>";    
    
    } else { // Get data from each row
        while( $row = $result->fetch_assoc() ){            
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


    if( $_POST ){
        if( $insert = $connection->prepare("INSERT INTO thingstodo(Task, DueDate, TaskCategoryID)VALUE(?, ?, ?)") ){
            if( $insert->bind_param("ssi", $_POST['task'], $_POST['date'], $_POST['task_category']) ){
                if( $insert->execute() ){
                    $message = "Your task added...";
                } else {
                    exit("There was a problem with the execute");
                }
            } else {
                exit("There was a problem with the bind_param");
            }
        } else {
            exit("There was a problem with the prepare statement");
        }
        $insert->close();
        
        echo '<pre>';
        echo print_r($_POST);
        echo '</pre>';

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
    
    <!-- Add Todo Start -->
    <h2>Add Todo</h2>

        
    <form action="#" method="POST" enctype="multipart/form-data">
        <p>
            <label for="task">Task</label>
            <input type="text" name="task" id="task">
        </p>
        <p>
            <label for="date">Due date</label>
            <input type="date" name="date" id="date" min="2020-01-01" max="2021-01-01">
        </p>
        <p>
            <label for="task_category">Task Category</label>
            <select name="task_category" id="task_category">
                <option value="">Pick one</option>
                <?php echo $category_select_options; ?>
            </select>
        </p>
        <p>
            <input type="submit" value="Add new task">
        </p>
    </form>
    <?php if($message) echo $message; ?>
    <!-- Add Todo end -->
    
    

    <!-- Things to do start -->
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
        <!-- Things to do end -->
        
    </table>
    
</body>
</html>