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
*/

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/// ContractCost class
/// since version 0.84
class ContractHeures extends CommonDBChild {

   // From CommonDBChild
   static public $itemtype = 'Contract';
   static public $items_id = 'contracts_id';
   public $dohistory       = true;


   static function getTypeName($nb=0) {
      return _n('Heures', 'Heures', $nb);
   }


   /**
    * @see CommonDBChild::prepareInputForAdd()
   **/
   function prepareInputForAdd($input) {
      return parent::prepareInputForAdd($input);
   }


   /**
    * @see CommonDBTM::prepareInputForUpdate()
   **/
   function prepareInputForUpdate($input) {
      return parent::prepareInputForUpdate($input);
   }


   /**
    * @see CommonGLPI::getTabNameForItem()
   **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      // can exists for template
      if (($item->getType() == 'Contract')
          && Contract::canView()) {
         $nb = 0;
         if ($_SESSION['glpishow_count_on_tabs']) {
            $nb = countElementsInTable('glpi_contractheures', "contracts_id = '".$item->getID()."'");
         }
         return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
      }
      return '';
   }


   /**
    * @param $item            CommonGLPI object
    * @param $tabnum          (default 1)
    * @param $withtemplate    (default 0)
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      self::showForContract($item, $withtemplate);
      return true;
   }

   function initBasedOnPrevious() {

      $ticket = new Ticket();
      if (!isset($this->fields['contracts_id'])
          || !$ticket->getFromDB($this->fields['contracts_id'])) {
         return false;
      }

      $lastdata = $this->getLastCostForContract($this->fields['contracts_id']);

      if (isset($lastdata['date_dernier_achat'])) {
         $this->fields['date_dernier_achat'] = $lastdata['date_dernier_achat'];
      }
      if (isset($lastdata['heures_contrat'])) {
         $this->fields['heures_contrat'] = $lastdata['heures_contrat'];
      }
      if (isset($lastdata['date_fin_contrat'])) {
         $this->fields['date_fin_contrat'] = $lastdata['date_fin_contrat'];
      }
   }

   /**
    * Get last datas for a contract
    *
    * @param $contracts_id        integer  ID of the contract
   **/
   function getLastCostForContract($contracts_id) {
      global $DB;

      $query = "SELECT *
                FROM `".$this->getTable()."`
                WHERE `contracts_id` = '$contracts_id'
                ORDER BY 'end_date' DESC, `id` DESC";

      if ($result = $DB->query($query)) {
         return $DB->fetch_assoc($result);
      }

      return array();
   }

   /**
    * Print the contract cost form
    *
    * @param $ID        integer  ID of the item
    * @param $options   array    options used
   **/
   function showForm($ID, $options=array()) {

      if ($ID > 0) {
         $this->check($ID, READ);
      } else {
         // Create item
         $options['contracts_id'] = $options['parent']->getField('id');
         $this->check(-1, CREATE, $options);
         $this->initBasedOnPrevious();
      }

      $this->showFormHeader($options);
      echo "<tr class='tab_bg_1'><td>".__('Nombre d\'heures')."</td><td>";
      Dropdown::showHours("heures_contrat", array('value' => $this->fields['heures_contrat']));
      echo "</td>";

      echo "<td>".__('Date d\'achat')."</td>";
      echo "<td>";
      Html::showDateField("date_dernier_achat", array('value' => $this->fields['date_dernier_achat']));
      echo "</td></tr>";
      $rowspan = 3;
      echo "<tr class='tab_bg_1'><td rowspan='$rowspan'>".__('Commentaires')."</td>";
      echo "<td rowspan='$rowspan' class='middle'>";
      echo "<textarea cols='45' rows='".($rowspan+3)."' name='commentaire' >".$this->fields["commentaire"].
           "</textarea>";
      echo "</td></tr><table></table>";
      $this->showFormButtonsHeures($options);
      return true;
   }

