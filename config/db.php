<?php
$host = "localhost";
$dbname = "webbanhang";
$user_db = "root";
$password = "12345";

try{

     $conn = new PDO("mysql:host=$host;dbname=$dbname", $user_db, $password);
     var_dump($conn);
     echo "Ket noi thanh cong";
}catch(Exception $ex){

      echo "Ket noi that bai: ".$ex->getMessage();
}
?>