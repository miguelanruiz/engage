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

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_engage_install()
{
   global $CFG_GLPI, $DB;

   $default_charset = DBConnection::getDefaultCharset();
   $default_collation = DBConnection::getDefaultCollation();
   $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

   $migration = new Migration(PLUGIN_ENGAGE_VERSION);

   $table = getTableForItemtype('PluginEngageConfig');

   if (!$DB->allow_signed_keys){
      $default_key_sign = "unsigned";
      $migration->displayMessage("You need consider review config_db, your database should allow SignedKeys, if yes please configure it and reinstall.");
      $migration->log("You need consider review config_db, your database should allow SignedKeys, if yes please configure it and reinstall.",true);
   }

   if (!$DB->tableExists($table)) {
      
      $query = "CREATE TABLE IF NOT EXISTS `{$table}` (
               `id` INT {$default_key_sign} NOT NULL auto_increment,
                  `users_id_tech` INT DEFAULT 0,
                  `itil_followup` INT DEFAULT 0,
                  `entities_id` INT DEFAULT NULL,
                  `is_recursive` INT DEFAULT 1,
                  PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
      $DB->queryOrDie($query, $DB->error());

      $migration->displayMessage("Table `{$table}` was created.");
   }

   $migration->addField($table,'is_active','boolean',['value' => '1']);
   $migration->displayMessage("New feature for disable interaction.");
   $migration->addField($table,'ticket_type','int',['value' => '3']);
   $migration->displayMessage("New feature for restrict incident or demand ticket.");

   $migration->executeMigration();
   $migration->displayMessage("Installation of Engage plugin was executed.");

   return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_engage_uninstall()
{
   global $DB;
   $config = getTableForItemtype('PluginEngageConfig');

   $tables = [
      $config
   ];

   foreach ($tables as $table) {

      $tablename = $table;

      if ($DB->tableExists($tablename)) {
         $DB->queryOrDie(
            "DROP TABLE `$tablename`",
            $DB->error()
         );
      }
   }
   
   return true;
}
