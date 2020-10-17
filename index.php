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
    $completed = null;
    $message = null;    
    
    

   /**
    *########################## Populate Form Select Options from TaskCategory Table ##################################    
    */
    
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
     * ################### Get Form Data ###################
     */

    if( $_GET ){
        if( empty($_GET['task']) ){
            echo $message = 'Please ADD Task!';
        }else {
            if( $insert = $connection->prepare("INSERT INTO thingstodo(Task, DueDate, TaskCategoryID)VALUE(?, ?, ?)") ){
                if( $insert->bind_param("ssi", $_GET['task'], $_GET['date'], $_GET['task_category']) ){
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

    }       

    /**
    *########################## Overdue Section start ########################
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
    

    /**
     * ****************** Completed to do **************************************
     */

     // Sanitize All the Input

    // if( isset($_GET) ){
        // if( !isset($_GET['task_complete_id']) || $_GET['task_complete_id'] === "" ) {
        //     exit("You have reached this page by mistake");
        // }
        // if( filter_var($_GET['task_complete_id'], FILTER_VALIDATE_INT) ) {
        //     $task_complete_id = $_GET['task_complete_id'];
        // } else {
        //     exit("An incorrect value for Task ID was used");
        // }
        // var_dump($_GET['task_complete_id']);
        // $task_complete_id = $_GET['task_complete_id'];
        // echo 'GET  empty';
        
    
    //     if( !isset($_GET['task_delete_id']) || $_GET['task_delete_id'] === "" ) {
    //         exit("You have reached this page by mistake");
    //     }
    //     if( filter_var($_GET['task_delete_id'], FILTER_VALIDATE_INT) ) {
    //         $task_delete_id = $_GET['task_delete_id'];
    //     } else {
    //         exit("An incorrect value for Task ID was used");
    //     }
    //}
    
    if(isset($_GET['task_complete_id'])){
        //Completed Insert data from thingstodo Table
    
        $task_complete_id = $_GET['task_complete_id'];

        $completed_thingstodo_insert_sql = "INSERT INTO completed (ThingstodoID,TaskCategoryID, TaskCategory,Task,DueDate) 
        SELECT ThingstodoID,TaskCategoryID, TaskCategory,Task,DueDate FROM thingstodo 
        INNER JOIN taskcategory USING(TaskCategoryID)
        WHERE ThingstodoID = $task_complete_id";
        if( !$completed_thingstodo_insert_result = $connection->query($completed_thingstodo_insert_sql) ) {
            die("Could not Insert to the completed database table");
        }

        // completed Insert data from overdue Table
        $completed_overdue_insert_sql = "INSERT INTO completed (ThingstodoID,TaskCategoryID, TaskCategory,Task,DueDate) 
        SELECT ThingstodoID,TaskCategoryID, TaskCategory,Task,DueDate FROM overdue     
        WHERE ThingstodoID = $task_complete_id";
        if( !$completed_overdue_insert_result = $connection->query($completed_overdue_insert_sql) ) {
            die("Could not Insert to the completed database table");
        }

        // Delete Duplicate from the Things to do    
        $completed_thingstodo_duplicate_delete_sql = "DELETE FROM thingstodo WHERE ThingstodoID = $task_complete_id";
        if( !$completed_thingstodo_duplicate_delete_result = $connection->query($completed_thingstodo_duplicate_delete_sql) ) {
            die("Could not add to completed from the thingstodo database table");
        }

        // Delete Duplicate from the Overdue    
        $completed_overdue_duplicate_delete_sql = "DELETE FROM overdue WHERE ThingstodoID = $task_complete_id";
        if( !$completed_overdue_duplicate_delete_result = $connection->query($completed_overdue_duplicate_delete_sql) ) {
            die("Could not add to completed from the overdue database table");
        }
    }
   


    if(isset($_GET['task_delete_id'])){

        $task_delete_id = $_GET['task_delete_id'];
        /********************************** DELETE START ********************** */
        $thingstodo_delete_sql = "DELETE FROM thingstodo WHERE ThingstodoID = $task_delete_id";
        if( !$thingstodo_delete_result = $connection->query($thingstodo_delete_sql) ) {
            die("Could not Deleted Task From the Thingstodo database table");
        }

        $overdue_delete_sql = "DELETE FROM overdue WHERE ThingstodoID = $task_delete_id";
        if( !$overdue_delete_result = $connection->query($overdue_delete_sql) ) {
            die("Could not Deleted Task From the overdue database table");
        }

        $completed_delete_sql = "DELETE FROM completed WHERE ThingstodoID = $task_delete_id";
        if( !$completed_delete_result = $connection->query($completed_delete_sql) ) {
            die("Could not Deleted Task From the completed database table");
        }

        /********************************** DELETE FINISH ********************* */

    }
   
    /**
     * #################### Display Completed Task #######################
     */

    // Select From the Completed Table
    $completed_sql = "SELECT * FROM completed";
    
    // Get the Result query Object
    $completed_result = $connection->query($completed_sql);
    if( !$completed_result ){
        echo "something went wrong with the query";
        exit();
    }
    
    // Check for Number of rows, if no Row found then display message
    if( $completed_result->num_rows === 0 ){
        $completed = "<tr><td colspan='4'>There is no completed Task</td><tr>";    
    
    } else { // Get data from each row
        while( $row = $completed_result->fetch_assoc() ){  

            $completed .= sprintf('  
                <tr>
                    <td>%s</td>
                    <td>%s</td>
                    <td>%s</td>
                    <td>                         
                        <form method="GET" action="#">                        
                        <input type="hidden" value="%d" name="task_delete_id">                                                   
                        <input type="submit" value="Delete">                            
                        </form>   
                    </td>
                </tr>
                ',
                $row['TaskCategory'],
                $row['Task'],
                $row['DueDate'],
                $row['ThingstodoID']                  
            );
        }
    }

     /**
    *########################## Display Overdue task ########################
    */
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
                        <form method="GET" action="#">
                        <input type="hidden" value="%d" name="task_complete_id">                        
                        <input type="submit" value="Complete">                                                 
                        </form> 

                        <form method="GET" action="#">                        
                        <input type="hidden" value="%d" name="task_delete_id">                                                   
                        <input type="submit" value="Delete">                            
                        </form>      
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
     * ########################### Display Thingstodo Task #############################
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
                        <form method="GET" action="#">
                        <input type="hidden" value="%d" name="task_complete_id">                        
                        <input type="submit" value="Complete">                                                 
                        </form> 

                        <form method="GET" action="#">                        
                        <input type="hidden" value="%d" name="task_delete_id">                                                   
                        <input type="submit" value="Delete">                            
                        </form>       
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
    
    <!-- Add Todo Start -->
    <h2>Add Todo</h2>

        
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET" enctype="multipart/form-data">
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
    
    <!-- Complete start -->
    <h2>Completed<h2>
    <table>
        <tr>
            <th>TaskCategory</th>    
            <th>Task</th>
            <th>DueDate</th>            
            <th>Delete</th>
        </tr>
        <?php echo $completed; ?>            
    </table>

    
</body>
</html>