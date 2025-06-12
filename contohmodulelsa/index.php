<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <title>CRUD XML</title>
    <link
      rel="stylesheet"
      type="text/css"
      href="asset/bootstrap/css/bootstrap.css"
    />
  </head>
  <body>
    <div class="container">
      <h1 class="page-header text-center">CRUD XML MENGGUNAKAN PHP</h1>
      <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
          <a href="#addnew" class="btn btn-primary" data-bs-toggle="modal"
            ><span class="glyphicon glyphicon-plus"></span> New</a
          >
          <?php  
          session_start(); 
          if(isset($_SESSION['message'])){ 
          ?>
          <div class="alert alert-info text-center" style="margin-top: 20px">
            <?php echo $_SESSION['message']; ?>
          </div>
          <?php 
          unset($_SESSION['message']); 
          } 
          ?>
          <table
            class="table table-bordered table-striped"
            style="margin-top: 20px"
          >
            <thead>
              <th>title</th>
              <th>uploader</th>
              <th>desc</th>
              <th>Action</th>
            </thead>
            <tbody>
              <?php 
                    //load xml file 
                    $file = simplexml_load_file('database.xml'); 
  
                    foreach($file->blog as $blog){ ?>
              <tr>
                <td><?php echo $blog->title; ?></td>
                <td><?php echo $blog->uploader; ?></td>
                <td><?php echo $blog->description; ?></td>
                <td>
                  <a
                    href="#edit_<?php echo $blog->title; ?>"
                    data-bs-toggle="modal"
                    class="btn btn-success btn-sm"
                    ><span class="glyphicon glyphicon-edit"></span> Edit</a
                  >
                  <a
                    href="#delete_<?php echo $blog->title; ?>"
                    data-bs-toggle="modal"
                    class="btn btn-danger btn-sm"
                    ><span class="glyphicon glyphicon-trash"></span> Delete</a
                  >
                </td>
                <?php include('edit.php'); ?>
              </tr>
              <?php 
                    } 
                    ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <?php include('create.php'); ?>
    <script src="asset/js/jquery-3.7.1.min.js"></script>
    <script src="asset/bootstrap/js/bootstrap.min.js"></script>
  </body>
</html>
