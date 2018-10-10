<?php

elgg_load_library('contest');

if (!isset($vars['entity'])){
   register_error(elgg_echo("contest:notfound"));
   forward("mod/contest/index.php");
}
$contest=$vars['entity'];
$contestpost=$contest->getGUID();
$container_guid = $contest->container_guid;
$container=get_entity($container_guid);

$order_by=$vars['order_by'];
if (strcmp($order_by,"votes")==0)
   $order_by_votes=true;
else
   $order_by_votes=false;

$owner = $contest->getOwnerEntity();
$owner_guid = $owner->getGUID();
$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);

if ($contest->subgroups){
   $my_subgroups = elgg_get_entities_from_relationship(array('type_subtype_pairs' => array('group' => 'lbr_subgroup'),'container_guids' => $container_guid,'relationship' => 'member','inverse_relationship' => false,'relationship_guid' => $user_guid));
   if ($my_subgroups) {
      $my_guid=$my_subgroups[0]->getGUID();
   } else {
      $my_guid = $user_guid;
   }
} else {
   $my_guid = $user_guid;
}

$operator = false;
if ($container instanceof ElggGroup) {
   $group_guid=$contest->container_guid;
   $group = get_entity($group_guid);
   $group_owner_guid = $group->owner_guid;

   if (($owner_guid==$user_guid)||($group_owner_guid==$user_guid)||(check_entity_relationship($user_guid,'group_admin',$group_guid))){
      $operator=true;
   }
}

$offset = $vars['offset'];
$limit = 10;
$this_limit = $offset+$limit;

$answering_opened = false;
$voting_opened = false;
$now = time();
if (strcmp($contest->option_activate_value,'contest_activate_date')==0){
   if (($now>=$contest->activate_time)&&($now<$contest->close_time)){
      $answering_opened=true;
   }
} else {
   if ($now<$contest->close_time) {
      $answering_opened=true;
   }
}

if (((($now>=$contest->activate_time)&&(strcmp($contest->option_opened_voting_value,'contest_opened_voting_while_answering')==0))||(($now>$contest->close_time)&&(strcmp($contest->option_opened_voting_value,'contest_opened_voting_while_answering')!=0)))&&($now<$contest->close_time_voting)){
   $voting_opened=true;
}

$number_votes_for_response_by_user = $contest->number_votes_for_response_by_user;
$number_total_votes_by_user = $contest->number_total_votes_by_user;

$form_body = "<br>";

//Assign game points
if (($container instanceof ElggGroup) && ($contest->contest_with_gamepoints) && ($operator) && (!$answering_opened) && (!$voting_opened)) {
   $url_assign_game_points=elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/contest/assign_game_points?contestpost=" . $contestpost);
   $text_assign_game_points=elgg_echo("contest:assign_game_points");
   $link_assign_game_points="<a href=\"{$url_assign_game_points}\">{$text_assign_game_points}</a>";
   $form_body .= $link_assign_game_points;
   $form_body .= "<br>";
}

$form_body .= "<div class=\"contentWrapper\">";
$form_body .= "<div class=\"contest_frame\">";

//General comments
$num_comments =  $contest->countComments();
if ($num_comments>0)
   $contest_general_comments_label = elgg_echo('contest:general_comments') . " (" . $num_comments . ")";
else
   $contest_general_comments_label = elgg_echo('contest:general_comments');
$form_body .= "<p align=\"left\"><a onclick=\"contest_show_general_comments();\" style=\"cursor:hand;\">$contest_general_comments_label</a></p>";
$form_body .= "<div id=\"commentsDiv\" style=\"display:none;\">";
$form_body .= elgg_view_comments($contest);
$form_body .= "</div>";

