<?php

include(dirname(__FILE__) . "/../../common_pages/includes/constant.inc.php");
include(dirname(__FILE__) . "/../../common_pages/includes/functions.inc.php");

  header('Content-Type: text/html; charset=UTF-8');

  @$nev = (!empty($_POST['nev'])) ? $_POST['nev'] : $_GET['nev'];
  @$cs = (!empty($_POST['cs'])) ? $_POST['cs'] : $_GET['cs'];

  runSelect("BEGIN");
  $rows = runSelect("SELECT email,activated FROM users WHERE confstring=:confstring FOR UPDATE", [ "confstring" => $cs ]);
  $err = 0;
  if ($rows === false) {
    $err = 1;
  } else if (count($rows) != 1) {
    $err = 2;
  } else if ($rows[0]["activated"] != null) {
    $err = 3;
  } else {
    $rows = runSelect("UPDATE users SET activated=:activated WHERE confstring=:confstring", [ "activated" => time(), "confstring" => $cs ]);
    srand((double)microtime()*1000000);
    $mailconfstring = "";
    for ($i=0; $i<32; $i++) {
      $j = rand(1,3);
      if ($j == 1) $k = chr(rand(48,57));
      if ($j == 2) $k = chr(rand(65,90));
      if ($j == 3) $k = chr(rand(97,122));
      $mailconfstring .= $k;
    }

    $rows = runSelect("INSERT INTO subscribed SELECT NULL,uid,email,CONCAT(vezeteknev,' ',keresztnev),:mailconfstring,:year FROM users WHERE confstring=:userconfstring", [
        "userconfstring" => $cs,
        "mailconfstring" => $mailconfstring,
        "year" => YEAR
    ]);
    // if ($_GET["k"] == 1) {
    //   runSelect("INSERT INTO subscribed VALUES (".$uid.", '".$email."', '".$fname."', '".$confstring."', " . YEAR . ")")..
    // }
  }
  runSelect("COMMIT");

?>
<!doctype html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>&lt;19 Formáld a világod <?= YEAR ?></title>
    <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width">
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Import materialize.css-->
    <link type="text/css" rel="stylesheet" href="../css/materialize.min.css"  media="screen,projection"/>
    <link type="text/css" rel="stylesheet" href="css/reg_page.css"  media="screen,projection"/>
    <script type="text/javascript" src="../js/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="../js/materialize.min.js"></script>
    <style media="screen">
      .btn, .btn:hover {
        background-color: #1b61ad;
      }
    </style>
	</head>
	<body reg-page>
    <div class="container">
  		<div class="form-header">
  			<img src="images/lt19.svg" />
  		</div>
  		<h1 class="center tb_marginclear">REGISZTRÁCIÓ</h1>
  		<div id="row">
  			<div id="col s8 offset-s2">
  				<h4 class="center-align">
  					<?php if ($err == 0): ?>
  					A regisztráció sikerrel lezárult. Köszönjük.
  					<?php elseif ($err == 1): ?>
  					A megerősítés nem járt sikerrel.<br/>Rendszerhiba.
  					<?php elseif ($err == 2): ?>
  					A megerősítés nem járt sikerrel.<br/>A megadott hitelesítő kód nem található.
            <?php elseif ($err == 3): ?>
  					A regisztráció megerősítése már korábban megtörtént.
  					<?php endif; ?>
  				</h4>
          <div id="row">
            <div id="col s8 offset-s2">
              <h4 class="center-align">
                <a class="btn" href="https://formaldavilagod.hu/">Vissza a formaldavilagod.hu oldalra</a>
              </h4>
            </div>
  			</div>
  		</div>
    </div>
	</body>
</html>
