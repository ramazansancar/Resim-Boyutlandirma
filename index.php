<?php

session_start();

require_once 'upload_crop.php';
require_once 'SimpleImage.php';
require_once 'class.image.php';



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
    <meta name="generator" content="WebMotionUK"/>
    <title>WebMotionUK - PHP &amp; Jquery image upload &amp; crop</title>
    <script type="text/javascript" src="js/jquery-pack.js"></script>
    <script type="text/javascript" src="js/jquery.imgareaselect.min.js"></script>
</head>
<body>
<?php

//Check to see if any images with the same name already exist
if (file_exists($large_image_location)) {
    if (file_exists($thumb_image_location)) {
        $thumb_photo_exists = "<img src=\"" . $upload_path . $thumb_image_name . "_" . $thumb_width . $_SESSION['user_file_ext'] . "\" alt=\"Thumbnail Image\"/>";
    } else {
        $thumb_photo_exists = "";
    }
    $large_photo_exists = "<img src=\"" . $upload_path . $large_image_name . $_SESSION['user_file_ext'] . "\" alt=\"Large Image\"/>";
} else {
    $large_photo_exists = "";
    $thumb_photo_exists = "";
}

if (isset($_POST["upload"])) {

    if (isset($_SESSION["cropped"])) {
        unlink($_SESSION["cropped"][0]["dir"] . $_SESSION["cropped"][0]["org"] . $_SESSION["cropped"][0]["ext"]);
        if ($_SESSION["cropped"][0]["thumbnail"] == true) {
            unlink($_SESSION["cropped"][0]["dir"] . $_SESSION["cropped"][0]["org"] . "_" . $_SESSION["thumb_width"] . $_SESSION["cropped"][0]["ext"]);
            unlink($_SESSION["cropped"][0]["dir"] . $_SESSION["cropped"][0]["org"] . "_" . $_SESSION["thumb_width2"] . $_SESSION["cropped"][0]["ext"]);
            unlink($_SESSION["cropped"][0]["dir"] . $_SESSION["cropped"][0]["org"] . "_" . $_SESSION["thumb_width3"] . $_SESSION["cropped"][0]["ext"]);
            $_SESSION["cropped"][0]["thumbnail"] == false;
        }
    }
    $_SESSION["Index"] = 0;

    //Get the file information
    $userfile_name = $_FILES['image']['name'];
    $userfile_tmp = $_FILES['image']['tmp_name'];
    $userfile_size = $_FILES['image']['size'];
    $userfile_type = $_FILES['image']['type'];
    $filename = basename($_FILES['image']['name']);
    $file_ext = strtolower(substr($filename, strrpos($filename, '.') + 1));

    //Only process if the file is a JPG, PNG or GIF and below the allowed limit
    if ((!empty($_FILES["image"])) && ($_FILES['image']['error'] == 0)) {

        foreach ($allowed_image_types as $mime_type => $ext) {
            //loop through the specified image types and if they match the extension then break out
            //everything is ok so go and check file size
            if ($file_ext == $ext && $userfile_type == $mime_type) {
                $error = "";
                break;
            } else {
                $error = "Only <strong>" . $image_ext . "</strong> images accepted for upload<br />";
            }
        }
        //check if the file size is above the allowed limit
        if ($userfile_size > ($max_file * 1048576)) {
            $error .= "Images must be under " . $max_file . "MB in size";
        }

    } else {
        $error = "Select an image for upload";
    }
    //Everything is ok, so we can upload the image.
    if (strlen($error) == 0) {

        if (isset($_FILES['image']['name'])) {
            //this file could now has an unknown file extension (we hope it's one of the ones set above!)
            $large_image_location = $upload_path . $large_image_name . "." . $file_ext;
            $thumb_image_location = $upload_path . $thumb_image_name . "_" . $thumb_width . "." . $file_ext;

            //put the file ext in the session so we know what file to look for once its uploaded
            $_SESSION['user_file_ext'] = "." . $file_ext;

            copy($userfile_tmp, $large_image_location);
            chmod($large_image_location, 0777);

            $width = getWidth($large_image_location);
            $height = getHeight($large_image_location);
            //Scale the image if it is greater than the width set above
            if ($width > $max_width) {
                $scale = $max_width / $width;
                $uploaded = resizeImage($large_image_location, $width, $height, $scale);
            } else {
                $scale = 1;
                $uploaded = resizeImage($large_image_location, $width, $height, $scale);
            }
            //Delete the thumbnail file so the user can create a new one
            if (file_exists($thumb_image_location)) {
                unlink($thumb_image_location);
            }
        }

        $_SESSION["cropped"][$_SESSION["Index"]]["thumbnail"] = false;
        $_SESSION["cropped"][$_SESSION["Index"]]["dir"] = $upload_dir . "/";
        $_SESSION["cropped"][$_SESSION["Index"]]["org"] = idEncode($_SESSION["sirketId"]) . "_" . $_SESSION['random_key'];
        $_SESSION["cropped"][$_SESSION["Index"]]["ext"] = $_SESSION["user_file_ext"];

        //Refresh the page to show the new uploaded image
        $_SESSION["sifirla"] = true;
        header("location:index.php");
        exit();
    }
}

