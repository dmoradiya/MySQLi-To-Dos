----------------------------------
------DATABASE : 'mytodolist'-----
----------------------------------


DROP TABLE IF EXISTS thingstodo;


-- Create a new Things to do Table
CREATE TABLE thingstodo (
    ThingstodoID int(10) PRIMARY KEY AUTO_INCREMENT,
    Task varchar(200) NOT NULL,
    DueDate date NOT NULL,
    TaskCategoryID tinyint(1) NOT NULL,
    TaskCategory varchar(8) NOT NULL    
);
-- Insert Value into Things To do
INSERT INTO thingstodo (Task, DueDate, TaskCategoryID,TaskCategory)
VALUES
( 'Buy Milk', '2020-10-18', 2, 'Chores' ),
( 'Buy Bread', '2020-10-18', 2, 'Chores' ),
( 'Buy Cheese', '2020-10-16', 2, 'Chores' ),
( 'Buy Fruits', '2020-10-15', 2, 'Chores' ),
( 'Buy Vegetables', '2020-10-17', 2, 'Chores' ),
( 'Learn PHP', '2020-10-18', 1, 'HomeWork' ),
( 'Learn JavaScript', '2020-10-15', 1, 'HomeWork' ),
( 'Learn Sql', '2020-10-12', 1, 'HomeWork' ),
( 'Learn React', '2020-10-11', 1, 'HomeWork' ),
( 'Learn CSS', '2020-10-10', 1, 'HomeWork' );




