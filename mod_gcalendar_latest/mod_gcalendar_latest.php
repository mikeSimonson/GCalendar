<?php

/**
* Google calendar latest events module
* @author allon
* @version $Revision: 1.5.2 $
**/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Include the helper functions only once
require_once (dirname(__FILE__).DS.'helper.php');
// Get data from helper class
$gcalendar_data = modGcalendarLatestHelper::getFeed($params);

require( JModuleHelper::getLayoutPath( 'mod_gcalendar_latest' ) );
?>