if (isset($_POST["upload_thumbnail"]) && strlen($large_photo_exists) > 0) {

    $_SESSION["sifirla"] = false;
    $_SESSION["cropped"][$_SESSION["Index"]]["thumbnail"] = true;

    //Get the new coordinates to crop the image.
    $x1 = $_POST["x1"];
    $y1 = $_POST["y1"];
    $x2 = $_POST["x2"];
    $y2 = $_POST["y2"];
    $w = $_POST["w"];
    $h = $_POST["h"];

    //Scale the image to the thumb_width set above
    $scale = $thumb_width / $w;
    $cropped = resizeThumbnailImage($upload_path . $thumb_image_name . "_" . $thumb_width . $_SESSION['user_file_ext'], $large_image_location, $w, $h, $x1, $y1, $scale);

    $cropped = resizeThumbnailImage($upload_path . $thumb_image_name . "_" . $thumb_width2 . $_SESSION['user_file_ext'], $large_image_location, $w, $h, $x1, $y1, $scale / ($thumb_width / $thumb_width2));

    $cropped = resizeThumbnailImage($upload_path . $thumb_image_name . "_" . $thumb_width3 . $_SESSION['user_file_ext'], $large_image_location, $w, $h, $x1, $y1, $scale / ($thumb_width / $thumb_width3));

    //Reload the page again to view the thumbnail
    header("location:index.php");
    exit();
}


if ($_GET['a'] == "delete" && strlen($_GET['t']) > 0 && strlen($_GET['e']) > 0) {
//get the file locations
    $large_image_location = $upload_path . $large_image_prefix . $_GET['t'] . $_GET['e'];
    $thumb_image_location = $upload_path . $thumb_image_prefix . $_GET['t'] . "_" . $thumb_width . $_GET['e'];
    $thumb_image_location2 = $upload_path . $thumb_image_prefix . $_GET['t'] . "_" . $thumb_width2 . $_GET['e'];
    $thumb_image_location3 = $upload_path . $thumb_image_prefix . $_GET['t'] . "_" . $thumb_width3 . $_GET['e'];

    if (file_exists($large_image_location)) {
        unlink($large_image_location);
    }
    if (file_exists($thumb_image_location)) {
        unlink($thumb_image_location);
    }

    if (file_exists($thumb_image_location2)) {
        unlink($thumb_image_location2);
    }
    if (file_exists($thumb_image_location3)) {
        unlink($thumb_image_location3);
    }

    unset($_SESSION["cropped"]);

    //header("location:form.php");
    exit();
}


