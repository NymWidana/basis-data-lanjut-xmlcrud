<?php 
 session_start(); 
 if(isset($_POST['edit'])){ 
  $blogs = simplexml_load_file('database.xml'); 
  foreach($blogs->blog as $blog){ 
   if($blog->title == $_POST['title']){ 
    $blog->uploader = $_POST['uploader']; 
    $blog->description = $_POST['description']; 
    break; 
   } 
  } 
  
  file_put_contents('database.xml', $blogs->asXML()); 
  $_SESSION['message'] = 'Blog telah berhasil di Update'; 
  header('location: index.php'); 
 } 
 else{ 
  $_SESSION['message'] = 'Pilih terlebih dahulu untuk mengedit data'; 
  header('location: index.php'); 
 } 
  
?> 
