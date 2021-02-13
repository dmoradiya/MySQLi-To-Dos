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

    // Input sanitization Function    
    // Citation Start : 
    // Link : https://www.tutorialspoint.com/php/php_validation_example.htm
    // Purpose : Filter input data 
    function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = stripcslashes($data);
        $data = htmlspecialchars($data);
        return $data;
     }
    // Citation End

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
       
        if( !empty($_GET['task']) && !empty($_GET['date']) && !empty($_GET['task_category'])){
        
            $task = test_input($_GET['task']);
            if( $insert = $connection->prepare("INSERT INTO thingstodo(Task, DueDate, TaskCategoryID)VALUE(?, ?, ?)") ){
                if( $insert->bind_param("ssi", $task, $_GET['date'], $_GET['task_category']) ){
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
        

    // Delete Duplicate(s) from the Things to do    
    $thingstodo_duplicate_delete_sql = "DELETE FROM thingstodo WHERE DueDate < NOW()";
    if( !$thingstodo_duplicate_delete_result = $connection->query($thingstodo_duplicate_delete_sql) ) {
        die("Could not Delete from the thingstodo database table");
    }         

    /**
     * ######################## Complete And Delete Buttons ########################**
     */    
    
    if(isset($_GET['task_complete_id'])){
        //Completed Insert data from thingstodo Table
        if( filter_var($_GET['task_complete_id'], FILTER_VALIDATE_INT) ) {
                $task_complete_id = $_GET['task_complete_id'];
            } else {
                exit("An incorrect value for Task ID was used");
            }  
       

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
   
    /********************************** DELETE BUTTON START ********************** */

    if(isset($_GET['task_delete_id'])){

        if( filter_var($_GET['task_delete_id'], FILTER_VALIDATE_INT) ) {
                    $task_delete_id = $_GET['task_delete_id'];
                } else {
                    exit("An incorrect value for Task ID was used");
                }       
        
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
        // $completed = "<tr><td colspan='4'>There is no completed Task</td><tr>";    
    
    } else { // Get data from each row
        while( $row = $completed_result->fetch_assoc() ){  

            $completed .= sprintf('  
                <tbody class="bg-gray-200">
                    <tr class="bg-green-100 border-4 border-gray-200">
                        <td class="px-16 py-0.5">%s</td>
                        <td class="px-16 py-0.5">%s</td>
                        <td class="px-16 py-0.5">%s</td>
                        <td class="px-16 py-0.5">
                            <div class="flex flex-row">
                                <div class="invisible bg-green-500 text-white px-4 py-2 border rounded-md hover:bg-green-200 hover:border-green-500 hover:text-black " >Complete</div> 
                                <form class="delete-btn" method="GET" action="#">                        
                                    <input type="hidden" value="%d" name="task_delete_id">                                                   
                                    <input class="bg-red-500 text-white px-6 py-2 border rounded-md hover:bg-red-200 hover:border-red-500 hover:text-black " type="submit" value="Delete">                            
                                </form> 
                            </div>      
                        </td>
                    </tr>
                </tbody>                
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
        // $overdue = "<tr><td colspan='5'>There is no Overdue Task</td><tr>";    

    } else { // Get data from each row
        while( $row = $overdue_result->fetch_assoc() ){  

            $overdue .= sprintf('  
                <tbody class="bg-gray-200">
                    <tr class="bg-red-100 border-4 border-gray-200">
                        <td class="px-16 py-0.5">%s</td>
                        <td class="px-16 py-0.5">%s</td>
                        <td class="px-16 py-0.5">%s</td>
                        <td class="px-16 py-0.5">
                            <div class="flex flex-row">
                                <form class="complete-btn"  method="GET" action="#">
                                    <input type="hidden" value="%d" name="task_complete_id">                        
                                    <input class="bg-green-500 text-white px-4 py-2 border rounded-md hover:bg-green-200 hover:border-green-500 hover:text-black " type="submit" value="Complete">                                                 
                                </form> 

                                <form class="delete-btn" method="GET" action="#">                        
                                    <input type="hidden" value="%d" name="task_delete_id">                                                   
                                    <input class="bg-red-500 text-white px-6 py-2 border rounded-md hover:bg-red-200 hover:border-red-500 hover:text-black " type="submit" value="Delete">                            
                                </form> 
                            </div>      
                        </td>
                    </tr>
                </tbody>                
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
                <tbody class="bg-gray-200">
                    <tr class="bg-blue-50 border-4 border-gray-200">
                            <td class="px-16 py-0.5">%s</td>
                            <td class="px-16 py-0.5">%s</td>
                            <td class="px-16 py-0.5">%s</td>
                            <td class="px-16 py-0.5">
                                <div class="flex flex-row">
                                    <form class="complete-btn"  method="GET" action="#">
                                    <input type="hidden" value="%d" name="task_complete_id">                        
                                    <input class="bg-green-500 text-white px-4 py-2 border rounded-md hover:bg-green-200 hover:border-green-500 hover:text-black " type="submit" value="Complete">                                                 
                                    </form> 

                                    <form class="delete-btn" method="GET" action="#">                        
                                    <input type="hidden" value="%d" name="task_delete_id">                                                   
                                    <input class="bg-red-500 text-white px-6 py-2 border rounded-md hover:bg-red-200 hover:border-red-500 hover:text-black " type="submit" value="Delete">                            
                                    </form> 
                                </div>      
                            </td>
                        </tr>
                </tbody>
                
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
<!----------------- HTML PART START ----------------------->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My ToDo List</title>
    <!-- Style(s) -->
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <!-- Script(s) -->
</head>
<body>
    <section class="mx-1 my-1 h-screen bg-blue-100 bg-opacity-75">
        <h1 class="font-serif text-center text-3xl mt-0 py-5">My ToDo List</h1>
        
        <!-- Add Todo Start -->
       
        <form class="flex flex-row justify-evenly py-4" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET" enctype="multipart/form-data">
            <p>               
                <input class="rounded-lg p-1 border-t mr-0 border-b border-l text-gray-800 border-gray-200 m-white" placeholder="Add Task..." type="text" name="task" id="task" required>
                <label class="invisible" for="task">Task</label>
            </p>
            <p>              
                <input class="rounded-l-lg p-1 border-t mr-0 border-b border-l text-gray-800 border-gray-200 m-white" type="date" name="date" id="date" min="2020-01-01" required>
                <label class="px-1 rounded-r-lg bg-blue-300  text-gray-800 font-bold p-1 uppercase border-blue-500  border-t border-b border-r" for="date">Due date</label>
            </p>
            <p>
                
                <select class="rounded-l-lg p-1 border-t mr-0 border-b border-l text-gray-800 border-gray-200 bg-white" name="task_category" id="task_category" required>
                    <option value="">Pick one</option>
                    <?php echo $category_select_options; ?>
                </select>
                <label class="px-1 rounded-r-lg bg-blue-300  text-gray-800 font-bold p-1 uppercase border-blue-500 border-t border-b border-r" for="task_category">Task Category</label>
            </p>
            <p>
                <input class="px-3 rounded-lg bg-yellow-400  text-gray-800 font-bold p-1 uppercase border-yellow-500 border-t border-b border-r hover:bg-yellow-200 hover:border-yellow-500 hover:text-black" type="submit" value="Add new task">
            </p>
        </form>
        <p id="message"><?php if($message) echo $message; ?></p>
        <!-- Add Todo end -->
        
        

        <!-- Things to do start -->
        <div class="flex flex-row justify-center my-2">            
            <div  class="mx-8 py-2 px-8 shadow-md no-underline rounded-full bg-blue-50 text-black-300 font-sans font-semibold text-l mr-2">Things To Do</div>
            <div  class="mx-8 py-2 px-8 shadow-md no-underline rounded-full bg-red-200 text-black-300 font-sans font-semibold text-l mr-2">Overdue Task</div>               
            <div  class="mx-8 py-2 px-8 shadow-md no-underline rounded-full bg-green-200 text-black-300 font-sans font-semibold text-l mr-2">Completed Task</div>         
        </div>
        <table class="min-w-full table-auto">
            <thead class="justify-between">
                <tr class="bg-gray-800">
                    <th class="px-16 py-2">
                        <span class="text-gray-300 text-left">TaskCategory</span>
                    </th>    
                    <th class="px-16 py-2">
                        <span class="text-gray-300">Task</span>
                    </th>
                    <th class="px-16 py-2">
                        <span class="text-gray-300">DueDate</span>
                    </th>
                    <th class="px-16 py-2">
                        <span class="text-gray-300">Actions</span>
                    </th>
                </tr>                                
            </thead>             
            <?php echo $things_to_do; ?>  
             <!-- Things to do end --> 
             
            <?php echo $overdue; ?> 
            <!-- Overdue end -->
              
            <?php echo $completed; ?>
            <!-- Complete end -->                      
        </table>
       
    </section>   
</body>
</html>