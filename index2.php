<?php
session_start();
include_once __DIR__ . '/vendor/autoload.php';
require '../googleKey.php';
require './class/Dossier.php';
$url_array = explode('?', 'http://' . $_SERVER ['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
$url = $url_array[0];
var_dump($url);
$client = new Google_Client();
ajouterKey($client); // dans le fichier googleKey.php
$client->setRedirectUri($url);
$client->setScopes(array('https://www.googleapis.com/auth/drive'));

if (isset($_GET['action']) && $_GET['action'] == "deco") {
    unset($_SESSION['upload_token']);
}
var_dump($_SESSION);
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
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
        <title>Google Picker Example</title>

        <script type="text/javascript">

            // The Browser API key obtained from the Google API Console.
            // Replace with your own Browser API key, or your own key.
            var developerKey = '';

            // The Client ID obtained from the Google API Console. Replace with your own Client ID.
            var clientId = "667645267284-rp5gn6an6p6qodb1et79h0dah1bpni3q.apps.googleusercontent.com"

            // Replace with your own project number from console.developers.google.com.
            // See "Project number" under "IAM & Admin" > "Settings"
            // var appId = "smart-firmament-102115";
            var appId = "667645267284";

            // Scope to use to access user's Drive items.
            var scope = ['https://www.googleapis.com/auth/drive'];

            var pickerApiLoaded = false;
            var oauthToken;

            // Use the Google API Loader script to load the google.picker script.
            function loadPicker() {
                gapi.load('auth', {'callback': onAuthApiLoad});
                gapi.load('picker', {'callback': onPickerApiLoad});
            }

            function onAuthApiLoad() {
                window.gapi.auth.authorize(
                        {
                            'client_id': clientId,
                            'scope': scope,
                            'immediate': false
                        },
                        handleAuthResult);
            }

            function onPickerApiLoad() {
                pickerApiLoaded = true;
                createPicker();
            }

            function handleAuthResult(authResult) {
                if (authResult && !authResult.error) {
                    oauthToken = authResult.access_token;
                    createPicker();
                }
            }

            // Create and render a Picker object for searching images.
            function createPicker() {
                if (pickerApiLoaded && oauthToken) {
                    var view = new google.picker.DocsView(google.picker.ViewId.FOLDERS).setParent('root').setSelectFolderEnabled(true)
                    view.setMimeTypes("application/vnd.google-apps.folder");
                    var picker = new google.picker.PickerBuilder()
                            .enableFeature(google.picker.Feature.NAV_HIDDEN)
                            .enableFeature(google.picker.Feature.MULTISELECT_ENABLED)
                            .setAppId(appId)
                            .setOAuthToken(oauthToken)
                            .addView(view)
                            .setLocale('fr')
                            // .addView(new google.picker.DocsView().setSelectFolderEnabled(true))
                            //.setDeveloperKey(developerKey)
                            .setCallback(pickerCallback)
                            .build();
                    picker.setVisible(true);
                }
            }

            // A simple callback implementation.
            function pickerCallback(data) {
                if (data.action == google.picker.Action.PICKED) {
                    var fileId = data.docs[0].id;
                    var url = "AjaxAjout.php";
                    $.post(
                            url,
                            {
                                dossier: fileId
                            },
                            function (result) {

                                retour = JSON.parse(result);
                                console.log(retour);
                                if (retour.resultat == "succes"){
                                    $("#result").html("succes");

                                }
                            }
                    );

                }
            }
        </script>
    </head>
    <body>
        <a href="http://localhost/Google/GoogleApi/index2.php?action=deco">Deco</a> <br/> <br/>
        <button onclick="loadPicker()"> ajouer au drive</button><br/><br/>

        <?php
        $unDossier = new Dossier("files");
        $unDossier->toString();
        ?>
        <div id="result"></div>

        <!-- The Google API Loader script. -->
        <script type="text/javascript" src="https://apis.google.com/js/api.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>

    </body>
</html>