//Open interval
if (($answering_opened)||($voting_opened)){
   if ($answering_opened) {
      if (strcmp($contest->option_activate_value,'contest_activate_date')==0){
         $friendlytime_from=date("d/m/Y",$contest->activate_time) . " " . elgg_echo("contest:at") . " " . date("G:i",$contest->activate_time);
         $friendlytime_to=date("d/m/Y",$contest->close_time) . " " . elgg_echo("contest:at") . " " . date("G:i",$contest->close_time);
         $open_answering_interval=elgg_echo('contest:answering_opened_from') . ": " . $friendlytime_from . " " . elgg_echo('contest:to') . ": " . $friendlytime_to;
      } else {
         $friendlytime_to=date("d/m/Y",$contest->close_time) . " " . elgg_echo("contest:at") . " " . date("G:i",$contest->close_time);
         $open_answering_interval=elgg_echo('contest:answering_opened_to') . ": " . $friendlytime_to;
      }
      if (!$voting_opened) {
         $open_voting_interval = elgg_echo('contest:opened_voting_after_answering');
      }
   }
   if ($voting_opened) {
       $friendlytime_to=date("d/m/Y",$contest->close_time_voting) . " " . elgg_echo("contest:at") . " " . date("G:i",$contest->close_time_voting);
        $open_voting_interval=elgg_echo('contest:voting_opened_to') . ": " . $friendlytime_to;
   }
   $open_interval = $open_answering_interval . "<br>" . $open_voting_interval;
} else {
   $open_interval = elgg_echo('contest:is_closed');
}

$form_body .= $open_interval;

if ($contest->contest_with_gamepoints == true){
   $form_body .= "<br>";
   $form_body .= elgg_echo('contest:points_distribute') . ": " . $contest->total_gamepoints;
   $form_body .= "<br>";
   if (strcmp($contest->option_type_grading_value,'contest_type_grading_percentage')==0){
      $form_body .= elgg_echo('contest:number_pupils_won') . ": " . $contest->number_winners_type_grading_percentage;
   } else{
      $gamepoints_for_winners_array = explode(',',$contest->gamepoints_type_grading_prearranged);
      $gamepoints_for_winners_array = array_map('trim', $gamepoints_for_winners_array);
      $form_body .= elgg_echo('contest:number_pupils_won') . ": " . count($gamepoints_for_winners_array);
   }
}

//===========================================================================================================================================

