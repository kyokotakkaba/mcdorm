<?php
class Front extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('front_model');
        $this->load->helper('url_helper');
    }

    public function index()
    {
        $this->home();
    }



    public function login()
    {
        $this->load->view('front/login');
    }

    public function loginvalidation()
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
            $username=$this->input->post('username');
            $password=$this->input->post('password');

            $row = $this->front_model->get_mahasiswa_login($username,md5($password));

            if (!empty($row)) {
                //echo "login admin success! ";

                $this->load->helper('cookie');

                $cookie= array(
                 'name'   => 'frontCookie',
                 'value'  => $username,
                 'expire' => '0',
             );
                $this->input->set_cookie($cookie);

                $cookie= array(
                    'name'   => 'frontNama',
                    'value'  => $row['nama_mahasiswa'],
                    'expire' => '0',
                );
                $this->input->set_cookie($cookie);

                echo "1";
            }else{
                echo "login failed";
            }

            //echo "<br>username: ".$data['username']."<br>";
            //echo "password: ".$data['password']."<br>";
        }
    }

    public function checkcookiemahasiswa()
    {
        $this->load->helper('cookie');
        if ($this->input->cookie('frontCookie',true)!=NULL) {
            return true;
        }else{
            return false;
        } 
    }

    public function getmahasiswaarray($id)
    {
        $data = $this->front_model->get_mahasiswa($id);

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

    public function home()
    {
        $this->load->view('templates/front/header');
        //$this->load->view('templates/front/control');
        $this->load->view('templates/front/navbar');
        $this->load->view('front/home');
        $this->load->view('templates/front/js');
        $this->load->view('templates/front/footer');
        
    }

    public function changepassword()
    {
        $this->load->view('templates/front/header');
        //$this->load->view('templates/front/control');
        $this->load->view('templates/front/navbar');
        $this->load->view('front/changepassword');
        $this->load->view('templates/front/js');
        $this->load->view('templates/front/footer');
        
    }

    public function updatepassword($id){
        $oldpassword = md5($this->input->post('oldpassword'));
        $data = array(
            'password' => md5($this->input->post('newpassword'))
        );

        $insertStatus = $this->front_model->update_password($data,$id,$oldpassword);
        echo $insertStatus;
    }

    public function about()
    {
        $this->load->view('templates/front/header');
        //$this->load->view('templates/front/control');
        $this->load->view('templates/front/navbar');
        $this->load->view('front/about');
        $this->load->view('templates/front/js');
        $this->load->view('templates/front/footer');
        
    }

    public function search(){
        if ($this->checkcookiemahasiswa()) {
            $this->load->view('templates/front/header');
            //$this->load->view('templates/front/control');
            $this->load->view('templates/front/navbar');
            $this->load->view('front/search');
            $this->load->view('templates/front/js');
            $this->load->view('templates/front/footer');
        }else{
            $this->login();
        }
    }

    public function getjumlahpesananKamar($idkamar){
        $data = $this->front_model->get_data_isikamar($idkamar);
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

    public function getallkamar(){
        $data = $this->front_model->get_all_kamar();
        $result = [];
        date_default_timezone_set('Asia/Jakarta');
        $now = time();
        foreach ($data as &$row){ //add & to call by reference
            $datakamardetail = $this->front_model->get_data_kamardetail($row['id_kamar'],'kamar');
            $countKuotaKosong = 0;
            $adaVakum = false;
            foreach ($datakamardetail as $row2) {
                if ($this->cekprosestransaksi($row2['id_kamardetail']) == false) {
                    $history = $this->gethistory($row2['id_kamardetail']);
                    if (empty($history)){
                        //kamar kosong
                        if ($row2['status_kamardetail']=='buka') {
                            $countKuotaKosong = $countKuotaKosong + 1;
                        }
                    }else{
                        if ($row2['status_kamardetail']!='tutup') {


                            //bulan buka
                            $bulanbuka = $now;
                            if ($row2['bulan_buka']!=NULL) {
                                if ($this->monthformattotime($row2['bulan_buka'])>$now) {
                                    $bulanbuka = $this->monthformattotime($row2['bulan_buka']);
                                }
                            }

                            $terdekat = $this->gethistoryterdekat($history);
                            if ($row2['status_kamardetail']=='buka terbatas') {
                                if (strtotime('+2 month',$bulanbuka) < $this->monthformattotime($terdekat['tanggal_masuk'])) {
                                    $adaVakum = true;
                                }
                            }else if ($row2['status_kamardetail']=='tutup terbatas') {
                                if (strtotime('+2 month',$bulanbuka) < $this->monthformattotime($terdekat['tanggal_masuk'])) {
                                    if ($terdekat['vakum']==1) {
                                        $adaVakum = true;
                                    }
                                }
                                
                            }
                        }
                    } 
                }
            }

            if ($countKuotaKosong>0 || $adaVakum == true) {
                $row['kuota'] = $countKuotaKosong;
                $result[] = $row;
            }
        }
        if (empty($result)) {
            $result = NULL;
        }
        echo json_encode($result);
    }

    public function getsearch(){
        if ($this->input->post('fasilitaskos')) {
            $fasilitaskos = $this->input->post('fasilitaskos');
        }else{
            $fasilitaskos = NULL;
        }
        if ($this->input->post('fasilitaskamar')) {
            $fasilitaskamar = $this->input->post('fasilitaskamar');
        }else{
            $fasilitaskamar = NULL;
        }
        $harga = explode(';',$this->input->post('harga'));
        $jarak = explode(';',$this->input->post('jarak'));
        $jarak[0] = $jarak[0]*1000;
        $jarak[1] = $jarak[1]*1000;
        $sort = $this->input->post('sort');
        $gender = $this->input->post('gender');

        // echo $gender."<br>".$harga[0]."<br>".$harga[1]."<br>";
        // var_dump($fasilitaskos);

        $data = $this->front_model->get_search_kamar((int)$harga[0],(int)$harga[1],(int)$jarak[0],(int)$jarak[1],$sort,$gender);

        $result = [];
        date_default_timezone_set('Asia/Jakarta');
        $now = time();
        foreach ($data as &$row){ //add & to call by reference
            $dataFasilitasKos = explode(',',$row['fasilitas_kos']);
            $dataFasilitasKamar = explode(',',$row['fasilitas_kamar']);
            $fasilitasKosCocok = true;
            $fasilitasKamarCocok = true;
            if ($fasilitaskos!=NULL) {
                foreach ($fasilitaskos as $value) {
                    if (!in_array($value, $dataFasilitasKos)) {
                        $fasilitasKosCocok = false;
                    }
                }
            }
            
            if ($fasilitaskamar!=NULL){
                foreach ($fasilitaskamar as $value) {
                    if (!in_array($value, $dataFasilitasKamar)) {
                        $fasilitasKamarCocok = false;
                    }
                }
            }





            // cek kuota dan vakum
            $datakamardetail = $this->front_model->get_data_kamardetail($row['id_kamar'],'kamar');
            $countKuotaKosong = 0;
            $adaVakum = false;
            foreach ($datakamardetail as $row2) {
                if ($this->cekprosestransaksi($row2['id_kamardetail']) == false) {
                    $history = $this->gethistory($row2['id_kamardetail']);
                    if (empty($history)){
                        //kamar kosong
                        if ($row2['status_kamardetail']=='buka') {
                            $countKuotaKosong = $countKuotaKosong + 1;
                        }
                    }else{
                        if ($row2['status_kamardetail']!='tutup') {


                            //bulan buka
                            $bulanbuka = $now;
                            if ($row2['bulan_buka']!=NULL) {
                                if ($this->monthformattotime($row2['bulan_buka'])>$now) {
                                    $bulanbuka = $this->monthformattotime($row2['bulan_buka']);
                                }
                            }

                            $terdekat = $this->gethistoryterdekat($history);
                            if ($row2['status_kamardetail']=='buka terbatas') {
                                if (strtotime('+2 month',$bulanbuka) < $this->monthformattotime($terdekat['tanggal_masuk'])) {
                                    $adaVakum = true;
                                }
                            }else if ($row2['status_kamardetail']=='tutup terbatas') {
                                if (strtotime('+2 month',$bulanbuka) < $this->monthformattotime($terdekat['tanggal_masuk'])) {
                                    if ($terdekat['vakum']==1) {
                                        $adaVakum = true;
                                    }
                                }
                                
                            }
                        }
                    } 
                }
            }

            if ($fasilitasKosCocok && $fasilitasKamarCocok) {
                if ($countKuotaKosong>0 || $adaVakum == true) {
                    $row['kuota'] = $countKuotaKosong;
                    $result[] = $row;
                }
            }
        }
        if (empty($result)) {
            $result = NULL;
        }
        echo json_encode($result);
    }

    public function detail(){
        if ($this->checkcookiemahasiswa()) {
            $this->load->view('templates/front/header');
            //$this->load->view('templates/front/control');
            $this->load->view('templates/front/navbar');
            $this->load->view('front/detail');
            $this->load->view('templates/front/js');
            $this->load->view('templates/front/footer');
        }else{
            $this->login();
        }
    }

    public function getdetail($idkos,$kamar = NULL){
        if ($kamar == NULL) {
            $data = $this->front_model->get_data_kos($idkos,'user_kos');
        }else{
            $data = $this->front_model->get_data_kos($idkos, 'kamar');

            $result = [];
            date_default_timezone_set('Asia/Jakarta');
            $now = time();
            $nowMonthString = date("F Y");
            foreach ($data as &$row){ //add & to call by reference
                $datakamardetail = $this->front_model->get_data_kamardetail($row['id_kamar'],'kamar');
                $countKuotaKosong = 0;
                $adaVakum = false;
                $resultkamardetail = [];
                foreach ($datakamardetail as &$row2) {
                    if ($this->cekprosestransaksi($row2['id_kamardetail']) == false) {
                        $history = $this->gethistory($row2['id_kamardetail']);
                        if (empty($history)){
                        //kamar kosong
                            if ($row2['status_kamardetail']=='buka') {
                                $countKuotaKosong = $countKuotaKosong + 1;


                                //bulan buka
                                $bulanbukaString = $nowMonthString;
                                if ($row2['bulan_buka']!=NULL) {
                                    if ($this->monthformattotime($row2['bulan_buka'])>$now) {
                                        $bulanbukaString = $row2['bulan_buka'];
                                    }
                                }

                                $row2['bulan_buka'] = $bulanbukaString;
                                $row2['bulan_tutup'] = NULL;
                                $resultkamardetail[] = $row2;
                            }
                        }else{
                            if ($row2['status_kamardetail']!='tutup') {


                                //bulan buka
                                $bulanbuka = $now;
                                $bulanbukaString = $nowMonthString;
                                if ($row2['bulan_buka']!=NULL) {
                                    if ($this->monthformattotime($row2['bulan_buka'])>$now) {
                                        $bulanbuka = $this->monthformattotime($row2['bulan_buka']);
                                        $bulanbukaString = $row2['bulan_buka'];
                                    }
                                }

                                $terdekat = $this->gethistoryterdekat($history);
                                if ($row2['status_kamardetail']=='buka terbatas') {
                                    if (strtotime('+2 month',$bulanbuka) < $this->monthformattotime($terdekat['tanggal_masuk'])) {
                                        $adaVakum = true;

                                        $row2['bulan_buka'] = $bulanbukaString;
                                        $row2['bulan_tutup'] = $terdekat['tanggal_masuk'];
                                        $resultkamardetail[] = $row2;


                                    }
                                }else if ($row2['status_kamardetail']=='tutup terbatas') {
                                    if (strtotime('+2 month',$bulanbuka) < $this->monthformattotime($terdekat['tanggal_masuk'])) {
                                        if ($terdekat['vakum']==1) {
                                            $adaVakum = true;

                                            $row2['bulan_buka'] = $bulanbukaString;
                                            $row2['bulan_tutup'] = $terdekat['tanggal_masuk'];
                                            $resultkamardetail[] = $row2;
                                        }
                                    }

                                }
                            }
                        } 
                    }
                }

                if ($countKuotaKosong>0 || $adaVakum == true) {
                    $row['kuota'] = $countKuotaKosong;
                    $row['kamardetail'] = $resultkamardetail;
                    $result[] = $row;

                }
            }
        }

        if (empty($data))
        {
            $data = [];
        }

        echo json_encode($data);
    }


    public function order($idmahasiswa){
        if ($this->cekprosestransaksi($this->input->post('idkamardetail')) == false){
            $statusMahasiswa = $this->getmahasiswaarray($idmahasiswa);
            if ($statusMahasiswa['status']=='Belum Pesan' || $statusMahasiswa['status']=='Terpesan' || $statusMahasiswa['status']=='Expired' || $statusMahasiswa['status']=='Batal') {
                date_default_timezone_set('Asia/Jakarta');
                $kadaluarsa = date("Y-m-d H:i:s", strtotime('+24 hours'));

                $data = array(
                    'status' => 'Belum Bayar',
                    'id_kos' => $this->input->post('idkos'),
                    'id_kamar' => $this->input->post('idkamar'),
                    'tanggal_masuk' => $this->input->post('tanggalmasuk'),
                    'kadaluarsa' => $kadaluarsa,
                    'vakum' => $this->input->post('vakum'),
                    'lama_pemesanan' => $this->input->post('lamapemesanan'),
                    'id_kamardetail' => $this->input->post('idkamardetail')
                );

                $insertStatus = $this->front_model->update_mahasiswa($data,$idmahasiswa);
            }else{
                $insertStatus = "Tidak bisa memesan, selesaikan dulu proses pemesanan yang masih berlangsung";
            }
        }else{
            $insertStatus = "Tidak bisa memesan, kamar sudah dipesan.";
        }
        
        echo $insertStatus;
    }

    public function payment(){
        if ($this->checkcookiemahasiswa()) {
            $this->load->view('templates/front/header');
            //$this->load->view('templates/front/control');
            $this->load->view('templates/front/navbar');
            $this->load->view('front/payment');
            $this->load->view('templates/front/js');
            $this->load->view('templates/front/footer');
        }else{
            $this->login();
        }
    }


    public function thankyou(){
        if ($this->checkcookiemahasiswa()) {
            $this->load->view('templates/front/header');
            //$this->load->view('templates/front/control');
            $this->load->view('templates/front/navbar');
            $this->load->view('front/thankyou');
            $this->load->view('templates/front/js');
            $this->load->view('templates/front/footer');

        }else{
            $this->login();
        }
    }

    public function uploadimagepayment($idmahasiswa){
        $ds          = DIRECTORY_SEPARATOR;
        $targetPath = getcwd().$ds.'photos'.$ds.'payment'.$ds;
        $filename = $idmahasiswa;

        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0755, true);
        }

        if (!empty($_FILES)) {
            $tempFile = $_FILES['file']['tmp_name'];
            $targetFile =  $targetPath. $filename;
            $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

            //image resize
            $maxWidth = 1024;
            list($width, $height, $type, $attr) = getimagesize($tempFile);
            if ($width > $maxWidth) {
                $ratio = $width/$height;
                $new_width = $maxWidth;
                $new_height = $maxWidth/$ratio;
            }else{
                $new_width = $width;
                $new_height = $height;
            }
            $src = imagecreatefromstring(file_get_contents($tempFile));
            $dst = imagecreatetruecolor($new_width,$new_height);
            imagecopyresampled($dst,$src,0,0,0,0,$new_width,$new_height,$width,$height);
            imagedestroy($src);
            imagejpeg($dst, $tempFile, 100);
            imagedestroy($dst);

            move_uploaded_file($tempFile,$targetFile.".jpg");

        //Status Bayar
            $statusMahasiswa = $this->getmahasiswaarray($idmahasiswa);
            if ($statusMahasiswa['status']=='Belum Bayar'){
                $data = array(
                    'status' => 'Belum Verifikasi',
                    'kadaluarsa'=> NULL
                );
                $insertStatus = $this->front_model->update_mahasiswa($data,$idmahasiswa);
                if ($insertStatus == 1) {
                    header("Location: ".base_url()."index.php/thankyou");
                    die();
                }else{
                    echo $insertStatus;
                }
            }else{
                header("Location: ".base_url()."index.php/status");
                die();
            }


        }else{
            echo "Upload failed";
        }
    }

    public function status(){
        if ($this->checkcookiemahasiswa()) {
            $this->load->view('templates/front/header');
            //$this->load->view('templates/front/control');
            $this->load->view('templates/front/navbar');
            $this->load->view('front/status');
            $this->load->view('templates/front/js');
            $this->load->view('templates/front/footer');
        }else{
            $this->login();
        }
    }

    private function gethistory($idkamardetail=NULL){
        $data = $this->front_model->get_history($idkamardetail);
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

    public function gethistorymahasiswa($id)
    {
        $data = $this->front_model->get_history_mahasiswa($id);
        if (empty($data))
        {
            $data = [];
        }else{
            echo json_encode($data);
        }
    }



    public function getmahasiswa($id)
    {
        $data = $this->front_model->get_mahasiswa($id);

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

        echo json_encode($data);
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

    public function cancelorder($id){
        $data = array(
            'status' => 'Batal',
            'kadaluarsa'=> NULL
        );

        $insertStatus = $this->front_model->update_mahasiswa($data,$id);

        if ($insertStatus == 1) {
            $this->deleteimagepayment($id);
        }
        echo $insertStatus;
    }

    public function logout(){
        $this->load->helper('cookie');
        delete_cookie("frontCookie");
        header("Location: ".base_url()."index.php/login");
        die();
    }


    private function cekprosestransaksi($idkamardetail)
    {
        $data = $this->front_model->get_mahasiswa_by_kamardetail($idkamardetail);

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

    private function monthformattotime($bulanmasuk){
        return strtotime(DateTime::createFromFormat('M Y', $bulanmasuk)->format('Y-m-d'));
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

}