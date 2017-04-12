<?php

require_once './class/Fichier.php';

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Dossier
 *
 * @author Developpement
 */
class Dossier {

    private $chemin;
    private $lesFichiers;

    function __construct($chemin) {
        $this->lesFichiers = array();
        $this->chemin = $chemin;
        $dir = dir($this->chemin);
        while ($file = $dir->read()) {
            if ($file != '.' && $file != '..') {
                $this->lesFichiers[] = new Fichier($this->chemin, $file);
            }
        }

        $dir->close();
    }

    public function toString() {
        echo "Chemin du dossier : " . $this->chemin . "</br>";
        echo " les fichiers : ";
        echo "</br></br>";
        foreach ($this->lesFichiers as $unFichier) {
            $unFichier->toString();
            echo "</br>";
        }
    }
    
    function getChemin() {
        return $this->chemin;
    }

    function getLesFichiers() {
        return $this->lesFichiers;
    }



}
