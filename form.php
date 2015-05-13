<?php session_start();

if (isset($_SESSION["tekin"])) {
    var_dump($_SESSION["tekin"]);
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
            if (result[0] == "OK" && result[1] != "") {
                alert("başarılı");
                document.getElementById("hidden").innerHTML = '<input type="hidden" name="croppedImage" value="' + result[1] + '">';
                document.getElementById('imageCrop').action = "islemtamam.php";
                document.getElementById("imageCrop").style.visibility = "hidden";
            }
        }
    }

    HandlePopupResult();
</script>
<form id="imageCrop" onsubmit="target_popup(this)" name="photo" enctype="multipart/form-data"
      action="<?php echo "index.php"; ?>"
      method="post">
    <input type="file" name="image" size="30"/> <input type="submit" name="upload" value="Upload"/>
    <!-- burada veri geldiği zaman farklı bir formu sabmit eden button ile değişecek !-->
</form>
<form id="hiddenForm" method="post" action="islemtamam.php" style="display: none;">
    <div id="hidden">

    </div>
    <input type="submit" value="Formu Gönder">
</form>
</body>
</html>