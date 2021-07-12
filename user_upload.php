<?php 
//connect to MySQL database.
$servername = "localhost";
$username = "root";
$password = "";
mysqli_report(MYSQLI_REPORT_STRICT);
// Create connection
try {
$conn = new mysqli($servername, $username, $password);
echo "MySQL Server connected successfully \n";
} catch (Exception $e) {
    echo 'ERROR:'.$e->getMessage();
    die("Failed to Connect MySQL " );
}
// Create database

$sql = "CREATE SCHEMA IF NOT EXISTS `userDB`;";
try {
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully.\n";
//Select database
mysqli_select_db($conn, 'userDB');
  }
} catch (Exception $e) {
  echo 'ERROR:'.$e->getMessage();
  die("Failed to create Database" );
}

//create users database table(unique index for email)
// sql to create table

$sql = "CREATE TABLE IF NOT EXISTS users (
  id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  fname VARCHAR(30) NOT NULL,
  surname VARCHAR(30) NOT NULL,
  email VARCHAR(50) UNIQUE
  );";
  try{
  $conn->query($sql);
    echo "Table users created successfully\n";
  } catch (Exception $e) {
    echo 'ERROR:'.$e->getMessage();
    die("Failed to create Table users" );
  }
//read users.csv file.
//Name and Surname convert to titlecase.
//email convert to lowercase.
//check for valid emailid
//insert into MySQL database.
//error message if emailid is repeated.

$conn->close();
?>