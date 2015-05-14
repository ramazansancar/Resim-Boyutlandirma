<?php session_start();

if (isset($_SESSION["cropped"])) {
    var_dump($_SESSION["cropped"]);
    //unset($_SESSION["cropped"]);
}
?>
<html>
<head>
    <title>Birşeyler Birşeyler..</title>
</head>
<body>
<script>
    function target_popup(form) {
        window.open('', 'formpopup', 'width=950,height=900,resizeable,scrollbars');
        form.target = 'formpopup';
    }

    function HandlePopupResult(result) {
        if (result != null) {
            if (result[0] == "OK" && result[1] != "" && result[2] != "") {
                alert("başarılı");
                document.getElementById("croppedImage").value = result[1] + result[2];
                document.getElementById("info").innerHTML = '<img src="' + result[1] + '_100' + result[2] + '">';
                //document.getElementById('imageCrop').action = "islemtamam.php";
                //document.getElementById("imageCrop").style.visibility = "hidden";
            }
        }
    }

    HandlePopupResult();
</script>
<form id="imageCrop" onsubmit="target_popup(this)" name="photo" enctype="multipart/form-data"
      action="<?php echo "index.php"; ?>" method="post">
    <input type="file" name="image" size="30"/> <input type="submit" name="upload" value="Upload"/>
    <!-- burada veri geldiği zaman farklı bir formu sabmit eden button ile değişecek !-->
</form>
<form id="hiddenForm" method="post" action="islemtamam.php">
    <div id="hidden">

    </div>
    <div id="info" class="alert alert-danger">
        <?php
        if (isset($_SESSION["cropped"][0]) && $_SESSION["cropped"][0]["thumbnail"] == true) {
            echo '<img src="' . $_SESSION["cropped"][0]["dir"] . $_SESSION["cropped"][0]["org"] . "_" . $_SESSION["thumb_width3"] . $_SESSION["cropped"][0]["ext"] . '">';
        } else {
            echo "<p>Resim Yüklenmedi</p>";
        }
        ?>
    </div>
    <input type="text" name="bir">
    <input type="text" name="iki">
    <input type="text" name="üç">
    <input type="text" name="dört">
    <input type="text" name="beş">

    <input type="hidden" id="croppedImage" name="croppedImage" value="">
    <input type="submit" value="Formu Gönder">
</form>
</body>
</html>