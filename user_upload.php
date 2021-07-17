<?php 

$servername = "localhost";
$username = "root";
$password = "";
$dryrun = false;
$conn = null;
$fileName = "users.csv";
$dbsetuprun = false;

$shortopts  = "";
$shortopts .= "u:";  // Required MySQL username
$shortopts .= "p:";  // Required MySQL password
$shortopts .= "h:";  // Required MySQL host address/name

$longopts  = array(
    "file:",          // Required value
    "create_table",   // Optional value
    "dry_run",        // No value
    "help",           // No value
);
$options = getopt($shortopts, $longopts);
var_dump($options);
//figure out what the directive was... 
if(array_key_exists("u", $options)){$username = $options["u"];}       //assign MySQL username.
if(array_key_exists("p", $options)){$password = $options["p"];}       //assign MySQL password
if(array_key_exists("h", $options)){$servername = $options["h"];}           //assign MySQL server hostname
if(array_key_exists("file", $options)){$fileName = $options["file"];}     //assign File name and path to read.
if(array_key_exists("create_table", $options)){$dbsetuprun = true;}   //initiate the sql setup and create table
if(array_key_exists("dry_run", $options)){$dryrun = true;}            //initiate dryrun
if(array_key_exists("help", $options)){  help();}

// if(isset($file) && $file != false){
//   dryRun($file);


if (!$dryrun){
connectDBServer($servername, $username, $password);
createDBSchema();
createDBTable();
}
// Create connection
function connectDBServer($servername, $username, $password) {
mysqli_report(MYSQLI_REPORT_STRICT);
try {
  $GLOBALS["conn"] = mysqli_connect($servername, $username, $password);
 
if (!$GLOBALS["conn"]) {
    die('Could not connect: ');
}
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
#die here for create table run
if($dbsetuprun){ die("Database setup run completed successfully");}
//read file users.csv.


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
  echo titleCase($data[0]). "\t\t".titleCase($data[1])."\t\t".strtolower($data[2]);
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
function help(){
  echo "help options: \n
  Commands\t \t\t\tDescription:\n
  --file [csv filename] \tGives the name of the file to be parsed \n
  --create_table\t\t\tThis will cause the MySQL users table to be built and die\n 
  --dry_run\t\t\t\tUsed with the --file directive in the instance that we want to run the\n
  \t\t\t\t\tscript but not insert into the DB. All other functions will be executed, \n
  \t\t\t\t\tbut the database won't be altered.\n
    -u \tMySQL username\n
    -p \tMySQL password\n
    -h \tMySQL host\n
  --help\t For help  OR to see these options\n
    ";
  die();
}
if($conn)$conn->close();
?>