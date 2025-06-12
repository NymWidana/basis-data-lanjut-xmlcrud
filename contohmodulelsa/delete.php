<?php 
 session_start(); 
 $title = $_GET['title']; 
  
 $blogs = simplexml_load_file('database.xml'); 
  
 //we're are going to create iterator to assign to each user 
 $index = 0; 
 $i = 0; 
  
 foreach($blogs->blog as $blog){ 
  if($blog->title == $title){ 
   $index = $i; 
   break; 
  } 
  $i++; 
 } 
  
 unset($blogs->blog[$index]); 
 file_put_contents('database.xml', $blogs->asXML()); 
  
 $_SESSION['message'] = 'Blog Berhasil di Hapus'; 
 header('location: index.php'); 
  
?> 
