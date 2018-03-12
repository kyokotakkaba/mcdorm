
<!-- Page -->
<div class="page animsition">
  <div class="page-header">
    <h1 class="page-title">Insert Data Mahasiswa</h1>
  </div>
  <div class="page-content">
    <div class="panel">
      <div class="panel-body container-fluid">
        <div class="row row-lg">
          <div class="col-md-12">
            <!-- Example Basic Form -->
            <div class="example-wrap">
              <div class="example">
                <form  id="insertMahasiswa" onsubmit="insertfunction(event)">
                  <div class="form-group row">
                    <div class="col-sm-6">
                      <label class="control-label">NIM</label>
                      <input type="text" class="form-control" name="id" required/>
                    </div>
                    <div class="col-sm-6">
                      <label class="control-label">Nama</label>
                      <input type="text" class="form-control" name="nama" required pattern="^[a-zA-Z\s]+$" />
                    </div>
                  </div>
                  <div class="form-group row">
                    <div class="col-sm-6">
                      <label class="control-label">Password</label>
                      <input type="password" class="form-control" name="password" required pattern="[A-Za-z0-9].{4,}"/>
                    </div>
                    <div class="col-sm-6">
                      <label class="control-label">Email</label>
                      <input type="email" class="form-control" name="email" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$"/>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="control-label">No Telepon</label>
                    <input type="text" class="form-control" name="notelp" required pattern="[0-9]+"/>
                  </div>
                  <div class="form-group pull-right">
                    <input id="submit" type="submit" class="btn btn-animate btn-animate-side btn-info btn-md" value="Tambahkan Data">
                  </input>
                  <button type="reset" class="btn btn-animate btn-animate-side btn-warning btn-md">
                    <span><i class="icon fa-refresh"></i> &nbsp<b>Refresh</b></span>
                  </button>
                  <a href="manajemen_mahasiswa_data.php">
                    <button type="button" class="btn btn-animate btn-animate-side btn-primary btn-md">
                      <span><i class="icon fa-mail-reply"></i> &nbsp<b>Kembali</b></span>
                    </button>
                  </a>
                </div>
              </form>
            </div>
          </div>
          <!-- End Example Basic Form -->
        </div>
      </div>
    </div>
  </div>
</div>
</div>
<!-- End Page -->
<script>

  function insertfunction(e) {
  e.preventDefault();// will stop the form submission
  var urls='main/insertMahasiswa';
  var dataString = $("#insertMahasiswa").serialize();
  var buttonname = $("#submit").val();
   $("#submit").val("Tunggu...");
    $("#submit").prop("disabled",true);
  $.ajax({
    url:"<?php echo base_url() ?>index.php/"+urls,
    type: 'POST',
    data:dataString,
    success: function(response){
      if (response == 1) {
        alert("Berhasil menambah data");
        window.location.href = 'manajemen_mahasiswa_data';
      }else{
        alert(response);
        $("#submit").val(buttonname);
      }
    },
    error: function(){
      alert('Gagal menambahkan data');
      $("#submit").val(buttonname);
    }
  }); 
}

</script>