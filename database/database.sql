----------------------------------
------DATABASE : 'mytodolist'-----
----------------------------------


DROP TABLE IF EXISTS thingstodo;

DROP TABLE IF EXISTS taskcategory;

DROP TABLE IF EXISTS Overdue;


-- Create a new Task-Category Table
CREATE TABLE taskcategory (    
    TaskCategoryID tinyint(1) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    TaskCategory varchar(8) NOT NULL    
);
-- Insert Value into Task-Category
INSERT INTO taskcategory (TaskCategory)
VALUES
( 'HomeWork' ),
( 'Chores' );


-- Create a new Things-to-do Table
CREATE TABLE thingstodo (
    ThingstodoID int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    TaskCategoryID tinyint(1) NOT NULL,    
    Task varchar(200) NOT NULL,
    DueDate date NOT NULL,    
    CONSTRAINT FK_thingstodo_taskcategory FOREIGN KEY (TaskCategoryID) REFERENCES taskcategory(TaskCategoryID)
         
);
-- Insert Value into Things-To-do
INSERT INTO thingstodo (Task, DueDate, TaskCategoryID)
VALUES
( 'Buy Milk', '2020-10-18', 2),
( 'Buy Bread', '2020-10-18', 2),
( 'Buy Cheese', '2020-10-16', 2),
( 'Buy Fruits', '2020-10-15', 2),
( 'Buy Vegetables', '2020-10-17', 2),
( 'Learn PHP', '2020-10-18', 1 ),
( 'Learn JavaScript', '2020-10-15', 1 ),
( 'Learn Sql', '2020-10-12', 1 ),
( 'Learn React', '2020-10-11', 1 ),
( 'Learn CSS', '2020-10-10', 1 );

-- Create a new Overdue Table
CREATE TABLE overdue (
    OverdueID int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    ThingstodoID int(10) NOT NULL,     
    TaskCategoryID tinyint(1) NOT NULL,  
    TaskCategory varchar(8) NOT NULL,   
    Task varchar(200) NOT NULL,
    DueDate date NOT NULL               
);



-- Create a new completed Table
CREATE TABLE completed (
    CompletedID int(10) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    ThingstodoID int(10) NOT NULL,     
    TaskCategoryID tinyint(1) NOT NULL,  
    TaskCategory varchar(8) NOT NULL,   
    Task varchar(200) NOT NULL,
    DueDate date NOT NULL               
);




INSERT INTO overdue (ThingstodoID,TaskCategoryID, TaskCategory,Task,DueDate) 
SELECT ThingstodoID,TaskCategoryID, TaskCategory,Task,DueDate FROM thingstodo 
INNER JOIN taskcategory USING(TaskCategoryID)
WHERE DueDate > '2020-10-15';
DELETE FROM thingstodo WHERE DueDate > '2020-10-15';



