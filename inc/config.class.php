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

class PluginEngageConfig extends CommonDBTM {

   static private $_instance = NULL;
   static $rightname         = 'config';


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
    * Check if the passed itemtype is in the blacklist
    *
    * @param  string $itemtype
    *
    * @return bool
    */
   public static function canItemtype($itemtype = '') {
      return (!class_exists($itemtype) || $itemtype == 'Ticket');
   }

   static function showTechnicianLabel($item) {
      if (!self::canView()) {
            return false;
      }

      if (isset($item['item'])
         && $item['item'] instanceof CommonDBTM){
         $itemtype = get_class($item['item']);
         if(self::canItemtype($itemtype)){
            $config = self::getInstance();

            $whoare = User::getNameForLog($config->fields['users_id_tech']);
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
   static function getInstance() {

      if (!isset(self::$_instance)) {
         self::$_instance = new self();
         if (!self::$_instance->getFromDB(1)) {
            self::$_instance->getEmpty();
         }
      }
      return self::$_instance;
   }

   /**
    * Singleton for the unique config record
    */
   static function showConfigForm() {

      $config = self::getInstance();

      $config->showFormHeader();

      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='2' class='center' width='100%'>".__('Tech in charge')."</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__("New ticket in charge of", "engage")."</td><td>";
      User::dropdown(['name'   => 'users_id_tech',
            'right'  => 'interface',
            'value'  => $config->fields['users_id_tech']
      ]);
      echo "</td></tr>";
      echo "<td>".__("ITIL followup template to use", "engage")."</td><td>";
      ITILFollowupTemplate::dropdown(['name'   => 'itil_followup',
            'value'  => $config->fields['itil_followup']
      ]);
      echo "</td></tr>";

      //Html::closeForm();
      $config->showFormButtons(['candel'=>false]);

      return false;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType()=='Config') {
            return self::getName();
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType()=='Config') {
         self::showConfigForm($item);
      }
      return true;
   }
}
