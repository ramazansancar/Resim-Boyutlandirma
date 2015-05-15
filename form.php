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

<script src="tekin.js" type="text/javascript"></script>
<script>
    HandlePopupResult();
</script>

<form id="imageCrop" onsubmit="target_popup(this)" name="photo" enctype="multipart/form-data"
      action="index.php" method="post">
    <input type="file" name="image" size="30"/> <input type="submit" name="upload" value="Upload"/>
</form>
<form id="hiddenForm" method="post" action="islemtamam.php">
    <div id="hidden">

    </div>
    <div id="info">
        <?php
        if (isset($_SESSION["cropped"][0]) && $_SESSION["cropped"][0]["thumbnail"] == true) {
            echo '<img src="' . $_SESSION["cropped"][0]["dir"] . $_SESSION["cropped"][0]["org"] . "_" . $_SESSION["thumb_width2"] . $_SESSION["cropped"][0]["ext"] . '">';
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