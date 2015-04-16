<?php

class ResimIslemleri
{

    public $db;
    public $SimpleImage;

    /**
     *
     * @param $DB config.php'den
     *            $DB bağlantısı
     */
    function __construct($DB)
    {
        $this->db = $DB;
        $this->SimpleImage = new SimpleImage(); // SimpleImage ın yeteneklerini de kullanabilelim diye..
    }

    public function imageUpload($inputname, $maximum_dosya_boyutu = false)
    {
        $db = $this->db;
        
        /**
         * *
         * dosya isimleri bu şekilde olsun ki dosyalara site dışından erişimimiz de kolay ve anlamlı olsun.
         * aynı kullanıcının resimleri ard arda görünsün..
         * *
         */
        
        $dosyaAdi = idEncode($_SESSION["sirketId"]) . "_" . date("YmdHis");
        
        // $izinverilenDosyalar = array("jpg","jpeg","png","gif","bmp");
        $izinVerilenTurler = array(
            "jpg",
            "jpeg",
            "png"
        ); // sadece bunlara izin verelim.
           
        // TODO: Bu kısım, eğer fonksiyon index dışında bir yerden çağırılırsa direkt o kısma
           // klasör açabilir.
           // Yani admin/login.php'de çağırdık diyelim. "upload" ve altındaki klasörleri orada
           // açabilir.
           // $_SERVER["DOCUMENT_ROOT"]
           // Sorun çıkarırsa bunu kullanırız.
        $klasoryolu = UPLOAD_DIR . "/" . date("Y-m");
        $maximum_dosya_boyutu = $maximum_dosya_boyutu == false ? 1024 * 1024 * 2 : $maximum_dosya_boyutu;
        
        if (! file_exists($klasoryolu)) {
            mkdir($klasoryolu, 0777, true);
        }
        
        $dosyaHatasi = $_FILES[$inputname]["error"]; // integer değer döner.
        if ($dosyaHatasi != 0) {
            // echo "Dosya yüklemesinde bir hata oluştu";
            return 2; // file upload sırasında bir hata
        } else {
            $boyut = $_FILES[$inputname]["size"];
            if ($boyut > ($maximum_dosya_boyutu)) {
                return 3; // "Dosya boyutu 2 MB'tan daha büyük olamaz!";
            } else {
                $dosyaTipi = $_FILES["dosya"]["type"];
                
                $durum = false;
                for ($i = 0; $i < count($izinVerilenTurler); $i ++) {
                    if (("image/" . $izinVerilenTurler[$i]) == $dosyaTipi) {
                        $dosyaUzanti = $izinVerilenTurler[$i];
                        $durum = true;
                        break;
                    }
                }
                
                if ($durum) {
                    $path = $_FILES[$inputname]["tmp_name"];
                    if (! getimagesize($_FILES[$inputname]["tmp_name"]) & is_executable($_FILES[$inputname]["tmp_name"])) {
                        return 4; // Dosya resim değil
                    } else {
                        $ciktiYolu = $klasoryolu . "/" . $dosyaAdi . "." . $dosyaUzanti;
                        if (copy($path, $ciktiYolu)) {
                            $update = $db->exec("UPDATE sirket SET logo = '$ciktiYolu' WHERE id = " . $_SESSION["sirketId"]);
                            if ($update) {
                                if($this->imageResize($ciktiYolu, $klasoryolu . "/" . $dosyaAdi)) {
                                    return true;
                                } else {
                                return 5; // resize hatası
                                }
                            } else {
                                return 6; // update hatası
                            }
                        } else {
                            return 7; // copy fonksiyonu hatası
                        }
                    }
                } else {
                    return 8; // "İzin verilmeyen dosya türü
                }
            }
        }
    }

    public function imageResize($dosyaYolu, $ciktiIsım)
    {
        list ($genislik, $yukseklik) = getimagesize($dosyaYolu);
        $oran = $yukseklik / $genislik;
        if ($oran > 3 & $oran < 1 / 3) {
            return 2; // logo istedik ulan banner değil! gibi birşey diyebiliriz burada :)
        } else {
            $istenenGenislikler = array(
                "100",
                "200",
                "400"
            );
            $image = $this->SimpleImage;
            for ($i = 0; $i < count($istenenGenislikler); $i ++) {
                $image->load($dosyaYolu);
                $image->resizeToWidth($istenenGenislikler[$i]);
                $image->save($ciktiIsım . "_" . $istenenGenislikler[$i] . ".jpg");
            }
        }
    }
}


?>
