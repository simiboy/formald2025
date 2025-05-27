<?php

include(dirname(__FILE__) . '/../../common_pages/includes/nocache.inc.php');
include(dirname(__FILE__) . '/../../common_pages/includes/constant.inc.php');
include(dirname(__FILE__) . '/../../common_pages/includes/session.inc.php');
include(dirname(__FILE__) . '/../../common_pages/includes/ip.inc.php');
include(dirname(__FILE__) . '/../../common_pages/includes/classes.inc.php');
include(dirname(__FILE__) . '/../../common_pages/includes/functions.inc.php');

  function cmd_login() {
    global $_POST;
    global $_GET;

    $username = isset($_POST['username']) ? $_POST['username'] : $_GET['username'];
    $password = isset($_POST['password']) ? $_POST['password'] : $_GET['password'];
    $now = time();
    $error_msg = $username;

    $options = [
      \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
      \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
      \PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new \PDO("mysql:host=db;dbname=verseny;charset=utf8mb4", "verseny", "bFVLEy3eKq", $options);

    $sql = "SELECT passwd,uid,activated FROM users WHERE email=:email";
    $sth = $pdo->prepare($sql);
    $sth->execute(["email" => $username]);
    $res = $sth->fetchAll();
    /*
    if (!$res) {
      $error = 6;
      $error_msg = "mysql error: " . implode(", ", $pdo->errorInfo());
    } else */ if (count($res) > 1) {
      $error = 5;
      $error_msg = "result row count error";
    } else if (count($res) < 1) {
      $error = 3;
      $error_msg = "bad password";
    } else if (!$res[0]["activated"]) {
      $error = 2;
      $error_msg = "user inactive";
    } else if (!password_verify($password, $res[0]["passwd"])) {
      $error = 3;
      $error_msg = "bad password";
    } else if (!isset($_SESSION)) {
      $error = 1;
      $error_msg = "session error";
    } else {
      $error = 0;
      $error_msg = $username;
      $_SESSION = array( "email" => $username, "uid" => $res[0]["uid"], "logged_in" => true );
      runSelect("UPDATE users SET lastlogin=:lastlogin WHERE uid=:uid", [
          "lastlogin" => $now,
          "uid" => $res[0]["uid"]
      ]);
/*
      if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
	  	}
*/
    }
    if ($error != 0) {
      syslog(LOG_LOCAL4 | LOG_INFO,"verseny.c3.hu login attempt [" . $username . "]/[" . $password . "]: failed [".$error_msg."]");
    } else {
      syslog(LOG_LOCAL4|LOG_INFO,"verseny.c3.hu login attempt [" . $username . "]: success");
    }
    return "{\"response\":{\"code\":" . $error . ", \"message\": \"" . $error_msg . "\"}}";
  }

  function cmd_logout() {
    $_SESSION["logged_in"] = false;
    return "{\"response\":{\"code\":0}}";
  }

  function cmd_getformdata() {
    $resp = "{\"response\":{\"code\":1}}";


    if (@$_SESSION["logged_in"]) {
      $user = getUserByEmail($_SESSION['email']);
      if ($user == FALSE) return $resp;
      $nevezo_db = getNevezoByUserId($user->uid);
      $allclosedforms= Array();
      if ($nevezo_db == FALSE) {
        $openforms = Array();
        $closedforms = Array();
        $receivedforms = Array();
      } else {
        $openforms = getNyitottNevezesekByNevezoId($nevezo_db->nevezo_id);
        $allclosedforms = getLezartNevezesekByNevezoId($nevezo_db->nevezo_id);
        $closedforms = Array();
        $receivedforms = Array();
      }
      for ($i=0; $i<count($openforms); $i++) $openforms[$i]->nevezo = $nevezo_db;
      for ($i=0; $i<count($allclosedforms); $i++) {
        $allclosedforms[$i]->nevezo = $nevezo_db;
        if ($allclosedforms[$i]->form_status == 1) array_push($receivedforms, $allclosedforms[$i]);
        else array_push($closedforms, $allclosedforms[$i]);
      }
      $resp = "{\"response\":{\"code\":0, \"openforms\":[";
      for ($i=0; $i<count($openforms); $i++) {
        if ($i>0) $resp .= ",";
        $resp .= $openforms[$i]->toJson();
      }
      $resp .= "], \"closedforms\": [";
      for ($i=0; $i<count($closedforms); $i++) {
        if ($i>0) $resp .= ",";
        $resp .= $closedforms[$i]->toJson();
      }
      $resp .= "], \"receivedforms\": [";
      for ($i=0; $i<count($receivedforms); $i++) {
        if ($i>0) $resp .= ",";
        $resp .= $receivedforms[$i]->toJson();
      }
      $resp .= "]}}";
    }
    return $resp;
  }

  function cmd_deleteform() {
    $resp = "{\"response\":{\"code\":1}}";

    if (!isset($_POST["id"]) || !is_numeric($_POST["id"])) return $resp;
    if (!$_SESSION['logged_in']) return $resp;
    $now = time();
    if ($now > DEADLINE && $_SESSION['email'] != 'acsi@c3.hu' && $_SESSION['email'] != 'marci@c3.hu') return $resp;
    $user = getUserByEmail($_SESSION['email']);
    if ($user == FALSE) return $resp;
    $nevezo_db = getNevezoByUserId($user->uid);
    if ($nevezo_db == FALSE) return $resp;
    $nevezes_db = getNevezesById($_POST["id"]);
    if ($nevezes_db == FALSE) return $resp;
    if ($nevezo_db->nevezo_id != $nevezes_db->nevezo_id) return $resp;
    $files = getFiles($nevezes_db->nevezes_id);
    for ($i=0; $i<count($files); $i++) {
      unlink($files[$i]->path);
    }
    delNevezes($nevezes_db->nevezes_id);
    return cmd_getformdata();
  }

  function cmd_getfilelist() {
    $resp = "{\"response\":{\"code\":1}}";

    if (!isset($_POST["id"]) || !is_numeric($_POST["id"])) return $resp;
    if (!$_SESSION['logged_in']) return $resp;
    $now = time();
    if ($now > DEADLINE && $_SESSION['email'] != 'acsi@c3.hu' && $_SESSION['email'] != 'marci@c3.hu') return $resp;
    $user = getUserByEmail($_SESSION['email']);
    if ($user == FALSE) return $resp;
    $nevezo_db = getNevezoByUserId($user->uid);
    if ($nevezo_db == FALSE) return $resp;
    $nevezes_db = getNevezesById($_POST["id"]);
    if ($nevezes_db == FALSE) return $resp;
    if ($nevezo_db->nevezo_id != $nevezes_db->nevezo_id) return $resp;
    $files = getFiles($nevezes_db->nevezes_id);
    $resp = "{\"response\":{\"code\":0, \"files\":[";
    for ($i=0; $i<count($files); $i++) {
      if ($i>0) $resp .= ",";
      $resp .= $files[$i]->toJson();
    }
    $resp .= "]}}";
    return $resp;
  }

  function cmd_deletefile() {
    $resp = "{\"response\":{\"code\":1}}";

    if (!isset($_POST["fid"]) || !is_numeric($_POST["fid"])) return $resp;
    if (!$_SESSION['logged_in']) return $resp;
    $now = time();
    if ($now > DEADLINE && $_SESSION['email'] != 'acsi@c3.hu' && $_SESSION['email'] != 'marci@c3.hu') return $resp;
    $user = getUserByEmail($_SESSION['email']);
    if ($user == FALSE) return $resp;
    $nevezo_db = getNevezoByUserId($user->uid);
    if ($nevezo_db == FALSE) return $resp;
    $file = getFileById($_POST["fid"]);
    if ($file == FALSE) return $resp;
    $nevezes_db = getNevezesById($file->nevezes_id);
    if ($nevezes_db == FALSE) return $resp;
    if ($nevezo_db->nevezo_id != $nevezes_db->nevezo_id) return $resp;
    delFile($file->fid);
    unlink($file->path);
    return cmd_getfilelist();
  }

  function cmd_uploadfile($is_form) {
    $resp = "{\"response\":{\"code\":1}}";

    syslog(LOG_LOCAL4|LOG_INFO,"verseny.c3.hu " . YEAR . " file upload");

    if (!isset($_POST["id"]) || !is_numeric($_POST["id"])) return $resp;
    if (!$_SESSION['logged_in']) return $resp;
    $now = time();
    if ($now > DEADLINE && $_SESSION['email'] != 'acsi@c3.hu' && $_SESSION['email'] != 'marci@c3.hu') return $resp;
    $user = getUserByEmail($_SESSION['email']);
    if ($user == FALSE) return $resp;
    $nevezo_db = getNevezoByUserId($user->uid);
    if ($nevezo_db == FALSE) return $resp;
    $nevezes_db = getNevezesById($_POST["id"]);
    if ($nevezes_db == FALSE) return $resp;
    if ($nevezo_db->nevezo_id != $nevezes_db->nevezo_id) return $resp;
    if (!$is_form && $nevezes_db->closed != null) return $resp;
    if ($is_form && ($nevezes_db->closed == null || $nevezes_db->form_status == 1)) return $resp;

    $upload_dir = UPLOAD_HOME . "/" . $nevezes_db->nevezes_id;
    //$upload_dir = __DIR__ . "/upload/" . $nevezes_db->nevezes_id;

    $upload = $_FILES['uploadfile'];
    if ($_FILES['uploadfile']['error'] != UPLOAD_ERR_OK) {
      $upload_errors = array("UPLOAD_ERR_OK", "UPLOAD_ERR_INI_SIZE", "UPLOAD_ERR_FORM_SIZE", "UPLOAD_ERR_PARTIAL", "UPLOAD_ERR_NO_FILE", "ERROR_5", "UPLOAD_ERR_NO_TMP_DIR", "UPLOAD_ERR_CANT_WRITE", "UPLOAD_ERR_EXTENSION");
      $df_src = "-";
      if (is_file($upload['tmp_name'])) {
        $dirname_src = dirname($upload['tmp_name']);
        $df_src = disk_free_space($dirname_src);
      }
      syslog(LOG_LOCAL4|LOG_INFO,"verseny.c3.hu " . YEAR . " file upload (" . $nevezes_db->nevezes_id . ") error " . $upload_errors[$upload['error']] . ": " . urldecode(http_build_query($upload)));
      return $resp;
    }
    if ($upload['name'] == "") return $resp;
    $realname = $upload_dir . "/_" . posix_getpid() . "_" . time();
    if ($is_form) $realname = $realname . "_nevezesi_lap";
    if (!is_dir($upload_dir)) {
      mkdir ($upload_dir, 0700);
    }
    $copy = copy($upload['tmp_name'], $realname);
    if ($copy) {
      syslog(LOG_LOCAL4|LOG_INFO,"verseny.c3.hu " . YEAR . " file copy [" . $upload['name'] . "] from [" . $upload['tmp_name'] . "] to [" . $realname . "]: success");
      $filesize = filesize($realname);
      $file = new DbFile(-1, $nevezes_db->nevezes_id, $upload['name'], $realname, time(), 1, $filesize, ($is_form ? 1 : 0));
      $db_file = FALSE;
      //if ($is_form) $db_file = getFormForNevezes($nevezes_db->nevezes_id);
      if ($db_file == FALSE) {
        $fid = newFile($file);
      } else {
        $db_file->fname = $file->fname;
        $db_file->uploaded = $file->uploaded;
        $db_file->filesize = $file->filesize;
        updateFormUpload($db_file);
      }
      return cmd_getfilelist();
    } else {
      $dirname_src = dirname($upload['tmp_name']);
      $dirname_dst = dirname($realname);
      $df_src = disk_free_space($dirname_src);
      $df_src = disk_free_space("/var/tmp");
      $df_dst = disk_free_space($dirname_dst);
      syslog(LOG_LOCAL4|LOG_INFO,"verseny.c3.hu " . YEAR . " file copy [" . $upload['name'] . "] from [" . $upload['tmp_name'] . "] to [" . $realname . "]: failed [" . $dirname_src . " free space: " . $df_src . ", " . $dirname_dst . " free space: " . $df_dst . "]");
      return $resp;
    }
  }

  function cmd_savedata() {
    global $user_ip;
    $resp = "{\"response\":{\"code\":1}}";

    if (!isset($_POST["id"]) || !is_numeric($_POST["id"])) return $resp;
    if ($_SESSION["logged_in"] != true) return $resp;
    $now = time();
    if ($now > DEADLINE && $user_ip != "59879b89") return $resp;
    $user = getUserByEmail($_SESSION['email']);
    if ($user == FALSE) return $resp;
    $nevezo_db = getNevezoByUserId($user->uid);
    if ($nevezo_db == FALSE && $_POST["id"] != 0) return $resp;
    if ($_POST["id"] != 0 && $nevezo_db != FALSE) {
      $nevezes_db = getNevezesById($_POST["id"]);
      if ($nevezes_db == FALSE) return $resp;
      if ($nevezo_db->nevezo_id != $nevezes_db->nevezo_id) return $resp;
    }

    $csapattagok = "";
    if (is_numeric(@$_POST["letszam"])) {
      $csapat = new stdClass();
      $csapat->nev = $_POST["csapatnev"];
      $csapat->tagok = array();
      for ($i=1; $i<($_POST["letszam"]); $i++) {
        $tag = new stdClass();
        $tag->nev = trim($_POST["tagnev"][$i] ?? "");
        $tag->nem = (int)$_POST["tagnem"][$i];
        $tag->szul = $_POST["tagszul"][$i];
        $tag->polo = (int)$_POST["tagpolo"][$i];
        $csapat->tagok[] = $tag;
        //$csapattagok .= htmlspecialchars(trim($_POST["tagnev"][$i])) . ">" . (int)$_POST["tagnem"][$i] . ">" . (int)$_POST["tagszulev"][$i] . ">" . (int)$_POST["tagszulho"][$i] . ">" . (int)$_POST["tagszulnap"][$i] . ">" . (int)$_POST["tagpolo"][$i] . ">";
      }
    }
    @$nevezo = new NevezoForm($_POST["lak_orszag"], $_POST["lak_irsz"], $_POST["lak_varos"], $_POST["lak_utca"], $_POST["lak_szam"], $_POST["tel"], $_POST["isk_nev"], $_POST["isk_orszag"], $_POST["isk_irsz"], $_POST["isk_varos"], $_POST["isk_utca"], $_POST["isk_szam"], $_POST["polo"]);
    @$nevezes = new NevezesForm($_POST["id"], $_POST["cim"], $_POST["keszites_eve"], json_encode($csapat), $_POST["tipus"], $_POST["egyebtipus"], $_POST["tartalom"], $_POST["keszites"], $_POST["futtatas"], $_POST["adathordozo"], $_POST["url"], $_POST["egyebmedium"]);
    if ($nevezo_db != FALSE) {
      modifyNevezoById($nevezo, $nevezo_db->nevezo_id);
      $nevezo_id = $nevezo_db->nevezo_id;
    } else {
      $nevezo_id = newNevezo($nevezo, $user->uid);
    }
    if ($_POST["id"] != 0) {
      updateNevezes($nevezes);
      $id = $_POST["id"];
    } else {
      $id = newNevezes($nevezes, $nevezo_id);
    }
    $resp = "{\"response\":{\"code\":0, \"nevezes_id\":" . $id . "}}";
    return $resp;
  }

  function cmd_closeform() {
    global $user_ip;

    $resp = "{\"response\":{\"code\":1}}";
    if (!isset($_POST["id"]) || !is_numeric($_POST["id"])) return $resp;
    if (!$_SESSION['logged_in']) return $resp;
    $now = time();
    if ($now > DEADLINE && $user_ip != "59879b89") return $resp;
    $user = getUserByEmail($_SESSION['email']);
    if ($user == FALSE) return $resp;
    $nevezo_db = getNevezoByUserId($user->uid);
    if ($nevezo_db == FALSE) return $resp;
    $nevezes_db = getNevezesById($_POST["id"]);
    if ($nevezes_db == FALSE) return $resp;
    if ($nevezo_db->nevezo_id != $nevezes_db->nevezo_id) return $resp;

    $nevezo_errors = array();
    $nevezo = new NevezoForm($nevezo_db->lak_orszag, $nevezo_db->lak_irsz, $nevezo_db->lak_varos, $nevezo_db->lak_utca, $nevezo_db->lak_szam, $nevezo_db->tel, $nevezo_db->isk_nev, $nevezo_db->isk_orszag, $nevezo_db->isk_irsz, $nevezo_db->isk_varos, $nevezo_db->isk_utca, $nevezo_db->isk_szam, $nevezo_db->polo);
    /*
    if (trim($nevezo->vezeteknev) == "") array_push($nevezo_errors, "vezeteknev");
    if (trim($nevezo->keresztnev) == "") array_push($nevezo_errors, "keresztnev");
    if ($nevezo->nem != 1 && $nevezo->nem != 2) array_push($nevezo_errors, "nem");
    if (trim($nevezo->szulhely) == "") array_push($nevezo_errors, "szulhely");
    if ($nevezo->szulev < (YEAR - 18) || $nevezo->szulev > YEAR) array_push($nevezo_errors, "szulev");
    if ($nevezo->szulho < 1 || $nevezo->szulho > 12) array_push($nevezo_errors, "szulho");
    else {
      switch ($nevezo->szulho) {
        case 1:
        case 3:
        case 5:
        case 7:
        case 8:
        case 10:
        case 12: if($nevezo->szulnap < 1 || $nevezo->szulnap > 31) array_push($nevezo_errors, "szulnap"); break;
        case 4:
        case 6:
        case 9:
        case 11: if($nevezo->szulnap < 1 || $nevezo->szulnap > 30) array_push($nevezo_errors, "szulnap"); break;
        case 2: if($nevezo->szulnap < 1 || $nevezo->szulnap > 29) array_push($nevezo_errors, "szulnap"); break;
      }
    }
    */
    if (trim($nevezo->lak_orszag ?? "") == "") array_push($nevezo_errors, "lak_orszag");
    if (trim($nevezo->lak_irsz ?? "") == "") array_push($nevezo_errors, "lak_irsz");
    if (trim($nevezo->lak_varos ?? "") == "") array_push($nevezo_errors, "lak_varos");
    if (trim($nevezo->lak_utca ?? "") == "") array_push($nevezo_errors, "lak_utca");
    if (trim($nevezo->lak_szam ?? "") == "") array_push($nevezo_errors, "lak_szam");
    if (trim($nevezo->tel ?? "") == "") array_push($nevezo_errors, "tel");
    //if (!preg_match('/^[0-9a-zA-Z\._-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,3}$/', $nevezo->email)) array_push($nevezo_errors, "email");
    if (trim($nevezo->isk_nev ?? "") != "") {
      if (trim($nevezo->isk_orszag ?? "") == "") array_push($nevezo_errors, "isk_orszag");
      if (trim($nevezo->isk_irsz ?? "") == "") array_push($nevezo_errors, "isk_irsz");
      if (trim($nevezo->isk_varos ?? "") == "") array_push($nevezo_errors, "isk_varos");
      if (trim($nevezo->isk_utca ?? "") == "") array_push($nevezo_errors, "isk_utca");
      if (trim($nevezo->isk_szam ?? "") == "") array_push($nevezo_errors, "isk_szam");
    }
    //if ($nevezo->honnan < 1 || $nevezo->honnan > 8) array_push($nevezo_errors, "honnan");
    //if ($nevezo->honnan == 8 && trim($nevezo->egyebhely) == "") array_push($nevezo_errors, "egyebhely");
    //if ($nevezo->honnan != 8 && trim($nevezo->egyebhely) != "") array_push($nevezo_errors, "egyebhely");
    if ($nevezo->polo < 1 || $nevezo->polo > 4) array_push($nevezo_errors, "polo");

    $nevezes_errors = array();
    $nevezes = new NevezesForm($nevezes_db->nevezes_id, $nevezes_db->cim, $nevezes_db->keszites_eve, $nevezes_db->csapat, $nevezes_db->tipus, $nevezes_db->egyebtipus, $nevezes_db->tartalom, $nevezes_db->keszites, $nevezes_db->futtatas, $nevezes_db->adathordozo, $nevezes_db->url, $nevezes_db->egyebmedium);
    if (trim($nevezes->cim ?? "") == "") array_push($nevezes_errors, "cim");
    if ($nevezes->keszites_eve < (YEAR - 18) || $nevezes->keszites_eve > YEAR) array_push($nevezes_errors, "keszites_eve");
    //if ($nevezes->csapat != 1 && $nevezes->csapat != 2) array_push($nevezes_errors, "csapat");
    //if ($nevezes->csapat != 2 && $nevezes->letszam > 1) array_push($nevezes_errors, "letszam");
    //if ($nevezes->csapat ==2 ) {
    if ($nevezes->csapat != null) {
      if (count($nevezes->csapat->tagok) < 1) array_push($nevezes_errors, "letszam");
      if (trim($nevezes->csapat->nev ?? "") == "") array_push($nevezes_errors, "csapatnev");
    //}
      for ($i=1; $i<count($nevezes->csapat->tagok); $i++) {
        if ($nevezes->csapat->tagok[$i-1]->nev == "") array_push($nevezes_errors, "csapattag_nev_" . $i);
        if ($nevezes->csapat->tagok[$i-1]->nem != 1 && $nevezes->csapat->tagok[$i-1]->nem != 2) array_push($nevezes_errors, "csapattag_nem_" . $i);
        if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $nevezes->csapat->tagok[$i-1]->szul)) array_push($nevezes_errors, "szul_" . $i);
        /*
        if ($nevezes->csapat->tagok[$i-1]->szulev < (YEAR - 18) || $nevezes->csapat->tagok[$i-1]->szulev > YEAR) array_push($nevezes_errors, "csapattag_szulev_" . $i);
        if ($nevezes->csapat->tagok[$i-1]->szulho < 1 || $nevezes->csapat->tagok[$i-1]->szulho > 12) array_push($nevezes_errors, "csapattag_szulho_" . $i);
        else {
          switch ($nevezes->csapat->tagok[$i-1]->szulho) {
            case 1:
            case 3:
            case 5:
            case 7:
            case 8:
            case 10:
            case 12: if ($nevezes->csapat->tagok[$i-1]->szulnap < 1 || $nevezes->csapat->tagok[$i-1]->szulnap > 31) array_push($nevezes_errors, "csapattag_szulnap_" . $i); break;
            case 4:
            case 6:
            case 9:
            case 11: if ($nevezes->csapat->tagok[$i-1]->szulnap < 1 || $nevezes->csapat->tagok[$i-1]->szulnap > 30) array_push($nevezes_errors, "csapattag_szulnap_" . $i); break;
            case 2: if ($nevezes->csapat->tagok[$i-1]->szulnap < 1 || $nevezes->csapat->tagok[$i-1]->szulnap > 29) array_push($nevezes_errors, "csapattag_szulnap_" . $i); break;
          }
        }
        */
      }
    }
    if ($nevezes->tipus < 1 || $nevezes->tipus > 10) array_push($nevezes_errors, "tipus");
    //if ($nevezes->tipus == 10 && trim($nevezes->egyebtipus) == "") array_push($nevezes_errors, "egyebtipus");
    //if ($nevezes->tipus != 10 && trim($nevezes->egyebtipus) != "") array_push($nevezes_errors, "egyebtipus");
    if (trim($nevezes->tartalom ?? "") == "") array_push($nevezes_errors, "tartalom");
    if (trim($nevezes->keszites ?? "") == "") array_push($nevezes_errors, "keszites");
    if (trim($nevezes->futtatas ?? "") == "") array_push($nevezes_errors, "futtatas");
    if ($nevezes->adathordozo < 1 || $nevezes->adathordozo > 4) array_push($nevezes_errors, "adathordozo");
    if ($nevezes->adathordozo == 4 && trim($nevezes->url ?? "") == "") array_push($nevezes_errors, "url");
    if ($nevezes->adathordozo != 4 && trim($nevezes->url ?? "") != "") array_push($nevezes_errors, "url");
    //if ($nevezes->adathordozo == 5 && trim($nevezes->egyebmedium ?? "") == "") array_push($nevezes_errors, "egyebmedium");
    //if ($nevezes->adathordozo != 5 && trim($nevezes->egyebmedium ?? "") != "") array_push($nevezes_errors, "egyebmedium");
    //if ($nevezes->virus != 1 && $nevezes->virus != 2) array_push($nevezes_errors, "virus");
    $del_files = FALSE;
    $files = getFiles($nevezes_db->nevezes_id);
    if ($nevezes->adathordozo == 3) {
      if (count($files) == 0) array_push($nevezes_errors, "feltoltes");
    } else {
      if (count($files) != 0) $del_files = TRUE;
    }

    if (count($nevezo_errors) == 0 && count($nevezes_errors) == 0) {
      closeNevezo($nevezo_db->nevezo_id);
      closeNevezes($nevezes_db->nevezes_id);
      if ($del_files) {
        for ($i=0; $i<count($files); $i++) {
          delFile($files[$i]->fid);
          unlink($files[$i]->location);
        }
      }
      $resp = "{\"response\":{\"code\":0}}";
      return $resp;
    } else {
      $errors = array_merge($nevezo_errors, $nevezes_errors);
      $resp = "{\"response\":{\"code\":2, \"errors\":[";
      for ($i=0; $i<count($errors); $i++) {
        if ($i != 0) $resp .= ",";
        $resp .= "\"" . $errors[$i] . "\"";
      }
      $resp .= "]}}";
      return $resp;
    }
  }

  session_start();

  if (empty($_POST['cmd']) && empty($_GET['cmd'])) exit;
  $cmd = (!empty($_POST['cmd'])) ? $_POST['cmd'] : $_GET['cmd'];

  $resp = "{}";
  switch ($cmd) {
    case "login": $resp = cmd_login(); break;
    case "logout": $resp = cmd_logout(); break;
    case "getformdata": $resp = cmd_getformdata(); break;
    case "deleteform": $resp = cmd_deleteform(); break;
    case "getfilelist": $resp = cmd_getfilelist(); break;
    case "deletefile": $resp = cmd_deletefile(); break;
    case "uploadfile": $resp = cmd_uploadfile(FALSE); break;
    case "savedata": $resp = cmd_savedata(); break;
    case "closeform": $resp = cmd_closeform(); break;
    case "uploadform": $resp = cmd_uploadfile(TRUE); break;
  }

  header("Content-type: application/json; charset=UTF-8");
  echo $resp;

?>
