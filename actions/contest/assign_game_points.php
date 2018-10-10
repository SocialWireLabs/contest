<?php

gatekeeper();

elgg_load_library('contest');

$contestpost = get_input('contestpost');
	
$contest = get_entity($contestpost);
$container_guid = $contest->container_guid;
$container = get_entity($container_guid);

$access = elgg_set_ignore_access(true);

$options = array('relationship' => 'contest_answer', 'relationship_guid' => $contestpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'contest_answer', 'limit' => 0);
$user_responses_temp=elgg_get_entities_from_relationship($options);

if ($user_responses_temp){
   $num_user_responses = 0;
   foreach($user_responses_temp as $one_response){
      $user_responses[$num_user_responses]=$one_response;
      $num_user_responses=$num_user_responses+1;            
   }
   if ($num_user_responses>1) {
      $last_index = $num_user_responses-1;
      sort_responses_by_votes($user_responses,0,$last_index);
   }
}

$total_gamepoints = $contest->total_gamepoints;

if (strcmp($contest->option_type_grading_value,'contest_type_grading_percentage')==0){
   $number_winners = $contest->number_winners_type_grading_percentage;
   $total_number_votes_winners = 0;
   if ($user_responses) {
      $i=0;
      $prev_number_of_votes = -1;
      foreach ($user_responses as $one_user_response){
         $one_user_response_guid = $one_user_response->getGUID();
         $user_of_answer = $one_user_response->getOwnerEntity();
         $user_guid_of_answer = $user_of_answer->getGUID();
         // Do not punctuate the owners of the group
         if (($container->getOwnerGUID()==$user_guid_of_answer)||(check_entity_relationship($user_guid_of_answer,'group_admin',$container_guid))){
            continue;
         }
	 $number_of_votes = $one_user_response->countAnnotations('vote');
	 if ($prev_number_of_votes != $number_of_votes) {
            $total_number_votes_winners += $number_of_votes;
            $i=$i+1;
            if ($i>$number_winners) {
               break;
            }
	    $prev_number_of_votes = $number_of_votes;
	 }
      }
   }
} else {
   $gamepoints_for_winners_array = explode(',',$contest->gamepoints_type_grading_prearranged);
   $gamepoints_for_winners_array = array_map('trim', $gamepoints_for_winners_array);
   $number_winners = count($gamepoints_for_winners_array);
}

if ($user_responses) {
   $i=0;
   $ac_gamepoints=0;
   $prev_number_of_votes = -1;
   $prev_game_points = -1;
   foreach ($user_responses as $one_user_response){	
      $one_user_response_guid = $one_user_response->getGUID();
      $user_of_answer = $one_user_response->getOwnerEntity();
      $user_guid_of_answer = $user_of_answer->getGUID();
      // Do not punctuate the owners of the group
      if (($container->getOwnerGUID()==$user_guid_of_answer)||(check_entity_relationship($user_guid_of_answer,'group_admin',$container_guid))){
         continue;
      }

      $number_of_votes = $one_user_response->countAnnotations('vote');
      if ($prev_number_of_votes != $number_of_votes) {
         if ($i>=$number_winners) {
            $game_points = 0;
         } else {
            if ($i==($number_winners-1)) {
               $game_points = $total_gamepoints-$ac_gamepoints;
            } else {
	       if (strcmp($contest->option_type_grading_value,'contest_type_grading_percentage')==0){
                  $game_points = round($total_gamepoints*$number_of_votes/$total_number_votes_winners);
               } else {
	          $game_points = round($total_gamepoints*$gamepoints_for_winners_array[$i]*0.01);
	       }
	       $ac_gamepoints += $game_points;
	       if ($ac_gamepoints > $total_gamepoints) {
	          $ac_gamepoints -= $game_points;
	          $game_points = $total_gamepoints-$ac_gamepoints;
	          $ac_gamepoints += $game_points;
	       }
            }
         }
	 $prev_number_of_votes = $number_of_votes;
	 $prev_game_points = $game_points;
	 $i=$i+1;
      } else {
         $game_points = $prev_game_points;
      }

      $previous_game_points = gamepoints_get_entity($one_user_response_guid);

      if ($previous_game_points) {
         if ($game_points>0) {
            gamepoints_update($previous_game_points->getGUID(), $game_points);
         } else {
	    gamepoints_update($previous_game_points->getGUID(),"");
         }
      } else {
         if ($game_points>0) {
            $description = $contest->title;
            $user = $one_user_response->getOwnerEntity();
            $user_guid = $user->getGUID();
            if ($contest->subgroups) {
               $user_subgroup = elgg_get_entities_from_relationship(array('type_subtype_pairs' => array('group' => 'lbr_subgroup'),'container_guids' => $container_guid,'relationship' => 'member','inverse_relationship' => false,'relationship_guid' => $user_guid));
	       if ($user_subgroup) {
                  $user_subgroup=$user_subgroup[0];
                  $user_guid=$user_subgroup->getGUID();
	       }
            } 
            gamepoints_add($user_guid,$game_points,$one_user_response->getGUID(),$container_guid,$contest->subgroups,$description);
         }
      }
   }
}

elgg_set_ignore_access($access);

//System message
system_message(elgg_echo("contest:game_points_assigned"));

//Forward
forward("contest/view/$contestpost");   

?>