<?php
require_once "../config/database.php";

class TenantController {

 public function store(){
   $db = (new Database())->connect();

   $name = $_POST['name'];
   $email = $_POST['email'];
   $unit = $_POST['unit_id'];

   // Upload
   $nid_img = $_FILES['nid_image']['name'];
   move_uploaded_file($_FILES['nid_image']['tmp_name'],
     "../../public/uploads/nid/".$nid_img);

   $sql = "INSERT INTO tenants 
   (name,email,unit_id,nid_image)
   VALUES('$name','$email','$unit','$nid_img')";

   $db->query($sql);

   // Update unit status
   $db->query("UPDATE units SET status='occupied' WHERE id=$unit");

   header("Location: /tenants");
 }
}
