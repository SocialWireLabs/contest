<?php

gatekeeper();

$contestpost = get_input('contestpost');
$contest = get_entity($contestpost);
$edit = get_input('edit');

$now=time();

if($contest->getSubtype() == "contest" && $contest->canEdit()){

        $contest->close_time_voting = $now;

        if (elgg_is_active_plugin('event_manager')){
              $event_voting_guid=$contest->event_voting_guid;
              if ($event_voting=get_entity($event_voting_guid)){
                 $deleted_voting=$event_voting->delete();
                 if (!$deleted_voting){
                   register_error(elgg_echo("contest:eventmanagernotdeleted"));
                   forward($_SERVER['HTTP_REFERER']);
                 }
              }
        }

        //System message 
           system_message(elgg_echo("contest:voting_closed"));
           //Forward
           if (strcmp($edit,'no')==0) {
              forward($_SERVER['HTTP_REFERER']);
           } else {
              forward("contest/edit/$contestpost");
           }
}
?>