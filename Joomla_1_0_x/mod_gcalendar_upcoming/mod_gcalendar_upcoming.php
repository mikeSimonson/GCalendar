<?php

/**
* Google calendar upcoming events module
* @author allon
* @version $Revision: 2.0.0 $
**/

// no direct access
defined('_VALID_MOS') or die('Restricted access');

global $mosConfig_absolute_path, $database, $mosConfig_cachepath, $mosConfig_lang;

// Get the right language if it exists
if (file_exists($mosConfig_absolute_path."/modules/mod_gcalendar_upcoming/languages/".$mosConfig_lang.".php")){
	include_once($mosConfig_absolute_path."/modules/mod_gcalendar_upcoming/languages/".$mosConfig_lang.".php");
}else{
	include_once($mosConfig_absolute_path."/modules/mod_gcalendar_upcoming/languages/english.php");
}

// Include SimplePie RSS Parser, supports utf-8 and international character sets in newsfeeds
if(!class_exists('SimplePie')){
	include_once('mod_gcalendar_upcoming/simplepie.inc');
}

if(!class_exists('SimplePie_GCalendar')){
	//include Simple Pie processor class
	require_once ('mod_gcalendar_upcoming/simplepie-gcalendar.php');
}

$calName = $params->get('name', '');
if(empty($calName)){
	echo _GCALENDAR_UPCOMING_CALENDAR_NO_DEFINED;
	return;
}

$feed = new SimplePie_GCalendar();
$feed->set_show_past_events(FALSE);
$feed->set_sort_ascending(TRUE);
$feed->set_orderby_by_start_date(TRUE);
$feed->set_expand_single_events(TRUE);
$feed->enable_order_by_date(FALSE);

// check if cache directory exists and is writeable
$cacheDir = $mosConfig_cachepath .'/upcoming/';
if(!file_exists($cacheDir))
	mkdir($cacheDir, 0755);
if ( !is_writable( $cacheDir ) ) {	
	return 'Cache Directory Unwriteable';
	$cache_exists = false;
}else{
	$cache_exists = true;
}

//check and set caching
if($cache_exists) {
	$feed->set_cache_location($cacheDir);
	$feed->enable_cache();
	$cache_time = (intval($params->get( 'cache', 3600 )));
	$feed->set_cache_duration($cache_time);
}
else {
	$feed->enable_cache(FALSE);
}

$query = "SELECT id,xmlUrl FROM #__gcalendar where name='".$calName."'";
$database->setQuery( $query );
$results = $database->loadObjectList();
if(empty($results)){
	echo _GCALENDAR_UPCOMING_CALENDAR_NOT_FOUND.$calName;
	return;
}
$url = '';
foreach ($results as $result) {
	if(!empty($result->xmlUrl))
		$url = $result->xmlUrl;
}

$lg = '?hl='._LANGUAGE;
$feed->set_feed_url($url.$lg);
 
// Initialize the feed so that we can use it.
$feed->init();
 
if ($feed->error()){
	echo _GCALENDAR_UPCOMING_SP_ERROR.$feed->error();
	return;
}

// Make sure the content is being served out to the browser properly.
$feed->handle_content_type();

// How you want each thing to display.
// All bits listed below which are available:
// ###TITLE###, ###DESCRIPTION###, ###DATE###, ###FROM###, ###UNTIL###,
// ###WHERE###, ###BACKLINK###, ###LINK###, ###MAPLINK###
// You can put ###DATE### in here too if you want to, and disable the 'group by date' below.
$dsplLink = "<a href='###BACKLINK###'>###TITLE###</a>";
if($params->get( 'openWindow', 0 )==1)
	$dsplLink = "<a href='###LINK###' target='_blank'>###TITLE###</a>";
$event_display="<p>###DATE### ###FROM###<br>".$dsplLink."</p>";

$tz = $params->get('timezone', '');
if($tz == '')
	$tz = $feed->get_timezone();

// Date format you want your details to appear
$dateformat=$params->get('dateFormat', 'd.m.Y'); // 10 March 2009 - see http://www.php.net/date for details
$timeformat=$params->get('timeFormat', 'H:i');; // 12.15am

$gcalendar_data = $feed->get_items();
// Loop through the array, and display what we wanted.
for ($i = 0; $i < sizeof($gcalendar_data) && $i <$params->get( 'max', 5 ); $i++){
	$item = $gcalendar_data[$i];
	
	// These are the dates we'll display
    $startDate = date($dateformat, $item->get_start_time());
    $startTime = date($timeformat, $item->get_start_time());
    $endTime = date($timeformat, $item->get_end_time());
    
    //Make any URLs used in the description also clickable
    $desc = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?,&//=]+)','<a href="\\1">\\1</a>', $item->get_description());

    // Now, let's run it through some str_replaces, and store it with the date for easy sorting later
    $temp_event=$event_display;
    $temp_event=str_replace("###TITLE###",$item->get_title(),$temp_event);
    $temp_event=str_replace("###DESCRIPTION###",$desc,$temp_event);
    $temp_event=str_replace("###DATE###",$startDate,$temp_event);
    $temp_event=str_replace("###FROM###",$startTime,$temp_event);
    $temp_event=str_replace("###UNTIL###",$endTime,$temp_event);
    $temp_event=str_replace("###WHERE###",$item->get_location(),$temp_event);
    $temp_event=str_replace("###BACKLINK###",urldecode('index.php?option=com_gcalendar&task=event&eventID='.$item->get_id().'&calendarName='.$calName.'&ctz='.$tz),$temp_event);
    $temp_event=str_replace("###LINK###",$item->get_link(),$temp_event);
    $temp_event=str_replace("###MAPLINK###","http://maps.google.com/?q=".urlencode($item->get_location()),$temp_event);
    // Accept and translate HTML
    $temp_event=str_replace("&lt;","<",$temp_event);
    $temp_event=str_replace("&gt;",">",$temp_event);
    $temp_event=str_replace("&quot;","\"",$temp_event);

	echo $temp_event;
}
?>