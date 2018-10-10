<?php

gatekeeper();

$contestpost = get_input('contestpost');
$contest = get_entity($contestpost);

$this_user_guid = elgg_get_logged_in_user_guid();

if ($contest->getSubtype() == "contest") {

   $now=time();
   $answerpost = get_input('answerpost');
   $container = get_entity($contest->container_guid);
      
   $user_response=get_entity($answerpost);
   
   if (!empty($user_response)){

      $user_response_guid = $user_response->getGUID();

      $num_votes_user_response = $user_response->countAnnotations('all_votes');
        
      if (($now>=$contest->activate_time)&&($now<$contest->close_time)&&($num_votes_user_response==0)){

         $files_response = elgg_get_entities_from_relationship(array('relationship' => 'response_file_link','relationship_guid' => $user_response_guid,'inverse_relationship' => false,'type' => 'object','limit'=>0));
         foreach($files_response as $one_file){
            $deleted=$one_file->delete();
            if (!$deleted){
	       register_error(elgg_echo("contest:filenotdeleted"));
	       forward("contest/view/$contestpost/");
	    }
	 }

	 $access = elgg_set_ignore_access(true);
         $game_points = gamepoints_get_entity($user_response_guid);
         if ($game_points) {
            $deleted=$game_points->delete();
            if (!$deleted){
               register_error(elgg_echo("contest:gamepointsnotdeleted"));
               forward("contest/view/$contestpost/");
            }
         } 
         elgg_set_ignore_access($access);   

         $deleted=$user_response->delete();
         if (!$deleted){
	    register_error(elgg_echo("contest:answernotdeleted"));
	    forward("contest/view/$contestpost/");     
         }
         system_message(elgg_echo("contest:answerdeleted"));	
      } else {
         register_error(elgg_echo("contest:answernotdeleted"));
      }
   }
   forward("contest/view/$contestpost/");
}

?>
