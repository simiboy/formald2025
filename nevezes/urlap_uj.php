<?php

include(dirname(__FILE__) . '/../../common_pages/includes/nocache.inc.php');
include(dirname(__FILE__) . '/../../common_pages/includes/constant.inc.php');
include(dirname(__FILE__) . '/../../common_pages/includes/session.inc.php');
include(dirname(__FILE__) . '/../../common_pages/includes/ip.inc.php');
include(dirname(__FILE__) . '/../../common_pages/includes/classes.inc.php');
include(dirname(__FILE__) . '/../../common_pages/includes/functions.inc.php');


  session_start();
	if (!$_SESSION['logged_in']) { exit; }
	$date = time();
	if ($date > DEADLINE && $_SESSION['email'] != 'acsi' && $_SESSION['email'] != 'marci' && $user_ip != "59879b89" && $user_ip != "5665e2cd" && $user_ip != "b03f0f9b") { echo $user_ip; exit; }
	$user = getUserByEmail($_SESSION['email']);
	if ($user == FALSE) { exit; }
	$showNevezo = TRUE;

	$nevezo_db = getNevezoByUserId($user->uid);
	if ($nevezo_db != FALSE) {

		if ($nevezo_db->closed != null) $showNevezo = FALSE;
		$nevezo = new NevezoForm($nevezo_db->lak_orszag, $nevezo_db->lak_irsz, $nevezo_db->lak_varos, $nevezo_db->lak_utca, $nevezo_db->lak_szam, $nevezo_db->tel, $nevezo_db->isk_nev, $nevezo_db->isk_orszag, $nevezo_db->isk_irsz, $nevezo_db->isk_varos, $nevezo_db->isk_utca, $nevezo_db->isk_szam, $nevezo_db->polo);
	} else {
		$nevezo = new NevezoForm("", "", "", "", "", "", "", "", "", "", "", "", "");
	}

	if (isset($_GET["id"]) && is_numeric($_GET["id"]) && $nevezo_db != FALSE) {
		$id = $_GET["id"];
		$nevezes_db = getNevezesById($id);
		if ($nevezes_db == FALSE) { exit; }
		if ($nevezo_db->nevezo_id != $nevezes_db->nevezo_id) { exit; }

		$nevezes = new NevezesForm($nevezes_db->nevezes_id, $nevezes_db->cim, $nevezes_db->keszites_eve, $nevezes_db->csapat, $nevezes_db->tipus, $nevezes_db->egyebtipus, $nevezes_db->tartalom, $nevezes_db->keszites, $nevezes_db->futtatas, $nevezes_db->adathordozo, $nevezes_db->url, $nevezes_db->egyebmedium);
	} else {
		$nevezes = new NevezesForm("", "", "", "", "", "", "", "", "", "", "", "");
		$nevezes_db = FALSE;
		$id = 0;
	}

	// header('Content-Type: text/plain; charset=ISO-8859-2');
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>&lt;19 Formáld a világod! verseny</title>
		<meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width">
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Import materialize.css-->
    <link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>
    <link type="text/css" rel="stylesheet" href="css/reg_page.css"  media="screen,projection"/>
		<style>
			body, html {
				height: auto;
			}
		</style>
    <script type="text/javascript" src="../js/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="../js/materialize.min.js"></script>
    <script type="text/javascript" src="js/iframeResizer.contentWindow.min.js"></script>
		<script type="text/javascript">

			var nevezes = <?= ($nevezes_db !== FALSE) ? $nevezes_db->toJson() : "{\"csapat\": { \"tagok\":[] } }" ?>;
      if (nevezes.csapat == null) nevezes.csapat = { "tagok": [] };
			var csapattagok_array = nevezes.csapat.tagok;

		</script>

		<script type="text/javascript" src="urlap_uj.js"></script>
	</head>
	<body class="app-page">
		<div class="content">
	    <div class="form-header">
	  		<img src="images/lt19.svg">
	  	</div>
	    <h3 class="center">NEVEZÉS</h3>
			<h5 class="alert-message white-text red center" id="hiba">Az űrlapon vannak még HIBÁK, így nem zárható le, kérjük javítsd!</h5>
				<form name="urlap" action="urlap_uj.php" method="POST">
					<input type="hidden" name="id" value="<?= $id ?>" />
					<?php if ($showNevezo): ?>
					<fieldset id="app-lakcim">
						<legend>Lakcím:</legend>
						<div class="form-float">
							<div class="input-field">
								<label for="app-lak_orszag">Ország</label><input id="app-lak_orszag" type="text" name="lak_orszag" value="<?= htmlspecialchars($nevezo->lak_orszag ?? "") ?>"/>
							</div>
							<div class="input-field">
								<label for="app-lak_irsz">Irányítószám</label><input id="app-lak_irsz" type="text" name="lak_irsz" value="<?= htmlspecialchars($nevezo->lak_irsz ?? "") ?>" />
							</div>
							<div class="input-field">
								<label for="app-lak_varos">Város</label><input id="app-lak_varos" type="text" name="lak_varos" value="<?= htmlspecialchars($nevezo->lak_varos ?? "") ?>" />
							</div>
							<div class="input-field">
								<label for="app-lak_utca">Utca</label><input id="app-lak_utca" type="text" name="lak_utca" value="<?= htmlspecialchars($nevezo->lak_utca ?? "") ?>" />
							</div>
							<div class="input-field">
								<label for="app-lak_szam">Házszám</label><input id="app-lak_szam" type="text" name="lak_szam" value="<?= htmlspecialchars($nevezo->lak_szam ?? "") ?>" />
							</div>
							<div class="input-field">
								<label for="app-tel">Telefon/mobil</label><input id="app-tel" type="text" name="tel" value="<?= htmlspecialchars($nevezo->tel ?? "") ?>" />
							</div>
					</fieldset>
					<fieldset id="app-iskolacim">
						<legend>Iskola adatai:</legend>
						<div class="form-float">
							<div class="input-field">
								<label for="app-isk_nev">Iskola neve:</label><input id="app-isk_nev" type="text" name="isk_nev" value="<?= htmlspecialchars($nevezo->isk_nev ?? "") ?>" />
							</div>
							<div class="input-field">
								<label for="app-isk_orszag">Ország</label><input id="app-isk_orszag" type="text" name="isk_orszag" value="<?= htmlspecialchars($nevezo->isk_orszag ?? "") ?>" />
							</div>
							<div class="input-field">
								<label for="app-isk_irsz">Irányítószám</label><input id="app-isk_irsz" type="text" name="isk_irsz" value="<?= htmlspecialchars($nevezo->isk_irsz ?? "") ?>" />
							</div>
							<div class="input-field">
								<label for="app-isk_varos">Város</label><input id="app-isk_varos" type="text" name="isk_varos" value="<?= htmlspecialchars($nevezo->isk_varos ?? "") ?>" />
							</div>
							<div class="input-field">
								<label for="app-isk_utca">Utca</label><input id="app-isk_utca" type="text" name="isk_utca" value="<?= htmlspecialchars($nevezo->isk_utca ?? "") ?>" />
							</div>
							<div class="input-field">
								<label for="app-isk_szam">Házszám</label><input id="app-isk_szam" type="text" name="isk_szam" value="<?= htmlspecialchars($nevezo->isk_szam ?? "") ?>" />
							</div>
						</div>
					</fieldset>
					<fieldset>
						<legend>Milyen méretű pólót szeretnél?</legend>
						<span id="radiobox_polo">
							<?php for ($i=1; $i<=4; $i++): ?>
								<label for="poloradio<?= $i ?>">
									<input id="poloradio<?= $i ?>"  type="radio" name="polo" value="<?= $i ?>" <?= ($nevezo->polo == $i) ? "checked=\"checked\"" : "" ?> />

									<span><?= $polo_array[$i] ?></span>
								</label>
							<?php endfor; ?>
						</span>
					</fieldset>
					<br />
					<?php endif; ?>
					<h5 class="center">PÁLYAMŰ ADATAI</h5>
					<div class="form-float">
						<div class="input-field">
					  	<label for="app-cim" class="active">A pályamű címe</label>
					  	<input id="app-cim" type="text" name="cim" maxlength="100" data-length="100" value="<?= htmlspecialchars($nevezes->cim ?? "") ?>">
						</div>
						<div class="input-field">
					  	<label for="app-keszites-eve" class="active">Készítés éve</label>
					  	<input id="app-keszites-eve" type="number" name="keszites_eve" maxlength="4" min="<?= (YEAR - 8) ?>" max="<?= YEAR ?>"  value="<?= $nevezes->keszites_eve ?>">
						</div>
					</div>
					<fieldset id="radiobox_csapat">
						<legend>A pályázatot</legend>
						<label for="egyedul">
							<input id="egyedul"  type="radio" name="csapat" value="1" <?= ($nevezes->csapat === "") ? "checked=\"checked\"" : "" ?> onchange="csapatletszam()" />
							<span>egyedül készítettem</span>
						</label>
						<br>
						<label for="csapatban">
							<input id="csapatban"  type="radio" name="csapat" value="2" <?= ($nevezes->csapat !== "") ? "checked=\"checked\"" : "" ?> onchange="csapatletszam()" />
						  <span>barátaimmal együtt készítettük,<br>a csapat velem együtt
								<select class="browser-default letszam" name="letszam" onchange="csapatletszam()">
									<option value="1">-</option>
									<?php for ($i=2; $i<21; $i++): ?>
									<option value="<?= $i ?>" <?= $nevezes->csapat !== "" && $nevezes->csapat->tagok !== null && (count($nevezes->csapat->tagok)+1 == $i) ? "selected=\"selected\"" : "" ?>><?= $i ?></option>
									<?php endfor; ?>
								</select>
							 főből áll.</span>
					 </label>
					</fieldset>
					<div id="csapatdiv">
						<h5 class="center">Csapat</h5>
						<div class="form-float">
							<div class="input-field">
								<label>A csapat neve</label>
								<input  type="text" name="csapatnev" value="<?= $nevezes->csapat != "" ? htmlspecialchars($nevezes->csapat->nev ?? "") : "" ?>" />
							</div>
						</div>
						<fieldset id="csapattagok">
							<legend>A további csapattagok névsora:</legend>
							<div class="csapattag hide" id="csapattag_template" >
								<div class="input-field">
									<label class="csapattag_sorszam"><span class="label-text"></span></label>
									<input class="csapattag_nev" type="text" name="tagnev[]" value="" />
								</div>
								<div class="input-field">
									<label class="active">Nem</label>
	  							<select class="csapattagok csapattag_nem" name="tagnem[]">
	  								<option value="0">-- válassz --</option>
	  								<option value="1">fiú</option>
	  								<option value="2">lány</option>
	  							</select>
								</div>

								<div class="form-float">
									<label>Születési idő:</label>
										<input id="reg-szul" name="tagszul[]" type="text" class="datepicker csapattag_szul" placeholder="éééé-hh-nn">
									</label>
								</div>
								<label>
									<span class="label-text">pólóméret:</span>
									<select class="csapattagok csapattag_polo" name="tagpolo[]">
										<?php for ($j=1; $j<=4; $j++): ?>
										<option value="<?= $j ?>"><?= $polo_array[$j] ?></option>
										<?php endfor; ?>
									</select>
								</label>
								<br>
							</div>
						</fieldset>
					</div>
					<fieldset>
						<legend>Leginkább melyik típusba sorolnád be a munkádat?</legend>
						<select name="tipus" id="tipus">
							<?php for ($i=1; $i<=9; $i++): ?>
							<option value="<?= $i ?>" <?= ($nevezes->tipus == $i) ? "selected=\"selected\"" : "" ?>><?= $tipus_array[$i] ?></option>
							<?php endfor; ?>
						</select>
					</fieldset>
					<fieldset>
						<legend>Röviden, címszavakban mutasd be a pályaművedet!</legend>
						<input id="app-tartalom"  type="text" name="tartalom" value="<?= htmlspecialchars($nevezes->tartalom ?? "") ?>" /><br>
					</fieldset>
					<fieldset>
						<legend>Írd le röviden, hogyan készítetted el a pályaművedet!</legend>
						<input id="app-keszites"  type="text" name="keszites" value="<?= htmlspecialchars($nevezes->keszites ?? "") ?>" /><br>
					</fieldset>
					<h5 class="center">TECHNIKAI KÖVETELMÉNYEK</h5>
					<fieldset>
						<legend>Milyen operációs rendszeren vagy speciális hardver/szoftver környezetben kell futtatni a pályaművedet?</legend>
						<input id="app-futtatas"  type="text" name="futtatas" value="<?= htmlspecialchars($nevezes->futtatas ?? "") ?>" /><br>
					</fieldset>
					<fieldset class="form-adat" id="radiobox_adathordozo">
					  <legend>Milyen módon adod be a munkádat?</legend>
						<p>
							<label for="adat_url" >
              	<input id="adat_url" type="radio" name="adathordozo" value="4" <?= ($nevezes->adathordozo == 4) ? "checked=\"checked\"" : "" ?> />
								<span class="url_span">online (URL):<input type="text" name="url" class="url_input" value="<?= htmlspecialchars($nevezes->url ?? "") ?>"></span>
							</label>
            </p>
						<p>
							<label for="adat_fizikai">
						  	<input id="adat_fizikai" type="radio" name="adathordozo" value="1" <?= ($nevezes->adathordozo == 1) ? "checked=\"checked\"" : "" ?> />
								<span>CD-ROM/DVD/Pendrive</span>
							</label>
						</p>
						<p>
              <label for="adat_hrdwr">
								<input id="adat_hrdwr" type="radio" name="adathordozo" value="2"  <?= ($nevezes->adathordozo == 2) ? "checked=\"checked\"" : "" ?> />
								<span>hardver (postai úton)</span>
							</label>
						</p>
						<p>
							<label for="adat_feltoltes">
              	<input id="adat_feltoltes" type="radio" name="adathordozo" value="3" <?= ($nevezes->adathordozo == 3) ? "checked=\"checked\"" : "" ?> />
								<span>verseny.c3.hu feltöltés</span>
							</label>
						</p>
					</fieldset>
				</form>
				<div id="upload">
					<h5 class="center">FELTÖLTÖTT FÁJLOK</h5>
					<table class="table" id="fajl_tabla">
						<tbody>
						<tr class="row center">
  							<td style="width:55%"><strong>Fájlnév</strong></td>
  							<td style="width:15%"><strong>Méret</strong></td>
  							<td style="width:20%"><strong>Feltöltés dátuma</strong></td>
  							<td style="width:10%"><strong>Törlés</strong></td>
  						</tr>
						</tbody>
					</table>
					<h6 class="alert-message center white-text red" id="feltoltes">A "formaldavilagod.hu feltöltés" opciót választottad, de nem töltöttél még fel fájlt.</h6>
					<div class="progress" style="display:none;" id="progressbar_div">
						<div class="determinate progress-bar" style="width: 0%"></div>
					</div>
					<p class="center" >
						<a id="fileselect" class="btn blue" href="javascript:;" onclick="selectFile();"><i class="fa fa-upload"></i> FELTÖLTÉS</a>
					</p>
					<input type="file" id="upFile" name="file" style="width:0px;height:0px;visibility:hidden;">
				</div>
				<br><br>
				<h5 class="alert-message white-text red center" id="hiba_bottom">Az űrlapon vannak még HIBÁK, így nem zárható le, kérjük, javítsd!</h5>
				<p class="center" id="button_div">
					<input id="bezar" class="btn red float-left" value="BEZÁR (MENTÉS NÉLKÜL)" type="button" />
					<input id="eltesz" class="btn green" value="ELTESZEM KÉSŐBBRE" type="button" />
					<input id="lezar" class="btn blue float-right" value="LEZÁROM" type="button" />
				</p>
	</div>
	<script type="text/javascript">
