<?php
error_reporting(0);
//print_r($_FILES['chat-attach']);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

   if(isset($_FILES['chat-attach'])){

      $errors= array();
      $file_name = $_FILES['chat-attach']['name'];
      $file_size =$_FILES['chat-attach']['size'];
      $file_tmp =$_FILES['chat-attach']['tmp_name'];
      $file_type=$_FILES['chat-attach']['type'];
      $file_ext = strtolower(end(explode('.',$file_name)));
      //echo  $file_ext;
      $expensions= array("jpeg","jpg","png","zip");
      
      if(in_array($file_ext,$expensions)=== false){
         $errors[]="extension not allowed, please choose a JPEG or PNG file or Zip file.";
      }
      
      if($file_size > 1073741824){
         $errors[]='File size must be excately 1 GB';
      }
      
      if(empty($errors)==true){
        move_uploaded_file($file_tmp,"uploads/".$file_name);
        echo json_encode("uploads/".$file_name);
      }else{
         print_r($errors);
      }
   }
?>