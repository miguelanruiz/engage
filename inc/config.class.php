<?php

/**
 * -------------------------------------------------------------------------
 * engage plugin for GLPI is a tool designed to facilitate user assignment 
 * and SLA compliance.
 * Copyright (C) 2022 by the engage Development Team.
 * -------------------------------------------------------------------------
 * 
 * LICENSE
 *
 * This file is part of Engage.
 *
 * Engage is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Engage is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Engage. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * @package     Engage
 * @author      Miguel Angel Ruiz (miguelangelrtorresco@gmail.com)
 * @copyright   Copyright (C) 2022 by the engage plugin team.
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt GPLv3+   
 * @link        https://github.com/miguelanruiz/engage
 * --------------------------------------------------------------------------
 */

use Glpi\Application\View\TemplateRenderer;

class PluginEngageConfig extends CommonDBTM {

   static private $_instance = NULL;
   static $rightname         = 'config';

   const CONFIG_PARENT       = 0;
   
   const ENABLED             = 1;
   const DISABLED            = 0;

   const INCIDENT            = 1;
   const REQUEST             = 2;
   const MIXED               = 3;

   static function canCreate() {
      return Session::haveRight('config', UPDATE);
   }


   static function canView() {
      return Session::haveRight('config', READ);
   }


   static function getTypeName($nb=0) {
      return __('Setup');
   }


   function getName($with_comment=0) {
      return __('Engage', 'engage');
   }

   /**
    * Check if the passed itemtype is in the blacklist
    *
    * @param  string $itemtype
    *
    * @return bool
    */
    public static function canItemtype($itemtype = '') {
      return (!class_exists($itemtype) || $itemtype == 'Ticket');
   }

   /**
    * Display the menu of plugin
    *
    */
   public static function displayMenu($options = []){
      global $CFG_GLPI;
      $pConfig = new PluginEngageConfig();
      $pConfig->fields['id'] = 1;
      $pConfig->showConfigForm();
   
      return true;
      
   }

   /**
    * Singleton for the unique config record
    */
    static function getInstance($ID) {

      if (!isset(self::$_instance)) {
         self::$_instance = new self();
         if (!self::$_instance->getFromDBByCrit(['entities_id' => $ID])) {
            self::$_instance->getEmpty();
         }
      }
      return self::$_instance;
   }

   /**
    * Default values for instance
    */
   public function post_getEmpty(){
      $this->fields['id'] = 0;
      $this->fields['users_id_tech'] = 0;
      $this->fields['itil_followup'] = 0;
      $this->fields['entities_id'] = 0;
      $this->fields['is_recursive'] = 1;
      $this->fields['is_active'] = 1;
      $this->fields['ticket_type'] = self::MIXED;
   }

   /**
    * Display the technician on Tickets
    */
   public static function displayTechnician($options = []){
      global $CFG_GLPI;
      $pConfig = new PluginEngageConfig();
      $pConfig->fields['id'] = 1;
      $pConfig->showTechnicianLabel($options);
   
      return true;
   }

   /**
     * Get criteria to restrict to current entities of the user
     *
     * @since 9.2
     *
     * @param string $table             table where apply the limit (if needed, multiple tables queries)
     *                                  (default '')
     * @param string $field             field where apply the limit (id != entities_id) (default '')
     * @param mixed $value              entity to restrict (if not set use $_SESSION['glpiactiveentities']).
     *                                  single item or array (default '')
     * @param boolean $is_recursive     need to use recursive process to find item
     *                                  (field need to be named recursive) (false by default, set to auto to automatic detection)
     * @param boolean $complete_request need to use a complete request and not a simple one
     *                                  when have acces to all entities (used for reminders)
     *                                  (false by default)
     *
     * @return array of criteria
     */
   public static function getConfigForEntity($value = '') {
      // !='0' needed because consider as empty
      $dbu = new DbUtils();
      $table = getTableForItemtype('PluginEngageConfig');
      $field = "$table.entities_id";

      $ancestors = [];
      if (is_array($value)) {
         $ancestors = $dbu->getAncestorsOf("glpi_entities", $value);
         $ancestors = array_diff($ancestors, $value);
      } else if (strlen($value) == 0) {
         $ancestors = $_SESSION['glpiparententities'] ?? [];
      } else {
         $ancestors = $dbu->getAncestorsOf('glpi_entities', $value);
      }
      array_push($ancestors,$value);
      $ancestors = array_reverse($ancestors);
      $config = new self();
      foreach ($ancestors as $key => $value) {
         if (!$config->getFromDBByCrit(['entities_id' => $value])) {
            $config->getEmpty();
            }
            
         if($config->isNewItem() 
            || ($config->fields['users_id_tech'] == PluginEngageConfig::CONFIG_PARENT 
               && !$config->fields['is_active'] == PluginEngageConfig::DISABLED)){
            continue;
         }

         if(!isset($config->fields['is_active']) || $config->fields['is_active'] == PluginEngageConfig::DISABLED){
            break;
         }

         return $config;
      }
      return $config->getEmpty();
   }

