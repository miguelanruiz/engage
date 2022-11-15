<?php

/**
 * -------------------------------------------------------------------------
 * engage plugin for GLPI
 * Copyright (C) 2022 by the engage Development Team.
 * -------------------------------------------------------------------------
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
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
    * @global array $CFG_GLPI
    * @param string $type
    */
    public static function displayMenu($options = []){
       global $CFG_GLPI;
       $pfConfig = new PluginEngageConfig();
       $pfConfig->fields['id'] = 1;
       $pfConfig->showConfigForm();
       //$pfConfig->display();
    
       return true;
        
    }


   /**
    * Display the technician on Tickets
    *
    * @global array $CFG_GLPI
    * @param string $type
    */
    public static function displayTechnician($options = []){
       global $CFG_GLPI;
       $pfConfig = new PluginEngageConfig();
       $pfConfig->fields['id'] = 1;
       $pfConfig->showTechnicianLabel($options);
    
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
               _n('Engage', 'Engages', 3, 'engage').
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
      echo "<td>".__("Ticket in charge", "engage")."</td><td>";
      User::dropdown(['name'   => "users_id_tech",
            'right'  => 'interface',
	    'value'  => $config->fields['users_id_tech']
      ]);
      echo "</td></tr>";
      echo "<td>".__("ITIL followup template to use", "engage")."</td><td>";
      ITILFollowupTemplate::dropdown(['name'   => "name",
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

   /**
    * Add name + value in configuration if not exist
    *
    * @param string $name
    * @param string $value  
    * @return integer|false integer is the id of this configuration name
    */
    public function addValue($name, $value)
    { 
        $existing_value = $this->getValue($name);
        if (!is_null($existing_value)) {
            return $existing_value;
        } else {
            return $this->add(['type'  => $name,
                                 'value' => $value]);
        }
    }

   /**
    * Add multiple configuration values
    *
    * @param array $values configuration values, indexed by name
    * @param boolean $update say if add or update in database
    */
    public function addValues($values, $update = true)
    {

        foreach ($values as $type => $value) {
            if ($this->getValue($type) === null) {
                $this->addValue($type, $value);
            } elseif ($update == true) {
                $this->updateValue($type, $value);
            }
        }
    }

    /**
    * Update configuration value
    *
    * @param string $name name of configuration
    * @param string $value
    * @return boolean
    **/
    public function updateValue($name, $value)
    {
       
       // retrieve current config
        $config = current($this->find(['type' => $name]));
       
       // set in db
        if (isset($config['id'])) {
            $result = $this->update(['id' => $config['id'], 'value' => $value]);
        } else {
            $result = $this->add(['type' => $name, 'value' => $value]);
        }
         // set cache 
        
        return $result;
    }


    /**
    * Get configuration value with name
    *    
    * @param string $name name in configuration                                     * @return null|string|integer
    */
    public function getValue($name)
    {
        
        $config = current($this->find(['type' => $name]));
        if (isset($config['value'])) {
            return $config['value'];
        }
        return null;
    }
}
