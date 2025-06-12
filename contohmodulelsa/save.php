<?php 
 session_start(); 
 if(isset($_POST['add'])){ 
   
  $blogs = simplexml_load_file('database.xml'); 
  $blog = $blogs->addChild('blog'); 
  $blog->addChild('title', $_POST['title']); 
  $blog->addChild('uploader', $_POST['uploader']); 
  $blog->addChild('description', $_POST['description']); 
   
  $dom = new DomDocument(); 
  $dom->preserveWhiteSpace = false; 
  $dom->formatOutput = true; 
  $dom->loadXML($blogs->asXML()); 
  $dom->save('database.xml'); 
  // Prettify / Format XML and save 
  
  $_SESSION['message'] = 'Blog berhasil di tambahkan'; 
  header('location: index.php'); 
 } 
 else{ 
  $_SESSION['message'] = 'Fill up add form first'; 
  header('location: index.php'); 
 } 
?>
