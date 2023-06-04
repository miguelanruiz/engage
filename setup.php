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

use Glpi\Plugin\Hooks;

define('PLUGIN_ENGAGE_VERSION', '1.0.1');

// Minimal GLPI version, inclusive
define("PLUGIN_ENGAGE_MIN_GLPI_VERSION", "10.0.0");
// Maximum GLPI version, exclusive
define("PLUGIN_ENGAGE_MAX_GLPI_VERSION", "10.0.99");

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_engage()
{
    global $PLUGIN_HOOKS, $CFG_GLPI;

    $Plugin = new Plugin();

    $PLUGIN_HOOKS['csrf_compliant']['engage'] = true;
    if ($Plugin->isActivated('engage')) {

        Plugin::registerClass('PluginEngageConfig', ['addtabon' => ['Entity']]);
        Plugin::registerClass('PluginEngageProfile', ['addtabon' => ['Profile']]);
        
        $PLUGIN_HOOKS[Hooks::PRE_ITEM_FORM]['engage'] = ['PluginEngageConfig','displayTechnician'];
        $PLUGIN_HOOKS[Hooks::ITEM_ADD]['engage'] = ['Ticket' => [ 'PluginEngageTicket','createFollowup']];
        $PLUGIN_HOOKS['config_page']['engage'] = 'front/config.form.php';

    }
}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_engage()
{
    return [
        'name'           => 'Simple Engage Service',
        'shortname'      => 'engage',
        'version'        => PLUGIN_ENGAGE_VERSION,
        'author'         => '<a href="https://www.imagunet.com">Imagunet, Miguel Ruiz</a>',
        'license'        => 'GPLv3+',
        'homepage'       => 'https://github.com/miguelanruiz/engage/',
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_ENGAGE_MIN_GLPI_VERSION,
                'max' => PLUGIN_ENGAGE_MAX_GLPI_VERSION,
                'dev' => false,
            ]
        ]
    ];

}

/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_engage_check_prerequisites()
{
    return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_engage_check_config($verbose = false)
{
    if (true) { // Your configuration check
        return true;
    }

    if ($verbose) {
        echo __('Installed / not configured', 'engage');
    }
    return false;
}

function plugin_engage_options()
{
    return [
        'autoinstall_disabled' => true,
    ];
}