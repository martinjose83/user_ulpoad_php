<?php 
//connect to MySQL database.
$servername = "localhost";
$username = "root";
$password = "";
$dryrun = false;
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
//read file users.csv.
$row = 1;
try
{
  $fileName = "users.csv";

  if ( !file_exists($fileName) ) {
    throw new Exception('File not found.');
  }$handle = fopen($fileName, "r");
  if ( !$handle ) {
    throw new Exception('File open failed.');
  }  
  $file_parts = pathinfo($fileName);
  if($file_parts['extension']!="csv"){
    throw new Exception('File found is not a csv file');
  }  

  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $num = count($data);
if(validEmail($data[2])){
    if(!$dryrun)updateToDB(titleCase($data[0]),titleCase($data[1]),strtolower($data[2]));
    echo " \n $num fields in line $row: \t\t";
    $row++;
    for ($c=0; $c < $num; $c++) {
      
     echo titleCase($data[$c]). "\t\t";
  }
}else{
  echo "\t".$data[2]."Not a valid email Id";
}
}
  fclose($handle);

  // send success JSON

} catch ( Exception $e ) {
  echo 'ERROR:'.$e->getMessage();
 

} 

//Name and Surname convert to titlecase.
function titleCase($str) {
  $str = trim($str);
  $sarr = explode(" ", $str);
  for ($x=0;$x<sizeof($sarr);$x++){
    $sarr[$x] = implode("'",explode(" ",ucwords(strtolower(implode(" ", explode("'",$sarr[$x]))))));
}
return implode(" ",$sarr);
}
//email convert to lowercase.
//check for valid emailid
function validEmail($email) {
  $email = filter_var($email, FILTER_SANITIZE_EMAIL);
  return filter_var($email, FILTER_VALIDATE_EMAIL);
}

//insert into MySQL database.
function updateToDB($fname, $lname, $email){
$sql = $GLOBALS["conn"]->prepare("INSERT INTO users (fname, surname, email)
VALUES (?,?,?);");
$sql->bind_param("sss", $fname, $lname, $email);
$x = $sql->execute();

if ($x) {
  echo "\t New record created successfully";
} else {
  echo "\t Error: " . mysqli_error($GLOBALS["conn"]);
}
}
//error message if emailid is repeated.

$conn->close();
?>