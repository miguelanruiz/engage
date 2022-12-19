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

class PluginEngageProfile extends Profile {

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      return self::createTabEntry(__('Engage management', 'engage'));
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $engageprofile = new self();
      $engageprofile->showForm($item->getID());
      return true;
   }

   function showForm($ID, array $options = []) {

      if (!self::canView()) {
         return false;
      }

      echo "<div class='spaced'>";
      $profile = new Profile();
      $profile->getFromDB($ID);
      if ($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE])) {
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $rights = [['rights'    => [READ => __('Read'), UPDATE => __('Update')],
                  'label'     => __('Technician', 'engage'),
                  'field'     => 'plugin_engage_config']];

      $profile->displayRightsChoiceMatrix($rights, [  
         'canedit'       => $canedit,
         'default_class' => 'tab_bg_2',
         'title'         => __('General', 'engage')]);

      /*$rights = [['itemtype'  => 'PluginEngageConfig',
                            'label'     => PluginEngageConfig::getTypeName(Session::getPluralNumber()),
                            'field'     => 'plugin_engage_config']];

      $matrix_options['title'] = __('Engage management', 'engage');
      $profile->displayRightsChoiceMatrix($rights, $matrix_options);*/

      if ($canedit) {
         echo "<div class='center'>";
         echo Html::hidden('id', ['value' => $ID]);
         echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";
   }
}
