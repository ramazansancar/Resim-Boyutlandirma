<?php

class ResimIslemleri
{
    public $SimpleImage;

    /**
     *
     * @param $DB config.php'den
     *            $DB bağlantısı
     */
    function __construct()
    {
        $this->SimpleImage = new SimpleImage(); // SimpleImage ın yeteneklerini de kullanabilelim diye..
    }

    /**
     * @param $inputname
     * @param bool $imageResize
     * @return int|string
     */
    public static function imageUpload($inputname, $imageResize = false, $turler = false)
    {

        /**
         * *
         * dosya isimleri bu şekilde olsun ki dosyalara site dışından erişimimiz de kolay ve anlamlı olsun.
         * aynı kullanıcının resimleri ard arda görünsün..
         * *
         */

        $dosyaAdi = idEncode($_SESSION["sirketId"]) . "_" . date("YmdHis");

        // $izinverilenDosyalar = array("jpg","jpeg","png","gif","bmp");

        if ($turler != false && is_array($turler)) {
            $izinVerilenTurler = $turler;
        } else {
            $izinVerilenTurler = array(
                "jpg",
                "jpeg",
                "png"
            );
        }

        // TODO: Bu kısım, eğer fonksiyon index dışında bir yerden çağırılırsa direkt o kısma
        // klasör açabilir.
        // Yani admin/login.php'de çağırdık diyelim. "upload" ve altındaki klasörleri orada
        // açabilir.
        // $_SERVER["DOCUMENT_ROOT"]
        // Sorun çıkarırsa bunu kullanırız.
        $klasoryolu = UPLOAD_DIR . "/" . date("Y-m");
        $maximum_dosya_boyutu = 1024 * 1024 * 2;

        if (!file_exists($klasoryolu)) {
            mkdir($klasoryolu, 0777, true);
        }

        $dosyaHatasi = $_FILES[$inputname]["error"]; // integer değer döner.
        if ($dosyaHatasi != 0) {
            return array(false,2); // file upload sırasında bir hata
        } else {
            $boyut = $_FILES[$inputname]["size"];
            if ($boyut > ($maximum_dosya_boyutu)) {
                return array(false,3); // "Dosya boyutu belirtilen değerden daha büyük olamaz!";
            } else {
                $dosyaTipi = pathinfo($_FILES[$inputname]["name"]);
                $dosyaTipi = $dosyaTipi['extension'];

                $durum = false;
                for ($i = 0; $i < count($izinVerilenTurler); $i++) {
                    if ($izinVerilenTurler[$i] == $dosyaTipi) {
                        $dosyaUzanti = $izinVerilenTurler[$i];
                        $durum = true;
                        break;
                    }
                }

                if ($durum) {
                    $path = $_FILES[$inputname]["tmp_name"];
                    if (!getimagesize($_FILES[$inputname]["tmp_name"]) & is_executable($_FILES[$inputname]["tmp_name"])) {
                        return array(false,4); // Dosya resim değil
                    } else {
                        $ciktiYolu = $klasoryolu . "/" . $dosyaAdi . "." . $dosyaUzanti;
                        if (copy($path, $ciktiYolu)) {
                            if ($imageResize != false && is_array($imageResize)) {
                                $resize = ResimIslemleri::imageResize($ciktiYolu,$dosyaAdi,$imageResize);
                                if ($resize[0] == true) {
                                    return array(true, $ciktiYolu);
                                } else {
                                    return array(false, 5); // resize hatası
                                }
                            } else {
                                return array(true, $ciktiYolu); // dosya tek kopya halinde yüklendi
                            }
                        } else {
                            return array(false,6); // copy fonksiyonu hatası
                        }
                    }
                } else {
                    return array(false,7); // "İzin verilmeyen dosya türü
                }
            }
        }
    }

    /**
     * @param $dosyaYolu
     * @param $ciktiIsım
     * @param $istenenGenislikler
     * @return mixed
     */
    public static function imageResize($dosyaYolu, $ciktiIsım, $istenenGenislikler)
    {
        list ($genislik, $yukseklik) = getimagesize($dosyaYolu);
        $oran = $yukseklik / $genislik;
        if ($oran > 3 & $oran < 1 / 3) {
            return array(false,2); // logo istedik ulan banner değil! gibi birşey diyebiliriz burada :)
        } else {
            $obj = new static();
            $SimpleImage = $obj->SimpleImage;
            $dosyaTipi = pathinfo($dosyaYolu);
            $dosyaTipi = $dosyaTipi['extension'];
            for ($i = 0; $i < count($istenenGenislikler); $i++) {
                $SimpleImage->load($dosyaYolu);
                $SimpleImage->resizeToWidth($istenenGenislikler[$i]);
                $SimpleImage->save($ciktiIsım . "_" . $istenenGenislikler[$i] . ".$dosyaTipi");
            }
            return array(true,$dosyaYolu);
        }
    }
}


?>