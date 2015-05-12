<html>
<head>
    <title>Birşeyler Birşeyler..</title>
</head>
<body>
<script>
    function target_popup(form) {

        window.open('', 'formpopup', 'width=950,height=900,resizeable,scrollbars');
        form.target = 'formpopup';

        $('input[type="submit"]').disable();
    }

    function formDisable(formId) {
        var form = document.getElementById(formId);
        var elements = form.elements;
        for (var i = 0, len = elements.length; i < len; ++i) {
            elements[i].disabled = true;
        }
    }

    function HandlePopupResult(result) {
        if (result != null) {
            alert("result of popup is: " + result);
        } else {
        }
    }

    HandlePopupResult();
</script>
<form id="imageCrop" onsubmit="target_popup(this)" name="photo" enctype="multipart/form-data"
      action="<?php echo "index.php"; ?>"
      method="post">
    <input type="file" name="image" size="30"/> <input type="submit" name="upload" value="Upload"/>
</form>
</body>
</html>