//Only display the javacript if an image has been uploaded
if (strlen($large_photo_exists) > 0) {
    $current_large_image_width = getWidth($large_image_location);
    $current_large_image_height = getHeight($large_image_location);?>
    <script type="text/javascript">
        function preview(img, selection) {
            var scaleX =
            <?php echo $thumb_width;?> /
            selection.width;
            var scaleY =
            <?php echo $thumb_height;?> /
            selection.height;

            $('#thumbnail + div > img').css({
                width: Math.round(scaleX * <?php echo $current_large_image_width;?>) + 'px',
                height: Math.round(scaleY * <?php echo $current_large_image_height;?>) + 'px',
                marginLeft: '-' + Math.round(scaleX * selection.x1) + 'px',
                marginTop: '-' + Math.round(scaleY * selection.y1) + 'px'
            });
            $('#x1').val(selection.x1);
            $('#y1').val(selection.y1);
            $('#x2').val(selection.x2);
            $('#y2').val(selection.y2);
            $('#w').val(selection.width);
            $('#h').val(selection.height);
        }

        $(document).ready(function () {
            $('#save_thumb').click(function () {
                var x1 = $('#x1').val();
                var y1 = $('#y1').val();
                var x2 = $('#x2').val();
                var y2 = $('#y2').val();
                var w = $('#w').val();
                var h = $('#h').val();
                if (x1 == "" || y1 == "" || x2 == "" || y2 == "" || w == "" || h == "") {
                    alert("Önce seçim yapmalısınız");
                    return false;
                } else {
                    return true;
                }
            });
        });

        $(window).load(function () {
            $('#thumbnail').imgAreaSelect({
                aspectRatio: '1:<?php echo $thumb_height/$thumb_width;?>',
                onSelectChange: preview
            });
        });

        function CloseMySelf(sender) {
            try {
                var result = sender.getAttribute("result");
                var img = sender.getAttribute("img");
                var ext = sender.getAttribute("ext");
                var dizi = [result, img, ext];

                window.opener.HandlePopupResult(dizi);
            }
            catch (err) {
            }
            window.close();
            return false;
        }

        function openInParent(url) {
            window.opener.location.href = url;
            window.close();
        }


        /*
         window.onbeforeunload(function () {
         return "Eğer bu pencereyi kapatırsanız resim kırpma işleminiz iptal edilecek ve yüklenen resimleriniz silinecektir!";
         });
         */

    </script>
<?php } ?>
<?php
//Display error message if there are any
if (strlen($error) > 0) {
    echo "<ul><li><strong>Error!</strong></li><li>" . $error . "</li></ul>";
}
if (strlen($large_photo_exists) > 0 && strlen($thumb_photo_exists) > 0) {
    $_SESSION["cropped"][$_SESSION["Index"]]["dir"] = $upload_dir . "/";
    $_SESSION["cropped"][$_SESSION["Index"]]["org"] = idEncode($_SESSION["sirketId"]) . "_" . $_SESSION['random_key'];
    $_SESSION["cropped"][$_SESSION["Index"]]["ext"] = $_SESSION["user_file_ext"];

    echo $large_photo_exists . "&nbsp;" . $thumb_photo_exists;
    echo "<p><a onclick='CloseMySelf(this);' result='DEL' img=\"\" href=\"?a=delete&t=" . $_SESSION['random_key'] . "&e=" . $_SESSION['user_file_ext'] . "\">Resimleri Sil</a></p>";
    echo "<p><a onclick='CloseMySelf(this);' result='OK' img=\"" . $upload_dir . "/" . idEncode($_SESSION["sirketId"]) . "_" . $_SESSION['random_key'] . "\" ext=\"" . $_SESSION['user_file_ext'] . "\" href=\"\">Resmi Kaydet</a></p>";
    echo "<a href=\"JavaScript:void(0);\" onclick=\"openInParent('islemtamam.php');\">
  click me
</a>";
//Clear the time stamp session and user file extension
    $_SESSION['random_key'] = "";
    $_SESSION['user_file_ext'] = "";
} else {
    if (strlen($large_photo_exists) > 0 || $_SESSION["user_file_ext"] != "") {
        ?>
        <h2>Create Thumbnail</h2>
        <div align="center">
            <img src="<?php echo $upload_path . $large_image_name . $_SESSION['user_file_ext']; ?>"
                 style="float: left; margin-right: 10px;" id="thumbnail" alt="Create Thumbnail"/>

            <div
                style="border:1px #e5e5e5 solid; float:left; position:relative; overflow:hidden; width:<?php echo $thumb_width; ?>px; height:<?php echo $thumb_height; ?>px;">
                <img src="<?php echo $upload_path . $large_image_name . $_SESSION['user_file_ext']; ?>"
                     style="position: relative;" alt="Thumbnail Preview"/>
            </div>
            <br style="clear:both;"/>

            <form name="thumbnail" action="" method="post">
                <input type="hidden" name="x1" value="" id="x1"/>
                <input type="hidden" name="y1" value="" id="y1"/>
                <input type="hidden" name="x2" value="" id="x2"/>
                <input type="hidden" name="y2" value="" id="y2"/>
                <input type="hidden" name="w" value="" id="w"/>
                <input type="hidden" name="h" value="" id="h"/>
                <input type="submit" name="upload_thumbnail" value="Save Thumbnail" id="save_thumb"
                       onclick="eventSil();"/>
            </form>
        </div>
        <hr/>
    <?php
    } else {
        var_dump($_REQUEST);
        var_dump($_SESSION);
    } ?>
<?php } ?>
<!-- Copyright (c) 2008 http://www.webmotionuk.com -->
</body>
</html>