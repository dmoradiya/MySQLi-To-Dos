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
    $overdue = null;
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

    /**
     * ****************************Things To Do Start************************
     */
    
    // Select From the thingstodo Table
    $thingstodo_sql = "SELECT TaskCategory, Task, DueDate, ThingstodoID FROM thingstodo INNER JOIN taskcategory USING(TaskCategoryID)";
    
    // Get the Result query Object
    $thingstodo_result = $connection->query($thingstodo_sql);
    if( !$thingstodo_result ){
        echo "something went wrong with the query";
        exit();
    }
        
    // Check for Number of rows if no Row found then display message
    if( $thingstodo_result->num_rows === 0 ){
        $things_to_do = "<tr><td colspan='5'>There is no Active Task</td><tr>";    
    
    } else { // Get data from each row
        while( $row = $thingstodo_result->fetch_assoc() ){            
            
            $things_to_do .= sprintf('  
                <tr>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>
                        <a href="index.php?task_id=%d">Complete</a> | 
                        <a href="index.php?task_id=%d">Delete</a>
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



    /**
    ********************************** Overdue start **********************
    */

    // Overdue Insert data from thingstodo Table

    $overdue_insert_sql = "INSERT INTO overdue (ThingstodoID,TaskCategoryID, TaskCategory,Task,DueDate) 
    SELECT ThingstodoID,TaskCategoryID, TaskCategory,Task,DueDate FROM thingstodo 
    INNER JOIN taskcategory USING(TaskCategoryID)
    WHERE DueDate < NOW()";
    if( !$overdue_insert_result = $connection->query($overdue_insert_sql) ) {
        die("Could not Insert to the overdue database table");
    }
        

    // Delete Duplicate from the Things to do    
    $thingstodo_duplicate_delete_sql = "DELETE FROM thingstodo WHERE DueDate < NOW()";
    if( !$thingstodo_duplicate_delete_result = $connection->query($thingstodo_duplicate_delete_sql) ) {
        die("Could not Delete from the thingstodo database table");
    }
    echo '<pre>';
    echo print_r($thingstodo_duplicate_delete_result);
    echo '</pre>';
    // Select From the Overdue Table
    $overdue_sql = "SELECT * FROM overdue";
    
    // Get the Result query Object
    $overdue_result = $connection->query($overdue_sql);
    if( !$overdue_result ){
        echo "something went wrong with the query";
        exit();
    }
    
    // Check for Number of rows if no Row found then display message
    if( $overdue_result->num_rows === 0 ){
        $overdue = "<tr><td colspan='5'>There is no Overdue Task</td><tr>";    
    
    } else { // Get data from each row
        while( $row = $overdue_result->fetch_assoc() ){  

            $overdue .= sprintf('  
                <tr>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>
                        <a href="index.php?task_id=%d">Complete</a> | 
                        <a href="index.php?task_id=%d">Delete</a>
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
        if( empty($_POST['task']) ){
            echo $message = 'Please ADD Task!';
        }else {
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
        }
       
        
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
    </table>
    <!-- Things to do end --> 


    <!-- Overdue start -->
    <h2>Overdue<h2>
    <table>
        <tr>
            <th>TaskCategory</th>    
            <th>Task</th>
            <th>DueDate</th>
            <th>Complete</th>
            <th>Delete</th>
        </tr>
        <?php echo $overdue; ?>            
    </table>
    

    
</body>
</html>