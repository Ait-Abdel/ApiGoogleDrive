<?php

session_start();
require_once './class/Dossier.php';
require_once './class/AjoutDrive.php';
require '../googleKey.php';
$url_array = explode('?', 'http://' . $_SERVER ['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
$url = $url_array[0];
$client = new Google_Client();
ajouterKey($client); // dans le fichier googleKey.php
$client->setRedirectUri($url);
$client->setScopes(array('https://www.googleapis.com/auth/drive'));
if (!empty($_SESSION['upload_token'])) {
    $client->setAccessToken($_SESSION['upload_token']);
    if ($client->isAccessTokenExpired()) {
        unset($_SESSION['upload_token']);
    }
} else {
    echo json_encode(array("resultat" => "erreur connexion L:20"));
    exit;
}
if (isset($_SESSION['upload_token']) && $_SESSION['upload_token']) {
    $unAjout = new AjoutDrive($client);
    $unDossier = new Dossier("files");
    $i = 0;
    foreach ($unDossier->getLesFichiers() as $unFichier) {
        $unAjout->ajout($unFichier, $_POST['dossier']);
    }

    echo json_encode(array("resultat" => "succes"));
    exit;
} else {

    echo json_encode(array("resultat" => "erreur connexion upload_token L:35"));
    exit;
}
?>