   /**
    * Print the contract costs
    *
    * @param $contract               Contract object
    * @param $withtemplate  boolean  Template or basic item (default '')
    *
    * @return Nothing (call to classes members)
   **/
   static function showForContract(Contract $contract, $withtemplate='') {
      global $DB, $CFG_GLPI;

      $ID = $contract->fields['id'];

      if (!$contract->getFromDB($ID)
          || !$contract->can($ID, READ)) {
         return false;
      }
      $canedit = $contract->can($ID, UPDATE);

      echo "<div class='center'>";

      $query = "SELECT *
                FROM `glpi_contractheures`
                WHERE `contracts_id` = '$ID'
                ORDER BY `date_dernier_achat`";

      $rand   = mt_rand();

      if ($canedit) {
         echo "<div id='viewheure".$ID."_$rand'></div>\n";
         echo "<script type='text/javascript' >\n";
         echo "function viewAddHeure".$ID."_$rand() {\n";
         $params = array('type'         => __CLASS__,
                         'parenttype'   => 'Contract',
                         'contracts_id' => $ID,
                         'id'           => -1);
         Ajax::updateItemJsCode("viewheure".$ID."_$rand",
                                $CFG_GLPI["root_doc"]."/ajax/viewsubitem.php", $params);
         echo "};";
         echo "</script>\n";
         echo "<div class='center firstbloc'>".
               "<a class='vsubmit' href='javascript:viewAddHeure".$ID."_$rand();'>";
         echo __('Ajouter des heures')."</a></div>\n";
      }

      if ($result = $DB->query($query)) {
         echo "<table class='tab_cadre_fixehov'>";
         echo "<tr><th colspan='5'>".self::getTypeName($DB->numrows($result))."</th></tr>";

         if ($DB->numrows($result)) {
            echo "<tr><th>".__('Date d\'achat')."</th>";
            echo "<th>".__('Date d\'expiration des heures')."</th>";
            echo "<th>".__('Nombre d\'heures')."</th>";
            echo "<th>".__('Commentaire')."</th></tr>";

         Session::initNavigateListItems(__CLASS__,
                              //TRANS : %1$s is the itemtype name,
                              //        %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'),
                                                Contract::getTypeName(1), $contract->getName()));

            $total = 0;
            while ($data = $DB->fetch_assoc($result)) {
               echo "<tr class='tab_bg_2' ".
                     ($canedit
                      ? "style='cursor:pointer' onClick=\"viewEditHeure".$data['contracts_id']."_".
                        $data['id']."_$rand();\"": '') .">";
              echo "<td>".HTml::convDate($data['date_dernier_achat'])."</td>";
              //printf(__('%1$s %2$s'), HTml::convDate($data['date_dernier_achat']),
                       //Html::showToolTip($data['commentaire'], array('display' => false)));
               if ($canedit) {
                  echo "\n<script type='text/javascript' >\n";
                  echo "function viewEditHeure" .$data['contracts_id']."_". $data["id"]. "_$rand() {\n";
                  $params = array('type'         => __CLASS__,
                                  'parenttype'   => 'Contract',
                                  'contracts_id' => $data["contracts_id"],
                                  'id'           => $data["id"]);
                  Ajax::updateItemJsCode("viewheure".$ID."_$rand",
                                         $CFG_GLPI["root_doc"]."/ajax/viewsubitem.php", $params);
                  echo "};";
                  echo "</script>\n";
               }
               echo "<td>".HTml::convDate($data['date_fin_contrat'])."</td>";
               echo "<td><strong>".HTml::convDateTimeCustom($data['heures_contrat'])."</strong></td>";
               $total += $data['heures_contrat'];
               echo "<td><p>".Html::resume_text($data['commentaire'], 100)."</p></td>";
               echo "</tr>";
               Session::addToNavigateListItems(__CLASS__, $data['id']);
               $id = $data['contracts_id'];
            }
            $requete = "SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(`glpi_contractheures`.`heures_contrat`)))
                        FROM `glpi_contracts`, `glpi_contractheures`
                        WHERE `glpi_contracts`.`id` = ".$id;
            $heures_totales = $DB->query($requete);
            $variable_finale = $DB->result($heures_totales,0,0);
            echo "<tr class='b noHover'><td colspan='3'>&nbsp;</td>";
            echo "<td class='right'>".__('Temps total').'</td>';
            echo "<td class='numeric'>".HTml::convDateTimeCustom($variable_finale).'</td></tr>';
         } else {
            echo "<tr><th colspan='5'>".__('No item found')."</th></tr>";
         }
         echo "</table>";
      }
      echo "</div><br>";
   }

}
?>