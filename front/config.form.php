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

include ("../../../inc/includes.php");

$plugin = new Plugin();

if (!$plugin->isInstalled('engage') || !$plugin->isActivated('engage')) {
   Html::displayNotFoundError();
}

$config = new PluginEngageConfig();

if (isset($_POST['add'])) {
    $config->add($_POST);
    Html::back();
}

if (isset($_POST['update'])) {
    $config->update($_POST);
    Html::back();
}

$id = Session::getActiveEntity();

Html::redirect($CFG_GLPI["root_doc"]."/front/entity.form.php?forcetab=PluginEngageConfig\$1&id=".$id);

//Html::footer();