/*
	$("#app-hiba").click(function() {
		$("#app-cim, #app-ev, #app-os, #app-csapatnev, textarea, fieldset, #app-szemelyes, #radiobox_polo").toggleClass("input-alert");
		$(".alert-message").toggle();
	});

    $("#bezar").click(function() {
      //window.parent.forceClose = true;
      //window.parent.$('#application').modal('hide');
			window.parent.hideApplicationFormModal();
    });
		$("#eltesz").click(function() {
			window.parent.hideApplicationFormModal();
			ajaxSaveData("eltesz");
		});
		$("#lezar").click(function() {
			window.parent.hideApplicationFormModal();
			ajaxCloseForm();
		});
		$(document).ready(function() {
			var selected = $("input[type='radio'][name='adathordozo']:checked");
			if (selected.length == 1 && selected.val() == 3) selected.click();
			csapatletszam();
		});

    $("[name=csapat]").click(function(){
      if ($(this).val() == 2) {
        $("#csapatdiv").show('slow');
      } else {
        $("#csapatdiv").hide();
      }
    });
    $("[name=adathordozo]").click(function(){
      if ($(this).val() == 3) {
        $("#upload").show('slow');
				ajaxGetFileList();
      } else {
        $("#upload").hide();
      }
    });
*/
  </script>
	</body>
</html>
