<?php

include ('../../../inc/includes.php');

Html::header(PluginEngageConfig::getTypeName(),
             $_SERVER['PHP_SELF'],
             "admin",
             "pluginengageconfig",
             "config");

Html::footer();
