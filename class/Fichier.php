<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Fichier
 *
 * @author Developpement
 */
class Fichier {
    //put your code here
    private $chemin;
    private $nom;
    private $cheminNom;
    private $mimeType;
    private $F_INFO;
//    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    
    function __construct($chemin, $nom) {
        $this->F_INFO = finfo_open(FILEINFO_MIME_TYPE);
        $this->chemin = $chemin;
        $this->nom = $nom;
        $this->cheminNom = $chemin.DIRECTORY_SEPARATOR.$nom;
        $this->mimeType = finfo_file($this->F_INFO , $this->cheminNom);
        finfo_close($this->F_INFO);
    }
    
    function getChemin() {
        return $this->chemin;
    }

    function getNom() {
        return $this->nom;
    }

    function getCheminNom() {
        return $this->cheminNom;
    }

    function getMimeType() {
        return $this->mimeType;
    }


    public function toString(){
        echo "Nom du fichier : ".$this->nom."</br>";
        echo "Chemin : ".$this->chemin."</br>";
        echo "Chemin+nom : ".$this->cheminNom."</br>";
        echo "mimeType du fichier : ".$this->mimeType."</br>";
    }
    

    
    
}
