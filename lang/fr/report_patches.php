<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @author Valery Fremaux valery@valeisti.fr
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package report_patches
 * @category report
 */

$string['config_patches_closepattern'] = 'Motif de fermeture du patch';
$string['config_patches_openpattern'] = 'Motif d\'ouverture du patch';
$string['config_patches_scanexcludes'] = 'Motifs d\'exclusion du scanner';
$string['desc_patches_closepattern'] = 'Le motif qui permet de détecter une fin de modifciation non standard. Il doit être de préférence un commentaire pleine ligne. Ecemple : "// /PATCH" ';
$string['desc_patches_openpattern'] = 'Le motif qui permet de détecter le début d\'une modification non standard. Le motif doit être placé sur une ligne et peut être suivi d\'un commentaire indiquant la fonction du patch. Exemple : "// PATCH" ';
$string['desc_patches_scanexcludes'] = 'Entrer une série de motifs qui s\'appliquent à chaque fichier ou répertroire examiné pour les exclure de l\'examen. Le scanner exclut en standard les images GIF, JPG, PNG, les fichiers SWF, PDF. Séparer les motifs par des espaces.';
$string['endline'] = 'Fin';
$string['location'] = 'Fichier';
$string['nopatches'] = 'Aucun patches trouvés';
$string['orderbypath'] = 'ORdonner par fichier';
$string['orderbyfeature'] = 'Ordonner par fonctionnalité';
$string['patches'] = 'Patchs Noyau';
$string['pluginname'] = 'Patchs Noyau';
$string['patchesreport'] = 'Rapport de patches';
$string['patchessettings'] = 'Réglages des patches';
$string['patchlist'] = 'Liste des patches de customisation';
$string['purpose'] = 'Fonction';
$string['scan'] = 'Scanner le code';
$string['startline'] = 'Début';
