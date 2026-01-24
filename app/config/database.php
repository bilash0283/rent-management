<?php
// class Database {
//     private $host = "localhost";
//     private $db   = "rent_management";
//     private $user = "root";
//     private $pass = "";
//     private $port = 3307;
//     public $conn;

//     public function connect(){
//         $this->conn = new mysqli(
//             $this->host,
//             $this->user,
//             $this->pass,
//             $this->db,
//                 $this->port,
//         );

//         if($this->conn->connect_error){
//             die("DB Error: " . $this->conn->connect_error);
//         }
//         return $this->conn;
//     }
// }



 $conn = mysqli_connect('localhost','root','','rent_management',3307);

?>
