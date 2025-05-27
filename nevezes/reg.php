<?php

  include(dirname(__FILE__) . "/../../common_pages/includes/constant.inc.php");
  include(dirname(__FILE__) . "/../../common_pages/includes/functions.inc.php");

  $ok = TRUE;

  $email_in_use = FALSE;
  @$keresztnev = (!empty($_POST['keresztnev'])) ? $_POST['keresztnev'] : $_GET['keresztnev'];
  @$vezeteknev = (!empty($_POST['vezeteknev'])) ? $_POST['vezeteknev'] : $_GET['vezeteknev'];
  @$email = strtolower((!empty($_POST['email'])) ? $_POST['email'] : $_GET['email']);
  @$nem = (!empty($_POST['nem'])) ? $_POST['nem'] : $_GET['nem'];
  @$szul = (!empty($_POST['szul'])) ? $_POST['szul'] : $_GET['szul'];
  @$jelszo = (!empty($_POST['jelszo'])) ? $_POST['jelszo'] : $_GET['jelszo'];
  @$jelszo2 = (!empty($_POST['jelszo2'])) ? $_POST['jelszo2'] : $_GET['jelszo2'];
  @$honnan = (!empty($_POST['honnan'])) ? $_POST['honnan'] : $_GET['honnan'];
  @$kerek = (!empty($_POST['kerek'])) ? $_POST['kerek'] : $_GET['kerek'];

  $name_error = false;
  if (trim($keresztnev ?? "") < 2 || trim($vezeteknev ?? "") < 2) {
    $name_error = true;
    $ok = false;
  }

  $address_format_error = FALSE;
  if (!preg_match('/^[0-9a-zA-Z\._-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,4}$/', $email)) {
    $address_format_error = TRUE;
    $ok = false;
  } else {
    $rows = runSelect("SELECT * FROM users WHERE email=:email", [ "email" => $email ]);
    if (count($rows)!=0) {
      $email_in_use = TRUE;
      $ok = FALSE;
    }
  }

  $szul_format_error = FALSE;
  if(!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $szul)) {
    $szul_format_error = TRUE;
    $ok = false;
  }

  $password_error = FALSE;
  if(!preg_match('/^[ -~]*$/', $jelszo) || strlen($jelszo) < 6 || $jelszo != $jelszo2) {
    $password_error = TRUE;
    $ok = false;
  }

  $nem_error = false;
  if ($nem != 1 && $nem != 2) {
    $nem_error = true;
    $ok = false;
  }

  $honnan_error = false;
  if (!array_key_exists((int)$honnan , $honnan_array)) {
    $honnan_error = true;
    $ok = false;
  }

  srand((double)microtime()*1000000);
  $confstring = "";
  for ($i=0; $i<32; $i++) {
    $j = rand(1,3);
    if ($j == 1) $k = chr(rand(48,57));
    if ($j == 2) $k = chr(rand(65,90));
    if ($j == 3) $k = chr(rand(97,122));
    $confstring .= $k;
  }

  if ($ok) {
    $data = [
      "email" => $email,
      "passwd" => password_hash($jelszo, PASSWORD_DEFAULT),
      "vezeteknev" => $vezeteknev,
      "keresztnev" => $keresztnev,
      "nem" => $nem,
      "szul" => $szul,
      "honnan" => $honnan,
      "confstring" => $confstring
    ];
    $res = runSelect("INSERT INTO users (email, passwd, vezeteknev, keresztnev, nem, szul, honnan, confstring) VALUES (:email, :passwd, :vezeteknev, :keresztnev, :nem, :szul, :honnan, :confstring)", $data);
    if ($res !== false) {
      $res = send_mail($email, "formaldavilagod.hu regisztráció", "Kedves Regisztráló!\n\nA formaldavilagod.hu weboldalon megkezdett regisztrációd megerősítéséhez kérjük, látogass el az alábbi címre:\n\nhttp://formaldavilagod.hu/" . YEAR . "/nevezes/reg_confirm.php?cs=$confstring\n\nRegisztrációdat köszönjük.\n\nÜdv,\nA Formáld a világod! verseny szervezői");
    } else {
      $ok = false;
      $server_error = true;
    }
  }

  header('Content-Type: text/html; charset=UTF-8');
?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>&lt;19 SFormáld a világod <?= YEAR ?></title>
    <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width">
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Import materialize.css-->
    <link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>
    <link type="text/css" rel="stylesheet" href="css/reg_page.css"  media="screen,projection"/>
    <script type="text/javascript" src="../js/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="../js/materialize.min.js"></script>
    <script type="text/javascript" src="js/iframeResizer.contentWindow.min.js"></script>
		<script type="text/javascript">
		<!--
			$(document).ready(function() {
				$("#mehet").click(function() {
					window.parent.forceClose = true;
					window.parent.$('#regmodal').modal('close');
				});
			});
		//-->
		</script>
	</head>
	<body class="reg-page">
		<div class="form-header">
			<img src="images/lt19.svg" />
		</div>
		<div id="n_nevezes_box">
			<div id="r_container">
				<h2 class="center piros">REGISZTRÁCIÓ</h2>
				<?php if ($name_error): ?>
				<p class="red-text"><b>A megadott név hibás!</b></p>
				<?php endif; ?>
				<?php if ($email_in_use): ?>
				<p class="red-text"><b>Ezzel az e-mail címmel már regisztráltak!</b></p>
				<?php endif; ?>
				<?php if ($address_format_error): ?>
				<p class="red-text"><b>A megadott e-mail cím érvénytelen!</b></p>
				<?php endif; ?>
				<?php if ($nem_error): ?>
				<p class="red-text"><b>Válaszd ki a nemedet!</b></p>
				<?php endif; ?>
				<?php if ($szul_format_error): ?>
				<p class="red-text"><b>Add meg az életkorodat!</b></p>
				<?php endif; ?>
				<?php if ($password_error): ?>
				<p class="red-text"><b>Érvenytelen karakterek a jelszóban, a jelszó túl rövid vagy a két jelszómező tartalma nem egyezik meg!</b></p>
				<?php endif; ?>
				<?php if ($honnan_error): ?>
				<p class="red-text"><b>válaszd ki, honnan értesültél a versenyről!</b></p>
				<?php endif; ?>
        <?php if (@$server_error): ?>
				<p class="red-text"><b>Szerverhiba!</b></p>
				<?php endif; ?>
				<?php if ($ok): ?>
				<h6 class="center">A regisztráció sikerült, hamarosan kapsz egy e-mailt, amelyben megkérünk a regisztráció megerősítésére</h6>
				<p class="center"><input id="mehet" class="btn green" value="OK" type="button"></p>
        <p>&nbsp;</p>
				<?php else: ?>
				<p class="center piros"><b>Valamilyen adatot hibásan adtál meg,<br /> kérjük, lépj vissza az előző oldalra és javítsd!</b><br /></p>
				<p class="center"><a class="btn red" href="javascript:history.back();" target="_self"><i class="fa fa-chevron-left feher"></i> JAVÍTÁS</a></p>
        <p>&nbsp;</p>
				<?php endif; ?>
			</div>
		</div>
	</body>
</html>
