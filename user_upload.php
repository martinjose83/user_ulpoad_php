<?php 

$servername = "localhost";
$username = "root";
$password = "";
$dryrun = false;
$conn = null;
$fileName = "users.csv";
if (!$dryrun){
connectDBServer($servername, $username, $password);
createDBSchema();
createDBTable();
}
// Create connection
function connectDBServer($servername, $username, $password){
mysqli_report(MYSQLI_REPORT_STRICT);
try {
  $GLOBALS["conn"] = new mysqli($servername, $username, $password);
echo "MySQL Server connected successfully \n";
} catch (Exception $e) {
    echo 'ERROR:'.$e->getMessage();
    die("Failed to Connect MySQL " );
}
}
// Create database
function createDBSchema(){
$sql = "CREATE SCHEMA IF NOT EXISTS `userDB`;";
try {
if ($GLOBALS["conn"]->query($sql) === TRUE) {
    echo "Database created successfully.\n";
//Select database
mysqli_select_db($GLOBALS["conn"], 'userDB');
  }
} catch (Exception $e) {
  echo 'ERROR:'.$e->getMessage();
  die("Failed to create Database" );
}
}
//create users database table(unique index for email)
function createDBTable(){
  // sql to create table
$sql = "CREATE TABLE IF NOT EXISTS users (
  id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  fname VARCHAR(30) NOT NULL,
  surname VARCHAR(30) NOT NULL,
  email VARCHAR(50) UNIQUE
  );";
  try{
  $GLOBALS["conn"]->query($sql);
    echo "Table users created successfully\n";
  } catch (Exception $e) {
    echo 'ERROR:'.$e->getMessage();
    die("Failed to create Table users" );
  }
}
//read file users.csv.
$row = 1;
try
{
  

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
$data1 = fgetcsv($handle, 10000, ",");
if($data1){
  $num = count($data1);
  if(trim($data1[2])!="email"){
    echo $data1[0].",".$data1[2];
  if(validEmail($data1[2])){
    if(!$dryrun)updateToDB(titleCase($data1[0]),titleCase($data1[1]),strtolower($data1[2]));
    $row++;
    for ($c=0; $c < $num; $c++) {
      
     echo titleCase($data1[$c]). "\t\t";
  }
}else{
  echo "\t".$data1[2]."Not a valid email Id";
}
}else{for ($c=0; $c < $num; $c++) {
      
  echo titleCase($data1[$c]). "\t\t";
}}
echo "\n";}else {echo $fileName. " is an empty file";}


  while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
    $num = count($data);
    if($num>2){
if(validEmail($data[2]) ){
if (validNames($data[0],$data[1])){
  echo titleCase($data[0]). "\t\t".titleCase($data[1]."\t\t".strtolower($data[2]));
    if(!$dryrun)updateToDB(titleCase($data[0]),titleCase($data[1]),strtolower($data[2]));
}else{
  echo "Invalid or empty firstname and last name";
}
}else{
  echo $data[0]."\t\t".$data[1]."\t\t"."(".$data[2].") is not a valid email Id";
}
}else{
  echo "(not a complete valid data)";
}
echo "\n";
}
  fclose($handle);

  // send success JSON

} catch ( Exception $e ) {
  echo 'ERROR:'.$e->getMessage();
} 
//return false if both firstname and lastname
function validNames($fname,$lname){
  if(!empty(trim($fname))){
   return true;
  }
  if(!empty(trim($lname))){
    return true;
  }
  return false;
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
  echo "\t Record added successfully";
} else {
  //error message if emailid is repeated. or any error
  echo "\t Error: " . mysqli_error($GLOBALS["conn"]);
}
}
if($conn)$conn->close();
?>