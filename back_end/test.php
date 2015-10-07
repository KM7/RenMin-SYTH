<?php

$servername = "";
$username = "";
$password = "";
$startid =$_GET['startid'];



// Create connection
$conn = new mysqli($servername, $username, $password,"liushanshan");

mysqli_select_db($conn, 'liushanshan');
mysqli_set_charset ( $conn , 'utf8' );
// Check connection

if ($conn->connect_error) {
echo "error";
    die("Connection failed: " . $conn->connect_error);
}

$sql ="SELECT * 
FROM  `msgrecords` 
WHERE  `msg_type` =1 AND `msg_id`> $startid
ORDER BY  `msgrecords`.`submission_time` ASC LIMIT 0 , 2";

$result = $conn->query($sql);
$rows = array();
while($r=$result->fetch_array())

{
    $rows[] = $r;
//echo $row['msg_content'];//$row["name"];

}
$out = array('random_pics' =>$rows);


print json_encode($out);

$conn->close();

?>

