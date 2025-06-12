<!-- Add New -->
<div
  class="modal fade"
  id="addnew"
  tabindex="-1"
  role="dialog"
  arialabelledby="myModalLabel"
  aria-hidden="true"
>
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button
          type="button"
          class="close"
          data-bs-dismiss="modal"
          ariahidden="true"
        >
          &times;
        </button>
        <center>
          <h4 class="modal-title" id="myModalLabel">TAMBAH DATA</h4>
        </center>
      </div>
      <div class="modal-body">
        <div class="container-fluid">
          <form method="POST" action="save.php">
            <div class="row form-group">
              <div class="col-sm-2">
                <label
                  class="control-label"
                  style="position: relative; top: 7px"
                  >title:</label
                >
              </div>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="title" />
              </div>
            </div>
            <div class="row form-group">
              <div class="col-sm-2">
                <label
                  class="control-label"
                  style="position: relative; top: 7px"
                  >uploader:</label
                >
              </div>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="uploader" />
              </div>
            </div>
            <div class="row form-group">
              <div class="col-sm-2">
                <label
                  class="control-label"
                  style="position: relative; top: 7px"
                  >description:</label
                >
              </div>
              <div class="col-sm-10">
                <input type="text" class="form-control" name="description" />
              </div>
            </div>
            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-default"
                data-dismiss="modal"
              >
                <span class="glyphicon glyphicon-remove"></span> Cancel
              </button>
              <button type="submit" name="add" class="btn btn-primary">
                <span class="glyphicon glyphicon-floppy-disk"></span> Save
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
