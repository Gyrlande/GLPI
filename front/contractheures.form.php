<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2015 Teclib'.

 http://glpi-project.org

 based on GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2014 by the INDEPNET Development Team.
 
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/** @file
* @brief
* @since version 0.84
*/

include ('../inc/includes.php');

Session::checkCentralAccess();

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["contracts_id"])) {
   $_GET["contracts_id"] = "";
}

$heure = new ContractHeures();
if (isset($_POST["add"])) {
   $heure->check(-1, CREATE,$_POST);

   if ($newID = $heure->add($_POST)) {
      Event::log($_POST['contracts_id'], "contracts", 4, "financial",
                 //TRANS: %s is the user login
                 sprintf(__('%s adds a heure'), $_SESSION["glpiname"]));
   }
   Html::back();

} else if (isset($_POST["purge"])) {
   $heure->check($_POST["id"], PURGE);

   if ($heure->delete($_POST, 1)) {
      Event::log($heure->fields['contracts_id'], "contracts", 4, "financial",
                 //TRANS: %s is the user login
                 sprintf(__('%s purges a heure'), $_SESSION["glpiname"]));
   }
   $contract = new Contract();
   $contract->getFromDB($heure->fields['contracts_id']);
   Html::redirect(Toolbox::getItemTypeFormURL('Contract').'?id='.$heure->fields['contracts_id'].
                  ($contract->fields['is_template']?"&withtemplate=1":""));

} else if (isset($_POST["update"])) {
   $heure->check($_POST["id"], UPDATE);

   if ($heure->update($_POST)) {
      Event::log($heure->fields['contracts_id'], "contracts", 4, "financial",
                 //TRANS: %s is the user login
                 sprintf(__('%s updates a heure'), $_SESSION["glpiname"]));
   }
   Html::back();

}

Html::displayErrorAndDie('Lost');
?>