<?php

gatekeeper();

$contestpost = get_input('contestpost');
$contest = get_entity($contestpost);
$edit = get_input('edit');

$now=time();

if($contest->getSubtype() == "contest" && $contest->canEdit()){

	$contest->close_time = $now;

	if (elgg_is_active_plugin('event_manager')){
	      $event_guid=$contest->event_guid;
	      if ($event=get_entity($event_guid)){
	         $deleted=$event->delete();
	         if (!$deleted){
	           register_error(elgg_echo("contest:eventmanagernotdeleted"));
	      	   forward($_SERVER['HTTP_REFERER']);
	         }
	      }
	}

	//System message 
	   system_message(elgg_echo("contest:answers_closed"));
	   //Forward
	   if (strcmp($edit,'no')==0) {
	      forward($_SERVER['HTTP_REFERER']);
	   } else {
	      forward("contest/edit/$contestpost");
	   }
}
?>