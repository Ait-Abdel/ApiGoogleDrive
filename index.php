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
    // var_dump($authUrl);
}




if (isset($_SESSION['upload_token']) && $_SESSION['upload_token']) {
    $client->setAccessToken($_SESSION['upload_token']);
    $drive = new Google_Service_Drive($client);
    $dossier = retrieveAllFiles($drive);
    foreach ($dossier as $unDossier) {
        echo"- " . $unDossier->name . " <br>";
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
            // var_dump($media);
            $media->setFileSize(filesize($file_path));
            // var_dump(filesize($file_path));
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

include 'index.phtml';