if ($voting_opened == false){

   if ($contest->contest_with_gamepoints == true){
      $form_body .= "<br><br>";
      $form_body .= "<b>" . elgg_echo('contest:pupils_won') . ": </b>";
   }

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
            if (($group_owner_guid==$user_guid_of_answer)||(check_entity_relationship($user_guid_of_answer,'group_admin',$group_guid))){
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
         if (($group_owner_guid==$user_guid_of_answer)||(check_entity_relationship($user_guid_of_answer,'group_admin',$group_guid))){
            continue;
         }

	 $number_of_votes = $one_user_response->countAnnotations('vote');
	 if ($prev_number_of_votes != $number_of_votes) {
            // Limit the number of winners showed ($i > 5)
            if ($i >= $number_winners || $i > 5) {
               break;
            } else {
               if ($i == ($number_winners-1)) {
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
            if ($game_points == 0){
               break;
            }
            $prev_number_of_votes = $number_of_votes;
	    $prev_game_points = $game_points;
            $i=$i+1;
	 } else {
	    $game_points = $prev_game_points;
	 }
	 $form_body .= "<br>";
         $form_body .= $game_points . " " . elgg_echo('contest:points_for') .  " ". $user_of_answer->getDisplayName();
      }
   }
} //end if ($voting_opened == false)
//===========================================================================================================================================

$form_body .= "<br><br>";

$form_body .= elgg_echo('contest:votes_for_response_by_user_label') . ": " . $number_votes_for_response_by_user;
$form_body .= "<br>";
$form_body .= elgg_echo('contest:total_votes_by_user_label') . ": " . $number_total_votes_by_user;

$form_body .= "</div>";
$form_body .= "</div>";

//Question body

$question_body ="";
if (strcmp($contest->question_html,"")!=0){
   $question_body .= "<p>" . "<b>" . elgg_echo('contest:question_simple_read') . "</b>" . "</p>";
   $question_body .= "<div class=\"contest_question_frame\">";
   $question_body .= elgg_view('output/longtext', array('value' => $contest->question_html));
   $question_body .= "</div>";
   if (strcmp($contest->question_type,"simple")!=0)
      $question_body .= "<br>";
}
switch ($contest->question_type) {
   case 'urls_files':
      $question_body .= "<p>" . "<b>" . elgg_echo('contest:question_urls_files_read') . "</b>" . "</p>";
      $question_body .= "<div class=\"contest_question_frame\">";
      $question_urls = explode(Chr(26),$contest->question_urls);
      $question_urls = array_map('trim',$question_urls);

      $files = elgg_get_entities_from_relationship(array('relationship' => 'question_file_link','relationship_guid' => $contestpost,'inverse_relationship' => false,'type' => 'object','subtype' => 'contest_question_file','limit'=>0));

      if ((count($question_urls)>0)&&(strcmp($question_urls[0],"")!=0)) {
         foreach ($question_urls as $one_url){
	          $comp_url = explode(Chr(24),$one_url);
            $comp_url = array_map('trim',$comp_url);
            $url_name = $comp_url[0];
            $url_value = $comp_url[1];
            if (elgg_is_active_plugin("sw_embedlycards")){
              $question_body .= "<div>
              <a class='embedly-card' href='$url_value'></a>
              </div>";
            }
            else if (elgg_is_active_plugin("hypeScraper"))
              $question_body .= elgg_view('output/sw_url_preview', array('value' => $url_value,));
            else
             $question_body .= "<a rel=\"nofollow\" href=\"$url_value\" target=\"_blank\">$url_value</a><br>";
          }
      }

      if ((count($files)>0)&&(strcmp($files[0]->title,"")!=0)){
	 foreach($files as $one_file) {
	    $params = $one_file->getGUID() . "_question";
      $icon = questions_set_icon_url($one_file, "small");
      $url_file = elgg_get_site_url()."mod/contest/download.php?params=$params";
      $trozos = explode(".", $one_file->title);
      $ext = strtolower(end($trozos));
      if (($ext == 'jpg') || ($ext == 'png') || ($ext == 'gif') || ($ext == 'tif') || ($ext == 'tiff') || ($ext =='jpeg'))
        $question_body .= "<p align=\"center\"><a href=\"".$url_file."\">"."<img src=\"" . $url_file . "\" width=\"600px\">"."</a></p>";
      else
        $question_body .= "<p><a href=\"".$url_file."\">"."<img src=\"" . elgg_get_site_url(). $icon . "\">".$one_file->title."</a></p>";
	 }
      }
      $question_body .= "</div>";
      break;
}

if (strcmp($question_body,"")!=0){
   $contest_question_body_label = elgg_echo('contest:contest');
   $form_body .= "<div class=\"contentWrapper\">";
   $form_body .= "<div class=\"contest_frame_blue\">";
   $form_body .= "<p align=\"left\"><a onclick=\"contest_show_question_body();\" style=\"cursor:hand;\">$contest_question_body_label</a></p>";
   $form_body .= "<div id=\"questionbodyDiv\" style=\"display:block;\">";
   $form_body .= $question_body;
   $form_body .= "</div>";
   $form_body .= "</div>";
   $form_body .= "</div>";
}

//Responses

$form_body .= "<div class=\"contentWrapper\">";
$form_body .= "<div class=\"contest_frame_green\">";

$i=0;
$responsesarray=array();
$membersarray=array();
$my_previous_responses = false;
$my_response_votes_count_array = array();
$my_votes_count=0;

$options = array('relationship' => 'contest_answer', 'relationship_guid' => $contestpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'contest_answer', 'limit' => 0);
$user_responses_temp=elgg_get_entities_from_relationship($options);


if ($user_responses_temp){
   $num_user_responses = 0;
   foreach($user_responses_temp as $one_response){
      $user_responses[$num_user_responses]=$one_response;
      $num_user_responses=$num_user_responses+1;
   }
   if ($order_by_votes){
      if ($num_user_responses>1) {
         $last_index = $num_user_responses-1;
         sort_responses_by_votes($user_responses,0,$last_index);
      }
   }
}

if (!empty($user_responses)){
   foreach ($user_responses as $one_user_response) {
      $owner_user_response= $one_user_response->getOwnerEntity();
      $owner_user_response_guid = $owner_user_response->getGUID();

      $owner_user_response_operator = false;

      if ($contest->subgroups){
         $subgroups = elgg_get_entities_from_relationship(array('type_subtype_pairs' => array('group' => 'lbr_subgroup'),'container_guids' => $container_guid,'relationship' => 'member','inverse_relationship' => false,'relationship_guid' => $owner_user_response_guid));
	 if ($subgroups) {
            $member_guid=$subgroups[0]->getGUID();
	 } else {
	    $member_guid = $owner_user_response_guid;
	 }
      } else {
         $member_guid = $owner_user_response_guid;
      }
      $member = get_entity($member_guid);

      $all_response_votes = $one_user_response->getAnnotations(array('name'=>'vote'),99999,0,'desc');
      $my_response_votes_count = 0;
      foreach ($all_response_votes as $item) {
         if ($item->value == $user_guid){
            $my_response_votes_count += 1;
         }
      }

      $my_votes_count += $my_response_votes_count;

      if (!$my_previous_responses) {
         if ($member_guid == $my_guid)
	    $my_previous_responses = true;
      }

      if (($member_guid==$my_guid)||($operator)||($contest->$responses_visibility)||($voting_opened)||($now>$contest->voting_close_time)) {
         $responsesarray[$i] = $one_user_response->getGUID();
         $membersarray[$i] = $member_guid;
         $my_response_votes_count_array[$i] = $my_response_votes_count;

	 $i=$i+1;
      }
   }
}

$count = $i;


if (($answering_opened)&&((!$my_previous_responses)||($contest->several_user_responses))) {
   if ((!$contest->subgroups)||($my_subgroups)) {
      //Answer
      $url_answer=elgg_add_action_tokens_to_url(elgg_get_site_url() . "contest/answer/" . $contestpost . "/" . "-1");
      $url_text_answer = elgg_echo('contest:to_contest');
      $link_answer= elgg_view('input/button', array('name' => 'to_contest',
         'class' => 'elgg-button-special', 'value' => $url_text_answer));
      $link_answer="<a href=\"{$url_answer}\">{$link_answer}</a>";

      $form_body .= $link_answer;
      $form_body .= "<br><br>";
   }
}

if ($count>0) {
   $form_body .= "<b>" . elgg_echo('contest:responses') . " (" . $count . ")" . "</b><br>";

   if (!$order_by_votes){
      $link_order_by_votes_name = elgg_echo("contest:order_by_votes");
      $link_order_by_votes_url = elgg_get_site_url() . "contest/view/" . $contestpost . "/" . "votes";
      $link_order_by_votes = "<a href=\"{$link_order_by_votes_url}\">{$link_order_by_votes_name}</a>";	
      $form_body .= $link_order_by_votes;
   } else {
      $link_order_by_time_name = elgg_echo("contest:order_by_time");
      $link_order_by_time_url = elgg_get_site_url() . "contest/view/" . $contestpost . "/" . "time";
      $link_order_by_time = "<a href=\"{$link_order_by_time_url}\">{$link_order_by_time_name}</a>";	
      $form_body .= $link_order_by_time;
   }

   $i=0;
   //===================================================================
   $option_values_votes_for_response_by_user =array();
   for ($j=0; $j <= $number_votes_for_response_by_user; $j++){
      array_push($option_values_votes_for_response_by_user, (string)$j);
   }
   //===================================================================
   foreach ($responsesarray as $one_response_guid){
      if (($i>=$offset)&&($i<$this_limit)){
         $member_guid = $membersarray[$i];
	 $member = get_entity($member_guid);
    if(!$contest->responses_visibility && $answering_opened && !$operator && $member_guid!=$my_guid){
       $i=$i+1;
       continue;
    }
	 if ($member_guid==$my_guid) {
	    if ($answering_opened) {
	       $url=elgg_add_action_tokens_to_url(elgg_get_site_url() . "contest/answer/" . $contestpost . "/" . $one_response_guid . "/" . $order_by);
	    } else {
	       $url=elgg_get_site_url() . "contest/show_answer/" . $contestpost . "/" . $one_response_guid . "/" . $order_by;
	    }
	    $url_text = elgg_echo('contest:response') . " " . elgg_echo('contest:of') . " " . $member->name;
	 } else {
	    $url=elgg_get_site_url() . "contest/show_answer/" . $contestpost . "/" . $one_response_guid . "/" . $order_by;
	    if (($contest->responses_authors_visibility)||($operator)) {
	       $url_text = elgg_echo('contest:response') . " " . elgg_echo('contest:of') . " " . $member->name;
	    } else {
	       $url_text = elgg_echo('contest:response');
	    }
	 }
         $link="<a href=\"{$url}\">{$url_text}</a>";
         $icon = elgg_view_entity_icon($member,'small');

	 $one_response = get_entity($one_response_guid);

         //$time_created = $one_response->time_created;
         //$time_updated = $one_response->answer_time;
         //$friendly_date_created = date('d/m/Y',$time_created);
         //$friendly_time_created = date('G:i',$time_created);
         //$one_response_created = elgg_echo('contest:response_created') . " " . $friendly_date_created . " " . elgg_echo('contest:at') . " " . $friendly_time_created;
         $info_left = $link . "<br>" .elgg_echo("contest:comments_label")." (". $one_response->countComments() .") ";
         //. elgg_view("likes/button",array('entity' => $one_response)) . " " . elgg_view("likes/count",array('entity' => $one_response));

	 $remaining_votes = $number_total_votes_by_user-$my_votes_count;
	 $remaining_votes_for_response = $number_votes_for_response_by_user-$my_response_votes_count_array[$i];
	 $number_previous_votes = $one_response->countAnnotations('vote');
	 $name_votes = "votes_" . $one_response_guid;
	 $votes_label = elgg_echo("contest:votes");
	 $your_votes_label = elgg_echo("contest:your_votes");
	 $your_votes_input = "<input type=\"text\" name=\"" . $name_votes . "\" value=\"" . $my_response_votes_count_array[$i] . "\"  style=\"width: 80px\"/>";

    //=============================================================================================================================================================

	 if ((($remaining_votes > 0) && ($remaining_votes_for_response > 0)) || ($my_response_votes_count_array[$i] > 0)) {
       $link_vote = elgg_view('input/dropdown',array('name'=>'number_votes_for_response_by_user'. $one_response_guid,'options_values'=>$option_values_votes_for_response_by_user, 'value'=> $my_response_votes_count_array[$i]));
	    $link_vote .= "&nbsp<a onclick=\"javascript:contest_vote(".$contestpost.",".$user_guid.",".$one_response_guid.",".
         $my_response_votes_count_array[$i].",".$remaining_votes.");return true;\">".elgg_echo("contest:vote")."</a>";
    } else {
	    $link_vote = "";
	 }
	 $info = "<div class=\"contest_options\">";
   $info .= "<b><span style=\"color:#FF0000\">" . $votes_label . ": " . $number_previous_votes . "</span></b>&nbsp;&nbsp;&nbsp;" ;
	 if ($voting_opened && $member_guid != $my_guid)
       $info .= $link_vote;

    //=============================================================================================================================================================


   if (strcmp($contest->response_type,"simple")==0){
     $abstract = substr(strip_tags($one_response->content), 0, 300);
    //Si el texto es mayor que la longitud se agrega puntos suspensivos
    if (strlen(strip_tags($one_response->content)) > 300)
        $abstract .= ' ...';
      $info_left .= "<blockquote>" . $abstract . "</blockquote>";
   }
	 $info .= "</div>";

	 $info .= $info_left;

         $form_body .= elgg_view_image_block($icon,$info);
      }
      $i=$i+1;
   }

   $form_body .= elgg_view("navigation/pagination",array('count'=>$count,'offset'=>$offset,'limit'=>$limit));


   $form_body .= elgg_echo('contest:your_remaining_votes') . ": " . $remaining_votes;

} else {
   $form_body .= "<b>" . elgg_echo('contest:responses') . "</b><br>";
   $form_body .= elgg_echo('contest:not_responses');
}

$form_body .= "</div>";
$form_body .= "</div>";

echo elgg_echo($form_body);

?>

<script type="text/javascript">

   function contest_show_general_comments(){
      var commentsDiv = document.getElementById('commentsDiv');
      if (commentsDiv.style.display == 'none'){
         commentsDiv.style.display = 'block';
      } else {
         commentsDiv.style.display = 'none';
      }
   }

   function contest_show_question_body(){
      var questionbodyDiv = document.getElementById('questionbodyDiv');
      if (questionbodyDiv.style.display == 'none'){
         questionbodyDiv.style.display = 'block';
      } else {
         questionbodyDiv.style.display = 'none';
      }
   }

   function contest_vote(contestpost,user_guid,response_guid,my_votes_before,remaining_votes){
      var url = "<?php echo elgg_get_site_url(); ?>mod/contest/actions/contest/vote.php";
      var this_url = location.href;

      var name='number_votes_for_response_by_user';
      var name= name.concat(response_guid);
      var num_votes = document.getElementsByName(name).item(0).value;

      var check = remaining_votes + my_votes_before;
      if (check < num_votes){
        elgg.register_error(elgg.echo("contest:not:available:votes"));
      } else {
         var postdata = {contestpost: contestpost, user_guid: user_guid, response_guid: response_guid, num_votes: num_votes, my_votes_before: my_votes_before};
         $.post(url, postdata);
        elgg.system_message(elgg.echo("contest:voted"));
         location.assign(this_url);
     }
   }

</script>
