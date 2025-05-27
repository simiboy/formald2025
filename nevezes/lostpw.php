<?php

include(dirname(__FILE__) . '/../../common_pages/includes/nocache.inc.php');
include(dirname(__FILE__) . '/../../common_pages/includes/constant.inc.php');
include(dirname(__FILE__) . '/../../common_pages/includes/session.inc.php');
include(dirname(__FILE__) . '/../../common_pages/includes/ip.inc.php');
include(dirname(__FILE__) . '/../../common_pages/includes/classes.inc.php');
include(dirname(__FILE__) . '/../../common_pages/includes/functions.inc.php');

  if (@$_POST["email"] !== null) {
    $step = 1;  // kukildjuk az emailt, es bezarjuk az popupot
  } elseif (@$_GET["cs"] !== null) {
    $step = 2;  // kirajuk a ket jelszo mezot
  } elseif (@$_POST["cs"] !== null && @$_POST["passwd"] !== null && @$_POST["passwd2"] !== null) {
    $step = 3;  // lecsereljuk a jelszot, es kiirjuk az eredmenyt
  } else {
    $step = 0;
  }
  $harderr = false;
  $softerr = false;

?>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

  <title>Regisztráció - &lt;19 Formáld a világod! verseny</title>
  <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width">
  <!--Import Google Icon Font-->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <!--Import materialize.css-->
  <link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>
  <link type="text/css" rel="stylesheet" href="css/reg_page.css"  media="screen,projection"/>
  <script type="text/javascript" src="../js/jquery-3.7.1.min.js"></script>
  <style>
    .container {
      max-width: 800px;
    }

    form {
      padding: 30px 0;
    }
  </style>
</head>
<?php if ($step == 1): ?>
<?php
  $rows = runSelect("SELECT uid,email FROM users WHERE email=:email", [ "email" => $_POST["email"] ]);
  if ($rows && count($rows) == 1) {
    $confstring = genRandString(32);
    $res = runSelect("INSERT INTO lostpws VALUES (:confstring, :uid, :created)", [
        "confstring" => $confstring,
        "uid" => $rows[0]["uid"],
        "created" => time()
    ]);
    if ($res === false) {
      die("rendszerhiba - lekerdezes");
    }
    $res = send_mail($_POST["email"], "formaldavilagod.hu jelszócsere", "Kedves Regisztráló!\n\nA formaldavilagod.hu weboldalon kezdeményezett jelszócseréhez kérjük, látogass el az alábbi címre:\n\nhttp://formaldavilagod.hu/" . YEAR . "/nevezes/lostpw.php?cs=$confstring\n\nÜdv,\nA Formáld a világod! verseny szervezői");
    if ($res === false) {
      die("rendszerhiba - email");
    }
  }
?>
  <script type="text/javascript">
    window.parent.forceClose = true;
    window.parent.$('#lostpwmodal').modal('close');
  </script>
<?php else: // step 0,2,3 ?>
<body class="lostpw-page" marginwidth="0" marginheight="0">
  <div class="container">
    <div class="form-header">
      <img src="images/lt19.svg" />
    </div>
    <h1 class="center">ELFELEJTETT JELSZÓ</h1>
    <form name="lostpwform" action="lostpw.php" method="post">
      
      <div class="lostpw-form">
<?php if ($step == 0): ?>
        <div class="row">
          <div class="input-field col s12">
            <label class="active" for="lostpw-email">e-mail cím</label>
            <input id="lostpw-email" name="email" type="text" placeholder="e-mail cím">
          </div>
        </div>
        <div class="row">
          <div class="input-field col s12">
            <label class="active" class="no-float" for="lostpw-ertesites">
              <span>Ha a megadott e-mail címmel már regisztráltál nálunk, jelszóváltoztató e-mailt küldünk neked.</span>
            </label>
          </div>
        </div>
<?php elseif ($step == 2): ?>
<?php
  $rows = runSelect("SELECT COUNT(*) AS count FROM lostpws WHERE confstring=:confstring", [
      "confstring" => $_GET["cs"]
  ]);
  if ($rows === false) die("rendszerhiba - sikertelen lekérdezés");
  if ($rows[0]["count"] != 1):
    $harderr = true;
