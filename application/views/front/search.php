
<div class="container">

    <h3 class="booking-title" align="center">Hasil Pencarian<small><a class="popup-text" href="#search-dialog" data-effect="mfp-zoom-out">Ubah Pencarian</a></small></h3>
    <div class="row">
        <div class="col-md-3">
            <aside class="booking-filters text-white">
                <form  id="insertData" onsubmit="insertfunction(event)">

                    <h3>Filter :</h3>
                    <ul class="list booking-filters-list">
                        <li>
                            <h5 class="booking-filters-title">Harga</h5>
                            <input type="text" id="price-slider" name="harga">
                        </li>
                        <li>
                            <h5 class="booking-filters-title">Jenis Kelamin</h5>
                            <div>
                              <div class="radio-inline">
                                <label><input type="radio" name="gender" value="pria" checked>Pria</label>
                            </div>
                            <div class="radio-inline">
                                <label><input type="radio" name="gender" value="wanita">Wanita</label>
                            </div>
                            <div class="radio-inline">
                                <label><input type="radio" name="gender" value="campuran">Campuran</label>
                            </div>
                        </div>
                    </li>
                    <li>
                        <h5 class="booking-filters-title">Fasilitas</h5>
                        <div class="checkbox" value="WiFi">
                            <label>
                                <input class="i-check" type="checkbox" name="fasilitaskos[]" value="WiFi"/>Wifi
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input class="i-check" type="checkbox" name="fasilitaskos[]" value="Parkir" />Parkir
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input class="i-check" type="checkbox" name="fasilitaskos[]" value="Nasi"/>Nasi
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input class="i-check" type="checkbox" name="fasilitaskos[]" value="Air Putih"/>Air Putih
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input class="i-check" type="checkbox" name="fasilitaskos[]" value="24Jam"/>24 Jam
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input class="i-check" type="checkbox" name="fasilitaskos[]" value="Laundry"/>Laundry
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input class="i-check" type="checkbox" name="fasilitaskos[]" value="Dapur"/>Dapur
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input class="i-check" type="checkbox" name="fasilitaskamar[]" value="AC"/>AC
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input class="i-check" type="checkbox" name="fasilitaskamar[]" value="KM Dalam"/>KM Dalam
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input class="i-check" type="checkbox" name="fasilitaskamar[]" value="Lemari"/>Lemari
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input class="i-check" type="checkbox" name="fasilitaskamar[]" value="Kipas Angin"/>Kipas Angin
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input class="i-check" type="checkbox" name="fasilitaskamar[]" value="Kunci Duplikat"/>Kunci Duplikat
                            </label>
                        </div>
                        <div class="checkbox">
                            <label>
                                <input class="i-check" type="checkbox" name="fasilitaskamar[]" value="Listrik Token"/>Listrik Token
                            </label>
                        </div>
                        <input id="search" class="btn btn-primary" type="submit" value="Submit">
                    </li>
                </ul>
                
            </form>
        </aside>
    </div>
    <div class="col-md-9">
        <div class="nav-drop booking-sort">
            <h5 class="booking-sort-title"><a href="#">Urutkan<i class="fa fa-angle-down"></i><i class="fa fa-angle-up"></i></a></h5>
            <ul class="nav-drop-menu">
                <li>
                    <a href="#">Harga Tinggi ke Rendah</a>
                </li>
                <li>
                    <a href="#">Harga Rendah ke Tinggi</a>
                </li>
            </li>
        </ul>
    </div>
    <div class="row row-wrap">
        <div class="col-md-4">
            <div class="thumb">
                <header class="thumb-header">
                    <a class="hover-img" href="detail.php">
                        <img src="<?php echo base_url(); ?>assets/images/hotel_1_800x600.jpg"/>
                        <h5 class="hover-title-center">Pesan Sekarang</h5>
                    </a>
                </header>
                <div class="thumb-caption"> 
                    <h5 class="thumb-title"><a class="text-darken" href="detail.php">Semangka 5</a></h5>
                    <p class="mb0"><small>Jl. Semangka 5 Kec. Bareng, Kawi, Malang</small>
                    </p>
                    <p class="mb0 text-darken"><span class="text-lg lh1em">Rp 650.000</span><small>/bulan</small>
                    </p>
                    <p class="mb0" style="font-size: 15px;color: #ff023c"><b>2 Kamar Tersisa !</b>
                    </p>
                </div>
            </div>
        </div>
        <hr />
        <div class="row">
            <div class="col-md-12">
                <ul class="pagination">
                    <li class="active"><a href="#">1</a>
                    </li>
                    <li><a href="#">2</a>
                    </li>
                    <li><a href="#">3</a>
                    </li>
                    <li class="dots">...</li>
                    <li><a href="#">10</a>
                    </li>
                    <li class="next"><a href="#">Next Page</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<div class="gap"></div>
</div>


<script>

    function insertfunction(e) {
   e.preventDefault();// will stop alethe form submission
   var dataString = $("#insertData").serialize();
   $.ajax({
    url:"<?php echo base_url() ?>index.php/getsearch",
    type: 'POST',
    data:dataString,
    success: function(response){
        if (response != null) {
            alert('sip');
        }else{
            alert('sup');
        }
        alert(response);
        // if( !$.isArray(response) ||  !response.length ) {
        //     alert('isi');
        // }


    },
    error: function(){
      alert('Gagal menambahkan data');
  }
}); 
}


</script>