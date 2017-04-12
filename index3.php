<?php
session_start();
require_once './class/Dossier.php';
require_once './class/AjoutDrive.php';
require '../googleKey.php';
$url_array = explode('?', 'http://' . $_SERVER ['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
$url = $url_array[0];
var_dump($url);
//unset($_SESSION['upload_token']);
$client = new Google_Client();
ajouterKey($client); // dans le fichier googleKey.php
$client->setRedirectUri($url);
$client->setScopes(array('https://www.googleapis.com/auth/drive'));

if (isset($_GET['action']) && $_GET['action'] == "deco") {
    unset($_SESSION['upload_token']);
}
/* authentication google */
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);
    // store in the session also
    $_SESSION['upload_token'] = $token;
    header('location:' . $url);
    exit;
}
if (!empty($_SESSION['upload_token'])) {
    $client->setAccessToken($_SESSION['upload_token']);
    if ($client->isAccessTokenExpired()) {
        unset($_SESSION['upload_token']);
    }
} else {
    $authUrl = $client->createAuthUrl();
    header('location:' . $authUrl);
}
var_dump($_SESSION['upload_token']);
if (isset($_SESSION['upload_token']) && $_SESSION['upload_token']) {
        $unAjout = new AjoutDrive($client);
        $unDossier = new Dossier("files");
        $i = 0;
        foreach ($unDossier->getLesFichiers() as $unFichier) {
            $unAjout->ajout($unFichier, "0BzKfLMtcL_iTRjVrMnQ3M1ptSGM");
            echo $i++;
        }

//        header('location:' . $url);
        exit;
}
?>
<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
0BzKfLMtcL_iTRjVrMnQ3M1ptSGM
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        //$unDossier->toString();
        ?>
    </body>
</html>