?>
        <div class="row">
          <div class="input-field col s12">
            <label class="active" class="no-float" for="lostpw-result">
              <span>A jelszóváltoztató link érvénytelen</span>
            </label>
          </div>
        </div>
<?php else: ?>
        <input name="cs" type="hidden" value="<?= $_GET["cs"] ?>">
        <div class="row">
          <div class="input-field col s12">
            <label class="active" for="lostpw-passwd">új jelszó</label>
            <input id="lostpw-passwd" name="passwd" type="password" placeholder="ide írd az új jelszót">
          </div>
        </div>
        <div class="row">
          <div class="input-field col s12">
            <label class="active" for="lostpw-passwd2">új jelszó még egyszer</label>
            <input id="lostpw-passwd2" name="passwd2" type="password" placeholder="írd be újra az új jelszót">
          </div>
        </div>
<?php endif; ?>
<?php elseif ($step == 3): ?>
<?php
  if (!preg_match('/^[ -~]*$/', $_POST["passwd"]) || strlen($_POST["passwd"]) < 6 || $_POST["passwd"] != $_POST["passwd2"]):
    $softerr = true;
?>
      <div class="row">
        <div class="input-field col s12">
          <label class="active" class="no-float" for="lostpw-result">
            <span>Érvenytelen karakterek a jelszóban, a jelszó túl rövid vagy a két jelszómező tartalma nem egyezik meg!</span>
          </label>
        </div>
      </div>
<?php
  else:

   runSelect("BEGIN WORK");
   $rows = runSelect("SELECT uid FROM lostpws WHERE confstring=:confstring FOR UPDATE", [
       "confstring" => $_POST["cs"]
   ]);
   if ($rows === false || count($rows) != 1) die("hiba1");
   $uid = $rows[0]["uid"];
   $rows = runSelect("UPDATE users SET passwd=:passwd WHERE uid=:uid", [
       "passwd" => password_hash($_POST["passwd"], PASSWORD_DEFAULT),
       "uid" => $uid
   ]);
   if ($rows === false) die("hiba2");
   runSelect("DELETE FROM lostpws WHERE confstring=:confstring", [
       "confstring" => $_POST["cs"]
   ]);
   runSelect("COMMIT WORK");
?>
        <div class="row">
          <div class="input-field col s12">
            <label class="active" class="no-float" for="lostpw-result">
              <span>A jelszóváltoztatás sikeresen megtörtént.</span>
            </label>
          </div>
        </div>
<?php endif; ?>
<?php endif; ?>
      <br>
      </div>
<?php if ($step >= 0 && $step < 3 && $softerr === false && $harderr === false) : ?>
      <p class="center"><input id="mehet" class="btn green" value="OK, MEHET" type="submit"></p>
<?php endif; ?>
<?php if ($step == 0 || ($step == 3 && $softerr === true)): ?>
      <p class="center"><button id="vissza" class="btn blue" type="button">VISSZA</button></p>
<?php endif; ?>
<?php if (($step == 3 && $softerr === false) || $harderr === true): ?>
      <p class="center"><button id="vissza" class="btn blue" type="button">VISSZA A BELÉPÉSHEZ</button></p>
<?php endif; ?>
      <p></p>
    </form>
  </div>
  <script type="text/javascript" src="../js/materialize.min.js"></script>
  <script type="text/javascript" src="js/iframeResizer.contentWindow.min.js"></script>
<?php if ($step == 0 || $step == 3): ?>
  <script type="text/javascript">
    //console.log(document.body.scrollHeight);
<?php if ($step == 0): ?>
      document.getElementById("lostpw-email").value = window.parent.document.getElementById("username").value
<?php endif; ?> 
    $("#vissza").click(function() {
<?php if ($step == 0): ?>
      window.parent.forceClose = true;
      window.parent.$('#lostpwmodal').modal('close');
<?php else: ?>
<?php   if ($softerr === true): ?>
      history.go(-1);
<?php   else: ?>
      document.location.href='https://formaldavilagod.hu/2024/#nevezes';
<?php   endif; ?>
<?php endif; ?>
    });

  </script>
<?php endif; ?>

<?php endif; ?>
<div style="clear: both; display: block;"></div></body></html>
