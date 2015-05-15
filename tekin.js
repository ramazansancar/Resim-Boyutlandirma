/**
 * Created by wissen on 15.5.2015.
 */

function target_popup(form) {
    window.open('', 'formpopup', 'width=950,height=900,resizeable,scrollbars');
    form.target = 'formpopup';

    /*****
     * popUp açıldığında yeni resim ekleyeceği ve popUp taki kodların eski resimleri sileceği için bu değerleri boşaltıyoruz
     * @type {string}
     */

    document.getElementById("croppedImage").value = "";
    document.getElementById("info").innerHTML = '';
}

function HandlePopupResult(result) {
    if (result != null) {
        if (result[0] == "OK" && result[1] != "" && result[2] != "") {
            alert("başarılı");
            document.getElementById("croppedImage").value = result[1] + result[2];
            document.getElementById("info").innerHTML = '<img src="' + result[1] + '_200' + result[2] + '">';
            //document.getElementById('imageCrop').action = "islemtamam.php";
            //document.getElementById("imageCrop").style.visibility = "hidden";
        } else {
            document.getElementById("croppedImage").value = "";
            document.getElementById("info").innerHTML = '';
        }
    }
}