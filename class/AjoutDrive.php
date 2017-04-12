<?php

include_once __DIR__ . '/../vendor/autoload.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Description of AjoutDrive
 *
 * @author Developpement
 */
class AjoutDrive {

    private $unClient;
    private $scopes = array('https://www.googleapis.com/auth/drive');
    private $drive;
    private $chunkSizeBytes ;

    function __construct($unClient = null) {
        $this->chunkSizeBytes = 1 * 1024 * 1024;
        if ($unClient != null) {
            $this->unClient = $unClient;
        }
        $this->drive = new Google_Service_Drive($this->unClient);
    }

    public function ajout(Fichier $unFichier, $idDossierParent) {
        $fichierDrive = new Google_Service_Drive_DriveFile(array(
            'parents' => array($idDossierParent)
        ));
        $fichierDrive->name = $unFichier->getNom();
        $fichierDrive->setDescription('This is a ' . $fichierDrive->name . ' document');
         $this->unClient->setDefer(true);
        $request = $this->drive->files->create($fichierDrive);

        $media = new Google_Http_MediaFileUpload(
                 $this->unClient, $request, $unFichier->getMimeType(), null, true, $this->chunkSizeBytes
        );

        $media->setFileSize(filesize($unFichier->getCheminNom()));

        $status = false;
        $handle = fopen($unFichier->getCheminNom(), "rb");
        while (!$status && !feof($handle)) {
            $chunk = $this->readVideoChunk($handle, $this->chunkSizeBytes);
            $status = $media->nextChunk($chunk);
        }
        $result = false;
        if ($status != false) {
            $result = $status;
        }
        fclose($handle);
    }

    private function readVideoChunk($handle, $chunkSize) {
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

    /*
      private function retrieveAllFiles($service) {
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
     */
}
