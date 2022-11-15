<?php
include ("../../../inc/includes.php");

$plugin = new Plugin();

if (!$plugin->isInstalled('engage') || !$plugin->isActivated('engage')) {
   Html::displayNotFoundError();
}

Html::header(PluginEngageConfig::getTypeName(),
             $_SERVER['PHP_SELF'],
             "admin",
             "pluginengageconfig",
	     "config");

$pfConfig = new PluginEngageConfig();
Toolbox::logDebug("Opening Engage");

if (isset($_POST['update'])) {
    $pfConfig->update($_POST);
    Html::back();
}

PluginEngageConfig::displayMenu();

Html::footer();
