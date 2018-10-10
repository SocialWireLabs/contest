<?php

gatekeeper();

$contestpost =  get_input('guid');
$contest = get_entity($contestpost);

if ($contest->getSubtype() == "contest" && $contest->canEdit()) {

   $container_guid = $contest->container_guid;
   $container = get_entity($container_guid);
   $owner = get_entity($contest->getOwnerGUID());
   $owner_guid = $owner->getGUID();
	
   //Delete question files
   $files = elgg_get_entities_from_relationship(array('relationship' => 'question_file_link','relationship_guid' => $contestpost,'inverse_relationship' => false,'type' => 'object','limit'=>0));
   foreach($files as $one_file){
      $deleted=$one_file->delete();
      if (!$deleted){
         register_error(elgg_echo("contest:filenotdeleted"));
	 forward(elgg_get_site_url() . 'contest/group/' . $container_guid);
      }
   }
   
   //Delete answers
   $options = array('relationship' => 'contest_answer', 'relationship_guid' => $contestpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'contest_answer','limit'=>0);
   $users_responses=elgg_get_entities_from_relationship($options);
   foreach($users_responses as $one_response){
      $one_response_guid = $one_response->getGUID();
      $files_response = elgg_get_entities_from_relationship(array('relationship' => 'response_file_link','relationship_guid' => $one_response_guid,'inverse_relationship' => false,'type' => 'object','limit'=>0));
      foreach($files_response as $one_file){
         $deleted=$one_file->delete();
         if (!$deleted){
	    register_error(elgg_echo("contest:filenotdeleted"));
	    forward(elgg_get_site_url() . 'contest/group/' . $container_guid);
	 }
      }	  

      $access = elgg_set_ignore_access(true);
      $game_points = gamepoints_get_entity($one_response_guid);
      if ($game_points) {
         $deleted=$game_points->delete();
         if (!$deleted){
            register_error(elgg_echo("contest:gamepointsnotdeleted"));
            forward(elgg_get_site_url() . 'contest/group/' . $container_guid);
         }
      } 
      elgg_set_ignore_access($access);   
    
      $deleted=$one_response->delete();
      if (!$deleted){
	 register_error(elgg_echo("contest:answernotdeleted"));
	 forward(elgg_get_site_url() . 'contest/group/' . $container_guid);
      }
   }

   // Delete events created with the contest (if event_manager plugin)
   if (elgg_is_active_plugin('event_manager')){
      $event_guid=$contest->event_guid;
      if ($event=get_entity($event_guid)){
         $deleted=$event->delete();
         if (!$deleted){
            register_error(elgg_echo("contest:eventmanagernotdeleted"));
            forward(elgg_get_site_url() . 'contest/group/' . $container_guid);
         }
      }
      $event_voting_guid=$contest->event_voting_guid;
      if ($event_voting=get_entity($event_voting_guid)){
         $deleted_voting=$event_voting->delete();
         if (!$deleted_voting){
            register_error(elgg_echo("contest:eventmanagernotdeleted"));
            forward(elgg_get_site_url() . 'contest/group/' . $container_guid);
         }
      }
   }
   
   // Delete it!
   $deleted = $contest->delete();
   if ($deleted > 0) {
      system_message(elgg_echo("contest:deleted"));
   } else {
      register_error(elgg_echo("contest:notdeleted"));
   }
   forward(elgg_get_site_url() . 'contest/group/' . $container_guid);
}	

?>