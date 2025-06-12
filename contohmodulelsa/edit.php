<!-- Edit --> 
<div class="modal fade" id="edit_<?php echo $blog->title; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"> 
    <div class="modal-dialog"> 
        <div class="modal-content"> 
            <div class="modal-header"> 
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button> 
                <center><h4 class="modal-title" id="myModalLabel">Edit Mahasiswa</h4></center> 
            </div> 
            <div class="modal-body"> 
                <div class="container-fluid"> 
                    <form method="POST" action="update.php"> 
                        <div class="row form-group"> 
                            <div class="col-sm-2"> 
                                <label class="control-label" style="position:relative; top:7px;">title:</label> 
                            </div> 
                            <div class="col-sm-10"> 
                                <input type="text" class="form-control" name="title" value="<?php echo $blog->title; ?>" readonly> 
                            </div> 
                        </div> 
                        <div class="row form-group"> 
                            <div class="col-sm-2"> 
                                <label class="control-label" style="position:relative; top:7px;">uploader:</label> 
                            </div> 
                            <div class="col-sm-10"> 
                                <input type="text" class="form-control" name="uploader" value="<?php echo $blog->uploader; ?>"> 
                            </div> 
                        </div> 
                        <div class="row form-group"> 
                            <div class="col-sm-2"> 
                                <label class="control-label" style="position:relative; top:7px;">description:</label> 
                            </div> 
                            <div class="col-sm-10"> 
                                <input type="text" class="form-control" name="description" value="<?php echo $blog->description; ?>"> 
                            </div> 
                        </div> 
                        <div class="modal-footer"> 
                            <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Cancel</button> 
                            <button type="submit" name="edit" class="btn btn-success"><span class="glyphicon glyphicon-check"></span> Update</a> 
                        </div> 
                    </form> 
                </div>  
            </div> 
        </div> 
    </div> 
</div> 
                
<!-- Delete --> 
<div class="modal fade" id="delete_<?php echo $blog->title; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"> 
    <div class="modal-dialog"> 
        <div class="modal-content"> 
            <div class="modal-header"> 
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button> 
                <center><h4 class="modal-title" id="myModalLabel">Delete Mahasiswa</h4></center> 
            </div> 
            <div class="modal-body">  
                <p class="text-center">Apakah anda ingin menghapus</p> 
                <h2 class="text-center"><?php echo $blog->description; ?></h2> 
            </div> 
            <div class="modal-footer"> 
                <button type="button" class="btn btn-default" data-bs-dismiss="modal"><span class="glyphicon glyphicon-remove"></span> Cancel</button> 
                <a href="delete.php?title=<?php echo $blog->title; ?>" class="btn btn-danger"><span class="glyphicon glyphicon-trash"></span> Yes</a> 
            </div> 
        </div> 
    </div> 
</div> 
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>