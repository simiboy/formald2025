<?php

  include(dirname(__FILE__) . '/../../common_pages/includes/nocache.inc.php');
  include(dirname(__FILE__) . '/../../common_pages/includes/constant.inc.php');
  include(dirname(__FILE__) . '/../../common_pages/includes/session.inc.php');
  include(dirname(__FILE__) . '/../../common_pages/includes/ip.inc.php');
  include(dirname(__FILE__) . '/../../common_pages/includes/classes.inc.php');
  include(dirname(__FILE__) . '/../../common_pages/includes/functions.inc.php');

  //header('Content-Type: text/plain; charset=ISO-8859-2');

?>
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

  <title>Regisztráció - &lt;19 Formáld a világod! verseny</title>
  <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width">
  <!--Import Google Icon Font-->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <!--Import materialize.css-->
  <link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>
  <link type="text/css" rel="stylesheet" href="css/reg_page.css"  media="screen,projection"/>
  <script type="text/javascript" src="../js/jquery-3.7.1.min.js"></script>
</head>
<body class="reg-page" marginwidth="0" marginheight="0">
  <div class="form-header">
		<img src="images/lt19.svg" />
	</div>
  <form name="regform" action="reg.php" method="post">
		<h3 class="center">REGISZTRÁCIÓ</h3>
    <div class="reg-form">
      <div class="row">
        <div class="input-field col s12">
      		<label class="active" for="reg-teljesnev">vezetéknév</label>
      		<input id="reg-vezeteknev" name="vezeteknev" type="text" placeholder="vezetéknév">
        </div>
      </div>
      <div class="row">
        <div class="input-field col s12">
      		<label class="active" for="reg-teljesnev">keresztnév</label>
      		<input id="reg-keresztnev" name="keresztnev" type="text" placeholder="keresztnév">
        </div>
      </div>
      <div class="row">
        <div class="input-field col s12">
      		<label class="active" for="reg-email">e-mail cím</label>
      		<input id="reg-email" name="email" type="text" placeholder="e-mail cím">
        </div>
      </div>
      <div class="row">
        <div class="input-field col s12">
  		    <label class="active" for="reg-szul">születési idő</label>
  		    <input id="reg-szul" name="szul" type="text" class="datepicker" placeholder="éééé-hh-nn">
        </div>
      </div>
      <div class="row">
        <div class="input-field col s12">
  		    <label class="active" for="reg-nem">nem</label>
          <p>
            <label>
              <input class="with-gap" name="nem" type="radio" value="2"/>
              <span>Lány</span>
            </label>
            <span class="separator">|</span>
            <label>
              <input class="with-gap" name="nem" type="radio"  value="1"/>
              <span>Fiú</span>
            </label>
          </p>
        </div>
      </div>
      <div class="row">
        <div class="input-field col s12">
  		    <label class="active" for="reg-jelszo">jelszó</label>
  		    <input id="reg-jelszo" type="password" name="jelszo">
        </div>
      </div>
      <div class="row">
        <div class="input-field col s12">
  		    <label class="active" for="reg-jelszo2">jelszó még egyszer</label>
  		    <input id="reg-jelszo2" type="password" name="jelszo2">
        </div>
      </div>
      <div class="row">
        <label class="active input-field col s12">Honnan értesültél a &lt;19 versenyről?</label>
        <div class="input-field col s12">
          <select id="reg-honnan" name="honnan">
            <option value="" disabled selected>Kérjük válassz</option>
            <?php foreach ($honnan_array as $i => $value): ?>
              <option value="<?= $i ?>"><?= $value ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="row">
        <div class="input-field col s12">
          <label class="active" class="no-float" for="reg-ertesites">
            <span>A regisztráció után e-mailben értesítést küldünk a versenyhez kapcsolódó eseményekről</span>
          </label>
        </div>
      </div>
		<br>
    </div>
    <div class="alert-message hide">
      <h4 class="center red"><strong>Valamilyen adatot hibásan adtál meg,<br> kérjük javítsd!</strong><br></h4>
      <ul class="browser-default">
      <li><strong>A név első karaktere csak betű lehet, ékezet nélküli kisbetű, szám, kötőjel, pont és aláhúzás szerepelhet benne, és legalább 3 betűsnek kell lennie!</strong></li>
  		<li><strong>A megadott e-mail cím érvénytelen!</strong></li>
  		<li><strong>Az életkor érvénytelen!</strong></li>
  		<li><strong>Érvenytelen karakterek a jelszóban, a jelszó túl rövid vagy a két jelszómező tartalma nem egyezik meg!</strong></li>
      <ul>
    </ul></ul></div>
		<p class="center"><input id="mehet" class="btn green" value="OK, MEHET" type="submit"></p>
		<p class="center"><button id="vissza" class="btn blue" type="button">VISSZA</button></p>
    <p></p>
	</form>
  <script type="text/javascript" src="../js/materialize.min.js"></script>
  <script type="text/javascript" src="js/iframeResizer.contentWindow.min.js"></script>
  <script type="text/javascript">
    //console.log(document.body.scrollHeight);
    $("#vissza").click(function() {
      window.parent.forceClose = true;
      window.parent.$('#regmodal').modal('close');
    });

    var currYear = (new Date()).getFullYear();
    $(document).ready(function() {
      $('select').formSelect();

      M.FormSelect.getInstance(document.getElementById("reg-honnan")).dropdown.options.closeOnClick = false
      $("#reg-honnan").on('change', function() {
        M.FormSelect.getInstance(document.getElementById("reg-honnan")).dropdown.close()
      });


      M.updateTextFields();
      $(".datepicker").datepicker({
        //defaultDate: new Date(2011,01,02),
        // setDefaultDate: new Date(2000,01,31),
        setDefaultDate: true,
        maxDate: new Date(),
        minDate: new Date(currYear-18,00,01),
        yearRange: [currYear-18, currYear],
        format: "yyyy-mm-dd",
        firstDay: 1,
        i18n: {
          cancel: "mégsem",
          weekdaysAbbrev: ['V','H','K','SZ','CS','P','SZ'],
          months:	['Január', 'Február', 'Március', 'Április', 'Május', 'Június', 'Július', 'Augusztus', 'Szeptember', 'Október', 'November', 'December'],
          monthsShort:	['Jan', 'Feb', 'Már', 'Ápr', 'Máj', 'Jún', 'Júl', 'Aug', 'Szep', 'Okt', 'Nov', 'Dec'],
          weekdays:	['Vasárnap', 'Hétfő', 'Kedd', 'Szerda', 'Csütörtök', 'Péntek', 'Szombat'],
          weekdaysShort: ['Va', 'Hé', 'Ke', 'Szed', 'Csü', 'Pé', 'Szo']
        }
      });
    });
  </script>


<div style="clear: both; display: block;"></div></body></html>