   static function showTechnicianLabel($item) {
      if (!self::canView()) {
            return false;
      }

      if (isset($item['item'])
         && $item['item'] instanceof CommonDBTM){
         $itemtype = get_class($item['item']);
         if(self::canItemtype($itemtype) && $item['item']->fields['entities_id'] >= 0){
            $config = self::getConfigForEntity($item['item']->fields['entities_id']);

            if($config->fields['users_id_tech'] && $config->fields['is_active']){
               $whoare = User::getNameForLog($config->fields['users_id_tech']);
            } else {
               $whoare = __('Not assigned or disabled');
            }

            $field_class = "form-field row col-12 d-flex align-items-center mb-2";
            $label_class = "col-form-label col-xxl-4 text-xxl-end";
            $input_class = "col-xxl-8 field-container";
            echo "<div class='$field_class'>";
            echo "<label class='$label_class'>".
               __('Technician', 'engage').
            "</label>";
            echo "<div class='$input_class'>";
            echo "<span class='entity-badge' title='Technician in charge'><span class='text-nowrap'>".$whoare."</span></span>";
            echo "</div>";
            echo "</div>";
         }
      }
   }

   /**
    * Singleton for the unique config record
    */
   static function showConfigForm(Entity $entity) {

      $config = self::getInstance($entity->getEntityID());

      //$config->showFormHeader();
      $rand = mt_rand();
      echo "";
      echo "<div>";
      echo "<form name='engageconfig_form$rand' id='engageconfig_form$rand' method='post' action='";
      echo self::getFormUrl()."'>";
      echo "<input type=hidden name='entities_id' value='".$entity->getEntityID()."'>";
      echo "<table class='tab_cadre_fixe'>";

      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='2' class='center' width='100%'>".__('Tech in charge')."</th>";
      echo "</tr>";

      echo "</td></tr>";
      echo "</td><td>" . __('Enable') . "</td><td colspan='3'>";
      Dropdown::showYesNo("is_active", $config->fields['is_active']);
      echo "</td></tr>\n";

      if($config->fields['is_active']){
         TemplateRenderer::getInstance()->display('@engage/pages/entity_setup.html.twig', [
            'canedit' => Session::haveRight(self::$rightname, UPDATE),
            'config'  => $config,
         ]);

         echo "<tr class='tab_bg_1'>";
         echo "<td>".__("New ticket in charge of", "engage")."</td><td>";
         User::dropdown(['name'   => 'users_id_tech',
               'right'  => 'interface',
               'value'  => $config->fields['users_id_tech'],
               'emptylabel' => $entity->getEntityID() ? __('Inherit from the parent'): Dropdown::EMPTY_VALUE,
               'width'  => '250px'
         ]);
         echo "</td></tr>";
         echo "<td>".__("ITIL followup template to use", "engage")."</td><td>";
         ITILFollowupTemplate::dropdown(['name'   => 'itil_followup',
               'value'  => $config->fields['itil_followup'],
               'width'  => '250px',
               'comments' => false
         ]);

         echo "</td></tr>";
         echo "</td><td>" . __('Recursive') . "</td><td colspan='3'>";
         Dropdown::showYesNo("is_recursive", $config->fields['is_recursive']);
         echo "</td></tr>\n";
      }

      //Html::closeForm();
      $config->showFormButtons(['withtemplate' => 0,'candel'=> false]);

      return false;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='Entity') {
            return self::getName();
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='Entity') {
         self::showConfigForm($item);
      }
      return true;
   }
}
