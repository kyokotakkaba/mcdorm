<?php
class Main extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('main_model');
        $this->load->helper('url_helper');
    }

    public function index()
    {
        echo "empty";
    }



    public function login()
    {
        $this->load->view('main/login');
    }

    public function Loginvalidation()
    {
        $this->load->helper('form');
        $this->load->library('form_validation');

        $this->form_validation->set_rules('username', 'Username', 'required');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() === FALSE)
        {
            echo "failed validation";
        }
        else
        {

            $data['username']=$this->input->post('username');
            $data['password']=$this->input->post('password');

            $data['admin'] = $this->main_model->get_admin_login();

            if ($data['username']==$data['admin']['id_admin'] && md5($data['password']) == $data['admin']['password']) {
                //echo "login admin success! ";

                $this->load->helper('cookie');

                $cookie= array(
                 'name'   => 'backendCookie',
                 'value'  => md5($data['admin']['id_admin']),
                 'expire' => '0',
             );
                $this->input->set_cookie($cookie);
                //echo "Session created : ";
                //$this->getcookieAdmin();

                echo "1";
            }else{
                echo "login failed";
            }

            //echo "<br>username: ".$data['username']."<br>";
            //echo "password: ".$data['password']."<br>";
        }
    }

    public function checkcookieadmin()
    {
        $this->load->helper('cookie');
        if ($this->input->cookie('backendCookie',true)!=NULL) {
            return true;
        }else{
            return false;
        } 
    }

    public function home()
    {
        if ($this->checkcookieadmin()) {
            $this->load->view('templates/header');
            $this->load->view('templates/navbar');
            $this->load->view('templates/sidebar');
            $this->load->view('main/home');
            $this->load->view('templates/js');
            $this->load->view('templates/footer');
        }else{
            $this->login();
        }
        
    }

    public function changepassword()
    {
        if ($this->checkcookieadmin()) {
            $this->load->view('templates/header');
            $this->load->view('templates/navbar');
            $this->load->view('templates/sidebar');
            $this->load->view('main/changepassword');
            $this->load->view('templates/js');
            $this->load->view('templates/footer');
        }else{
            $this->login();
        }
        
    }

    public function updatepasswordadmin(){
        $oldpassword = md5($this->input->post('oldpassword'));
        $data = array(
            'password' => md5($this->input->post('newpassword'))
        );

        $insertStatus = $this->main_model->update_password($data,$oldpassword);
        echo $insertStatus;
    }

    public function manajemen_mahasiswa_data(){
        if ($this->checkcookieadmin()) {
            $this->load->view('templates/header');
            $this->load->view('templates/navbar');
            $this->load->view('templates/sidebar');
            $this->load->view('main/manajemen_mahasiswa_data');
            $this->load->view('templates/js');
            $this->load->view('templates/footer');
        }else{
            $this->login();
        }

    }

    public function getmahasiswa($id = NULL)
    {
        $data = $this->main_model->get_data_mahasiswa($id);

        if (empty($data))
        {
            $data = [];
        } else if ($id == NULL) {
            foreach ($data as &$row){ //add & to call by reference
                unset($row['password']);
                if ($row['status']=="Belum Bayar") {
                    date_default_timezone_set('Asia/Jakarta');
                    $now = time();
                    $expire = strtotime($row['kadaluarsa']);
                    if ($now >= $expire) {
                        $row['status'] = 'Expired';
                    }
                }
                
            }
        }else{
            unset($data['password']);
            if ($data['status']=="Belum Bayar") {
                date_default_timezone_set('Asia/Jakarta');
                $now = time();
                $expire = strtotime($data['kadaluarsa']);
                if ($now >= $expire) {
                    $data['status'] = 'Expired';
                }
            }
        }

        echo json_encode($data);
    }

    public function manajemen_mahasiswa_insert(){
        if ($this->checkcookieadmin()) {
            $this->load->view('templates/header');
            $this->load->view('templates/navbar');
            $this->load->view('templates/sidebar');
            $this->load->view('main/manajemen_mahasiswa_insert');
            $this->load->view('templates/js');
            $this->load->view('templates/footer');
        }else{
            $this->login();
        }
        
    }

    public function insertmahasiswa(){
        $data = array(
            'id_mahasiswa' => $this->input->post('id'),
            'password' => md5($this->input->post('password')),
            'nama_mahasiswa' => $this->input->post('nama'),
            'email' => $this->input->post('email'),
            'notelp_mahasiswa' => $this->input->post('notelp'),
            'status' => "Belum Pesan",
            'id_kos' => NULL,
            'id_kamar' => NULL,
            'tanggal_masuk' => NULL,
            'kadaluarsa' => NULL,
            'vakum' => 0,
            'lama_pemesanan' => NULL,
            'id_kamardetail' => NULL
        );

        $insertStatus = $this->main_model->insert_new_mahasiswa($data);
        echo $insertStatus;
    }

    public function manajemen_mahasiswa_edit(){
        if ($this->checkcookieadmin()) {
            $this->load->view('templates/header');
            $this->load->view('templates/navbar');
            $this->load->view('templates/sidebar');
            $this->load->view('main/manajemen_mahasiswa_edit');
            $this->load->view('templates/js');
            $this->load->view('templates/footer');
        }else{
            $this->login();
        }
        
    }

    public function resetpasswordmahasiswa($id){
        $data = array(
            'password' => md5($id)
        );

        $insertStatus = $this->main_model->reset_password($data,$id);
        echo $insertStatus;
    }

    public function getmahasiswaarray($id){
        $data = $this->main_model->get_data_mahasiswa($id);

        if (empty($data))
        {
            $data = [];
        }else{
            unset($data['password']);
            if ($data['status']=="Belum Bayar") {
                date_default_timezone_set('Asia/Jakarta');
                $now = time();
                $expire = strtotime($data['kadaluarsa']);
                if ($now >= $expire) {
                    $data['status'] = 'Expired';
                }
            }
        }
        return $data;
    }

    private function deleteimagepayment($idmahasiswa){
        $ds          = DIRECTORY_SEPARATOR;
        $targetPath = getcwd().$ds.'photos'.$ds.'payment'.$ds;
        $filename = $idmahasiswa.".jpg";
        $targetFile =  $targetPath. $filename;
        if (file_exists($targetFile)){
            unlink($targetFile);
        }
    }

    public function updatemahasiswa($jenis = NULL,$id = NULL){
        if ($jenis == 'profil') {
            $data = array(
                'nama_mahasiswa' => $this->input->post('nama'),
                'email' => $this->input->post('email'),
                'notelp_mahasiswa' => $this->input->post('notelp')
            );
        } else if ($jenis == 'verifikasi') {
            $mahasiswa = $this->getmahasiswaarray($id);
            $dataHistory = array(
                'id_mahasiswa' => $mahasiswa['id_mahasiswa'],
                'id_kamar' => $mahasiswa['id_kamar'],
                'nama_kos' => $mahasiswa['nama_kos'],
                'nama_kamar' => $mahasiswa['nama_kamar'],
                'alamat' => $mahasiswa['alamat'],
                'harga' => $mahasiswa['harga'],
                'gender' => $mahasiswa['gender_kos'],
                'tanggal_masuk' => $mahasiswa['tanggal_masuk'],
                'vakum' =>$mahasiswa['vakum'],
                'lama_pemesanan' =>$mahasiswa['lama_pemesanan'],
                'id_kamardetail' =>$mahasiswa['id_kamardetail']
            );
            $this->main_model->insert_new_history($dataHistory);

            $dataKamarDetailLama = $this->main_model->get_data_kamardetail($mahasiswa['id_kamardetail'],"kamardetail");
            if ($mahasiswa['vakum'] == 0) {
                //pesan kamar kosong
                $statusKamarDetail = 'buka terbatas';
            }else if($dataKamarDetailLama['status_kamardetail'] == 'buka terbatas'){
                //pesan vakum pertama
                $statusKamarDetail = 'tutup terbatas';
            }else if($dataKamarDetailLama['status_kamardetail'] == 'tutup terbatas'){
                //pesan vakum kedua
                $statusKamarDetail = 'tutup';
            }
            $dataKamarDetail = array(
                'status_kamardetail' => $statusKamarDetail,
            );
            $this->main_model->update_kamardetail($dataKamarDetail,$mahasiswa['id_kamardetail']);


            $data = array(
                'status' => 'Terpesan',
                'kadaluarsa'=> NULL
            );
        } else if ($jenis == 'cancel') {
            $data = array(
                'status' => 'Batal',
                'kadaluarsa'=> NULL
            );

            $this->deleteimagepayment($id);
        }

        $insertStatus = $this->main_model->update_mahasiswa($data,$id);
        echo $insertStatus;
    }

    public function manajemen_kos_data(){
        if ($this->checkcookieadmin()) {
            $this->load->view('templates/header');
            $this->load->view('templates/navbar');
            $this->load->view('templates/sidebar');
            $this->load->view('main/manajemen_kos_data');
            $this->load->view('templates/js');
            $this->load->view('templates/footer');
        }else{
            $this->login();
        }

    }

    public function getkos($id = NULL)
    {
        $data = $this->main_model->get_data_kos($id);

        if (empty($data))
        {
            $data = [];
        }

        echo json_encode($data);
    }

    public function manajemen_kos_edit(){
        if ($this->checkcookieadmin()) {
            $this->load->view('templates/header');
            $this->load->view('templates/navbar');
            $this->load->view('templates/sidebar');
            $this->load->view('main/manajemen_kos_edit');
            $this->load->view('templates/js');
            $this->load->view('templates/footer');
        }else{
            $this->login();
        }

    }

    public function updatekos($jenis = NULL,$id = NULL){
        if ($jenis == 'profil') {

            if ($this->input->post('fasilitas')) {
                $fasilitas = implode(',', $this->input->post('fasilitas'));
            }else{
                $fasilitas = "";
            }

            $data = array(
                'nama_kos' => $this->input->post('nama'),
                'alamat' => $this->input->post('alamat'),
                'notelp_kos' => $this->input->post('notelp'),
                'fasilitas_kos' => $fasilitas,
                'deskripsi_kos' => $this->input->post('deskripsi'),
                'gender_kos' => $this->input->post('gender'),
                'latitude' => $this->input->post('latitude'),
                'longitude' => $this->input->post('longitude'),
                'distance' => $this->input->post('distance')
            );
        }

        $insertStatus = $this->main_model->update_kos($data,$id);
        echo $insertStatus;
    }

    public function manajemen_kos_insert(){
        if ($this->checkcookieadmin()) {
            $this->load->view('templates/header');
            $this->load->view('templates/navbar');
            $this->load->view('templates/sidebar');
            $this->load->view('main/manajemen_kos_insert');
            $this->load->view('templates/js');
            $this->load->view('templates/footer');
        }else{
            $this->login();
        }

    }

    public function insertkos(){

        if ($this->input->post('fasilitas')) {
            $fasilitas = implode(',', $this->input->post('fasilitas'));
        }else{
            $fasilitas = "";
        }

        $data = array(
            'id_kos' => $this->input->post('id'),
            'nama_kos' => $this->input->post('nama'),
            'alamat' => $this->input->post('alamat'),
            'notelp_kos' => $this->input->post('notelp'),
            'fasilitas_kos' => $fasilitas,
            'deskripsi_kos' => $this->input->post('deskripsi'),
            'gender_kos' => $this->input->post('gender'),
            'latitude' => $this->input->post('latitude'),
            'longitude' => $this->input->post('longitude'),
            'distance' => $this->input->post('distance')
        );

        $insertStatus = $this->main_model->insert_new_kos($data);

        if ($insertStatus == 1) {
            try {
                $ds          = DIRECTORY_SEPARATOR;
                $tempDir = getcwd().$ds.'photos'.$ds.'temp'.$ds;
                $permanentDir = getcwd().$ds.'photos'.$ds.$this->input->post('id').$ds;

                if (is_dir($tempDir)) {
                    if (is_dir($permanentDir)) {
                        rmdir($permanentDir);
                    }
                    rename($tempDir, $permanentDir);
                }

            } catch (Exception $e) {
                $insertStatus = $e;
            }
        }


        echo $insertStatus;
    }




    public function manajemen_kos_kamar_insert(){
        if ($this->checkcookieadmin()) {
            $this->load->view('templates/header');
            $this->load->view('templates/navbar');
            $this->load->view('templates/sidebar');
            $this->load->view('main/manajemen_kos_kamar_insert');
            $this->load->view('templates/js');
            $this->load->view('templates/footer');
        }else{
            $this->login();
        }

    }

    public function insertkamar($idkos){

        if ($this->input->post('fasilitas')) {
            $fasilitas = implode(',', $this->input->post('fasilitas'));
        }else{
            $fasilitas = "";
        }

        $data = array(
            'id_kos' => $idkos,
            'nama_kamar' => $this->input->post('nama'),
            'harga' => $this->input->post('harga'),
            'panjang' => $this->input->post('panjang'),
            'lebar' => $this->input->post('lebar'),
            'fasilitas_kamar' => $fasilitas
        );

        $insertStatus = $this->main_model->insert_new_kamar($data);

        if ($insertStatus != 0) {
            //insertstatus = insertID
            try {
                $ds          = DIRECTORY_SEPARATOR;
                $tempDir = getcwd().$ds.'photos'.$ds.$idkos.$ds.'temp'.$ds;
                $permanentDir = getcwd().$ds.'photos'.$ds.$idkos.$ds.$insertStatus.$ds;
                if (is_dir($tempDir)) {
                    if (is_dir($permanentDir)) {
                        rmdir($permanentDir);
                    }
                    rename($tempDir, $permanentDir);
                }
                $insertStatus = 1;
            } catch (Exception $e) {
                $insertStatus = $e;
            }
        }else{
            $insertStatus = "Failed to insert Record";
        }


        echo $insertStatus;
    }

    public function manajemen_kos_kamar_edit(){
        if ($this->checkcookieadmin()) {
            $this->load->view('templates/header');
            $this->load->view('templates/navbar');
            $this->load->view('templates/sidebar');
            $this->load->view('main/manajemen_kos_kamar_edit');
            $this->load->view('templates/js');
            $this->load->view('templates/footer');
        }else{
            $this->login();
        }

    }

    public function getjumlahpesanankamar($idkamar){
        $data = $this->main_model->get_data_isikamar($idkamar);
        $count = 0;
        if ($data){
            foreach ($data as $row){
                if ($row['status'] == 'Belum Bayar') {
                    date_default_timezone_set('Asia/Jakarta');
                    $now = time();
                    $expire = strtotime($row['kadaluarsa']);
                    if ($now < $expire) {
                        $count = $count + 1;
                    }
                }else if($row['status'] == 'Belum Verifikasi'){
                    $count = $count + 1;
                }

            }
        }
        return $count;
    }

    public function getjumlahpesanankos($idkos){
        $data = $this->main_model->get_data_isikos($idkos);
        $count = 0;
        if ($data){
            foreach ($data as $row){
                if ($row['status'] == 'Belum Bayar') {
                    date_default_timezone_set('Asia/Jakarta');
                    $now = time();
                    $expire = strtotime($row['kadaluarsa']);
                    if ($now < $expire) {
                        $count = $count + 1;
                    }
                }else if($row['status'] == 'Belum Verifikasi'){
                    $count = $count + 1;
                }

            }
        }
        return $count;
    }

    public function getkamar($idkos,$idkamar = NULL)
    {
        $data = $this->main_model->get_data_kamar($idkos,$idkamar);

        if (empty($data))
        {
            $data = [];
        } 
        // else if($idkamar == NULL){
        //     foreach ($data as &$row){
        //     //kuota dikurangi jumlah pemesan
        //         $row['kuota'] = $row['kuota'] - $this->getjumlahpesanankamar($row['id_kamar']);
        //     }
        // } else{
        //     $data['kuota'] = $data['kuota'] - $this->getjumlahpesanankamar($data['id_kamar']);
        // }

        echo json_encode($data);
    }

    public function updatekamar($idkos,$idkamar){

        if ($this->input->post('fasilitas')) {
            $fasilitas = implode(',', $this->input->post('fasilitas'));
        }else{
            $fasilitas = "";
        }

        $kuota = $this->getjumlahpesanankamar($idkamar) + $this->input->post('kuota');

        $data = array(
            'nama_kamar' => $this->input->post('nama'),
            'harga' => $this->input->post('harga'),
            'panjang' => $this->input->post('panjang'),
            'lebar' => $this->input->post('lebar'),
            'fasilitas_kamar' => $fasilitas
        );
        $insertStatus = $this->main_model->update_kamar($data,$idkos,$idkamar);
        echo $insertStatus;
    }


    public function uploadimage($filename,$idkos,$idkamar = NULL){
        $ds          = DIRECTORY_SEPARATOR;
        $targetPath = getcwd().$ds.'photos';
        if ($idkamar == NULL) {
            $targetPath = $targetPath.$ds.$idkos.$ds;
        }else{
            $targetPath = $targetPath.$ds.$idkos.$ds.$idkamar.$ds;
        }

        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0755, true);
        }


        if (!empty($_FILES)) {
            $tempFile = $_FILES['file']['tmp_name'];
            $targetFile =  $targetPath. $filename;
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

            if ($ext == "png" || $ext == "PNG") {
                $image = imagecreatefrompng($tempFile);
                $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
                imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
                imagealphablending($bg, TRUE);
                imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
                imagedestroy($image);
                imagejpeg($bg, $tempFile, 100);
                imagedestroy($bg);
            }
            move_uploaded_file($tempFile,$targetFile.".jpg");
        }
    }

    public function logout(){
        $this->load->helper('cookie');
        delete_cookie("backendCookie");
        header("Location: ".base_url()."index.php/main/login");
        die();
    }

    public function securedelete($jenis, $id){

        if ($jenis == 'mahasiswa') {
            $linkedCount = 0;
        }else if ($jenis == 'kos') {
            $linkedCount = $this->getjumlahpesanankos($id);
        }else if ($jenis == 'kamar') {
            $linkedCount = $this->getjumlahpesanankamar($id);
        }else if ($jenis == 'kamardetail') {
            $linkedCount = $this->getjumlahpesanankamardetail($id);
        }

        if ($linkedCount <= 0) {
            $insertStatus = $this->main_model->delete($jenis,$id);
        } else{
            $insertStatus = "Tidak bisa menghapus. Kamar masih dalam proses pemesanan.";
        }
        
        echo $insertStatus;
    }

    public function getjumlahpesanankamardetail($idkamardetail){
        $data = $this->main_model->get_data_isikamardetail($idkamardetail);
        $count = 0;
        if ($data){
            foreach ($data as $row){
                if ($row['status'] == 'Belum Bayar') {
                    date_default_timezone_set('Asia/Jakarta');
                    $now = time();
                    $expire = strtotime($row['kadaluarsa']);
                    if ($now < $expire) {
                        $count = $count + 1;
                    }
                }else if($row['status'] == 'Belum Verifikasi'){
                    $count = $count + 1;
                }

            }
        }
        return $count;
    }

    public function insertkamardetail($idkamar){
        $status = $this->input->post('status_kamardetail');


        $data = array(
            'id_kamar' => $idkamar,
            'nama_kamardetail' => $this->input->post('nama_kamardetail'),
            'status_kamardetail' => $status,
        );

        $insertStatus = $this->main_model->insert_new_kamardetail($data);

        if ($insertStatus == 0) {
            $insertStatus = "Failed to insert Record";
        }

        echo $insertStatus;
    }

    public function getkamardetail($idkamar)
    {
        $data = $this->main_model->get_data_kamardetail($idkamar,"kamar");

        if (empty($data))
        {
            $data = [];
        }else{
            foreach ($data as &$row){
                if ($this->cekprosestransaksi($row['id_kamardetail']) == true) {
                    //jika ada transaksi
                    $row['status']='sedang dipesan';
                }else{

                    $history = $this->gethistory($row['id_kamardetail']);
                    if (empty($history)) {
                        if ($row['status_kamardetail'] == 'buka terbatas' || $row['status_kamardetail'] == 'tutup terbatas') {
                            $row['status_kamardetail'] = 'tutup';
                        }
                    }
                    $row['status']=$row['status_kamardetail'];
                }
            }
        }

        echo json_encode($data);
    }

    
    public function updatekamardetail($idkamardetail){
        $nama = $this->input->post('nama_kamardetail_update');
        $status = $this->input->post('status_kamardetail_update');
        $bulanbuka = $this->input->post('bulan_buka');

        $history = $this->gethistory($idkamardetail);

        $insertStatus = NULL;
        if ($status == "buka") {
            //rule 1: Tidak boleh open bila ada proses transaksi yang masih berlangsung
            //rule 2: Tidak boleh open bila belum mencapai bulan masuk pemesanan
            if ($this->cekprosestransaksi($idkamardetail) == true) {
                //rule 1
                $insertStatus = 'Tidak dapat membuka kamar. Masih ada proses transaksi yang belum selesai.';
            }else if (empty($history)) {
                $status_kamardetail = 'buka';
            }else {
                $terdekat = $this->gethistoryterdekat($history);
                if ($terdekat['vakum']==0) {
                    if ($this->monthformattotime($bulanbuka)<$this->monthformattotime($terdekat['tanggal_masuk'])) {
                        $status_kamardetail = 'buka terbatas';
                    }else{
                        //rule 2
                        $insertStatus = 'Tidak dapat membuka kamar di bulan tersebut karena masih dalam masa pemesanan lain. Kamar hanya bisa dibuka setelah melewati bulan masuk pemesanan.';
                    }
                }else if ($terdekat['vakum']==1) {
                    if ($this->monthformattotime($bulanbuka)<$this->monthformattotime($terdekat['tanggal_masuk'])) {
                        $status_kamardetail = 'tutup terbatas';
                    }else{
                        //rule 2
                        $insertStatus = 'Tidak dapat membuka kamar di bulan tersebut karena masih dalam masa pemesanan lain. Kamar hanya bisa dibuka setelah melewati bulan masuk pemesanan.';
                    }
                }
            }
        } else if($status == "tutup"){
            $status_kamardetail = 'tutup';
            $bulanbuka = NULL;

        }

        if ($insertStatus == NULL) {
            $data = array(
                'nama_kamardetail' => $nama,
                'status_kamardetail' =>$status_kamardetail,
                'bulan_buka' => $bulanbuka
            );

            $insertStatus = $this->main_model->update_kamardetail($data,$idkamardetail);

            if ($insertStatus == 0) {
                $insertStatus = "Gagal mengubah data";
            }
        }

        echo $insertStatus;
    }

    private function gethistory($idkamardetail=NULL){
        $data = $this->main_model->get_history($idkamardetail);
        $result = [];
        date_default_timezone_set('Asia/Jakarta');
        $now = time();
        foreach ($data as $row){
            if ($this->monthformattotime($row['tanggal_masuk'])>$now) {
                $result[] = $row;
            }
        }
        return $result;
    }

    private function gethistoryterdekat($datahistory){
        $empty = true;
        $terdekat = NULL;
        foreach ($datahistory as $row){
            if ($empty == true) {
                $terdekat = $row;
                $empty = false;
            }else if ($this->monthformattotime($row['tanggal_masuk'])<$this->monthformattotime($terdekat['tanggal_masuk'])) {
                $terdekat = $row;
            }
        }

        return $terdekat;
    }

    private function monthformattotime($bulanmasuk){
        return strtotime(DateTime::createFromFormat('M Y', $bulanmasuk)->format('Y-m-d'));
    }


    private function cekprosestransaksi($idkamardetail)
    {
        $data = $this->main_model->get_mahasiswa_by_kamardetail($idkamardetail);

        if (empty($data))
        {
            $masihproses = false;
        } else {
            $masihproses = false;
            foreach ($data as &$row){ //add & to call by reference
                unset($row['password']);
                if ($row['status']=="Belum Bayar") {
                    date_default_timezone_set('Asia/Jakarta');
                    $now = time();
                    $expire = strtotime($row['kadaluarsa']);
                    if ($now >= $expire) {
                        $row['status'] = 'Expired';
                    }
                }

                if ($row['status']=="Belum Bayar" || $row['status']=="Belum Verifikasi") {
                    $masihproses = true;
                }
                
            }
        }

        return $masihproses;
    }

}