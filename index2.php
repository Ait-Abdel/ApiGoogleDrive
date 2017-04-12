<<<<<<< HEAD
<?php

session_start();
$url_array = explode('?', 'http://' . $_SERVER ['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
$url = $url_array[0];
var_dump($url);
include_once __DIR__ . '/vendor/autoload.php';
require '../googleKey.php';
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
if (isset($_SESSION['upload_token']) && $_SESSION['upload_token']) {
    $client->setAccessToken($_SESSION['upload_token']);
    $drive = new Google_Service_Drive($client);
    $dossier = retrieveAllFiles($drive);
    foreach ($dossier as $unDossier) {
        echo"- " . $unDossier->name . " <br>";
    }

    try {
        $optParams = array(
            "fields"=> "storageQuota"
        );
        $about = $drive->about->get($optParams);
        $storage = $about->getStorageQuota();
        echo "<br> Espace dispo : " .formatBytes($storage["limit"]);
        echo "<br> Espace Utiliser : " . formatBytes($storage["usage"]);
    } catch (Exception $e) {
        print "An error occurred: " . $e->getMessage();
    }

    /* fichier  ajouter au drive */
    $files = array();
    $dir = dir('files');
    while ($file = $dir->read()) {
        if ($file != '.' && $file != '..') {
            $files[] = $file;
        }
    }
    $dir->close();
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $client->getAccessToken()) {
        $service = new Google_Service_Drive($client);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
//        if (!isset($_POST["dossier"])) {
        $file = new Google_Service_Drive_DriveFile(array(
            'parents' => array($_POST["dossier"])
        ));
//        } else {
//            $file = new Google_Service_Drive_DriveFile();
//            echo "parent";
//        }
        $chunkSizeBytes = 1 * 1024 * 1024;
        foreach ($files as $file_name) {
            $file_path = 'files/' . $file_name;
            $mime_type = finfo_file($finfo, $file_path);
            $file->name = $file_name;
            $file->setDescription('This is a ' . $mime_type . ' document');
            $client->setDefer(true);
            $request = $service->files->create($file);
            // $file->setMimeType($mime_type);
            $media = new Google_Http_MediaFileUpload(
                    $client, $request, $mime_type, null, true, $chunkSizeBytes
            );
            $media->setFileSize(filesize($file_path));
            // Upload the various chunks. $status will be false until the process is
            // complete.
            $status = false;
            $handle = fopen($file_path, "rb");
            while (!$status && !feof($handle)) {
                // read until you get $chunkSizeBytes from TESTFILE
                // fread will never return more than 8192 bytes if the stream is read buffered and it does not represent a plain file
                // An example of a read buffered file is when reading from a URL
                $chunk = readVideoChunk($handle, $chunkSizeBytes);
                // $chunk = fread($handle, $chunkSizeBytes);
                $status = $media->nextChunk($chunk);
            }
            // The final value of $status will be the data from the API for the object
            // that has been uploaded.
            $result = false;
            if ($status != false) {
                $result = $status;
            }
            fclose($handle);
        }
        finfo_close($finfo);
        header('location:' . $url);
        exit;
    }
}

function readVideoChunk($handle, $chunkSize) {
    $byteCount = 0;
    $giantChunk = "";
    while (!feof($handle)) {
        // fread will never return more than 8192 bytes if the stream is read buffered and it does not represent a plain file
        $chunk = fread($handle, 8192);
        $byteCount += strlen($chunk);
        $giantChunk .= $chunk;
        if ($byteCount >= $chunkSize) {
            return $giantChunk;
        }
    }
    return $giantChunk;
}

function retrieveAllFiles($service) {
    $result = array();
    $pageToken = NULL;
    do {
        try {
            $parameters = array();
            $parameters['q'] = "mimeType='application/vnd.google-apps.folder' and 'root' in parents and trashed=false";
            if ($pageToken) {
                $parameters['pageToken'] = $pageToken;
            }
            $files = $service->files->listFiles($parameters);
            $result = array_merge($result, $files->getFiles());
            $pageToken = $files->getNextPageToken();
        } catch (Exception $e) {
            print "An error occurred: " . $e->getMessage();
            $pageToken = NULL;
        }
    } while ($pageToken);
    return $result;
}
function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
     $bytes /= pow(1024, $pow);
//     $bytes /= (1 << (10 * $pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow]; 
}

include 'index.phtml';
=======
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
		  console.log(data);
        var fileId = data.docs[0].id;
        alert('The user selected: ' + fileId);
      }
    }
    </script>
  </head>
  <body>
    <div id="result"></div>

    <!-- The Google API Loader script. -->
    <script type="text/javascript" src="https://apis.google.com/js/api.js?onload=loadPicker"></script>
  </body>
</html>
>>>>>>> 29a7dc08a71effba67d841253887d9bf0139afa4
