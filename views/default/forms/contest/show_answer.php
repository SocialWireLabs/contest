<div class="contentWrapper">

<?php

elgg_load_library('contest');

if (isset($vars['entity'])) {
   $contestpost=$vars['entity']->getGUID();
   $contest=$vars['entity'];

   $answerpost=$vars['answerpost'];

   $answer = get_entity($answerpost);

   $user_response = get_entity($answerpost);


//////////////////////////////////////////////////////////////////
$order_by = $vars['order_by'];
$voting_opened = false;
$answering_opened = false;
$now = time();
if (((($now>=$contest->activate_time)&&(strcmp($contest->option_opened_voting_value,'contest_opened_voting_while_answering')==0))||(($now>$contest->close_time)&&(strcmp($contest->option_opened_voting_value,'contest_opened_voting_while_answering')!=0)))&&($now<$contest->close_time_voting)){
   $voting_opened=true;
}
if (strcmp($contest->option_activate_value,'contest_activate_date')==0){
   if (($now>=$contest->activate_time)&&($now<$contest->close_time)){
      $answering_opened=true;
   }
} else {
   if ($now<$contest->close_time) {
      $answering_opened=true;
   }
}

$container_guid = $contest->container_guid;
$container=get_entity($container_guid);

$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);

$operator = false;
if ($container instanceof ElggGroup) {
   $group_guid=$contest->container_guid;
   $group = get_entity($group_guid);
   $group_owner_guid = $group->owner_guid;

   if (($owner_guid==$user_guid)||($group_owner_guid==$user_guid)||(check_entity_relationship($user_guid,'group_admin',$group_guid))){
      $operator=true;
   }
}

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

$owner_user_response= $answer->getOwnerEntity();
$owner_user_response_guid = $owner_user_response->getGUID();

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

$options = array('relationship' => 'contest_answer', 'relationship_guid' => $contestpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'contest_answer', 'limit' => 0);
$user_responses=elgg_get_entities_from_relationship($options);

$my_votes_count = 0;

if (!empty($user_responses)){
   foreach ($user_responses as $one_user_response) {

      $all_response_votes = $one_user_response->getAnnotations(array('name'=>'vote'),99999,0,'desc');
      $my_response_votes_count = 0;
      foreach ($all_response_votes as $item) {
         if ($item->value == $user_guid){
            $my_response_votes_count += 1;
         }
      }

      $my_votes_count += $my_response_votes_count;
   }
}

$number_total_votes_by_user = $contest->number_total_votes_by_user;
$number_votes_for_response_by_user = $contest->number_votes_for_response_by_user;
$remaining_votes = $number_total_votes_by_user-$my_votes_count;
$remaining_votes_for_response = $number_votes_for_response_by_user-$my_response_votes_count;
$number_previous_votes = $answer->countAnnotations('vote');
$option_values_votes_for_response_by_user =array();
   for ($j=0; $j <= $number_votes_for_response_by_user; $j++){
      array_push($option_values_votes_for_response_by_user, (string)$j);
}
$name_votes = "votes_" . $answerpost;
$votes_label = elgg_echo("contest:votes");

$your_votes_input = "<input type=\"text\" name=\"" . $name_votes . "\" value=\"" . $my_response_votes_count . "\"  style=\"width: 80px\"/>";


if ((($remaining_votes > 0) && ($remaining_votes_for_response > 0)) || ($my_response_votes_count > 0)) {
   $link_vote = elgg_view('input/dropdown',array('name'=>'number_votes_for_response_by_user'. $answerpost,'options_values'=>$option_values_votes_for_response_by_user, 'value'=> $my_response_votes_count));
   $link_vote .= "&nbsp<a onclick=\"javascript:contest_vote(".$contestpost.",".$user_guid.",".$answerpost.",".$my_response_votes_count.",".$remaining_votes.");return true;\">".elgg_echo("contest:vote")."</a>";
} else {
   $link_vote = "";
}

$info_votes = "<b><span style=\"color:#FF0000\">" . $votes_label . ": " . $number_previous_votes . "</span></b>&nbsp;&nbsp;&nbsp;" ;
if ($voting_opened && $member_guid != $my_guid)
   $info_votes .= $link_vote;

	 
//////////////////////////////////////////////////////////////////

   $return_button_text = elgg_echo('contest:return');
   $return_button_link = elgg_get_site_url() . 'contest/view/'. $contestpost . '/' . $order_by;
   $return_button = elgg_view('input/button', array('name' => 'return', 'class' => 'elgg-button-cancel', 'value' => $return_button_text));
   $return_button = "<a href=" . $return_button_link . ">" . $return_button. "</a>";



if (strcmp($order_by,'votes')==0) {
   $order_by_votes = true;
} else {
   $order_by_votes = false;
} 

if ($user_responses){
      $num_user_responses = 0;
      foreach($user_responses as $one_response){
         $owner_one_response = $one_response->getOwnerEntity();
         $owner_one_response_guid = $owner_one_response->getGUID();
         if ($contest->subgroups){
            $subgroups = elgg_get_entities_from_relationship(array('type_subtype_pairs' => array('group' => 'lbr_subgroup'),'container_guids' => $container_guid,'relationship' => 'member','inverse_relationship' => false,'relationship_guid' => $owner_one_response_guid));
	    if ($subgroups) {
               $member_one_response_guid = $subgroups[0]->getGUID();
	    } else {
	       $member_one_response_guid = $owner_one_response_guid;
	    }
         } else {
            $member_one_response_guid = $owner_one_response_guid;
         }
         $member_one_response = get_entity($member_one_response_guid);
	 if (($member_one_response_guid==$my_guid)||($operator)||($contest->responses_visibility)||($voting_opened)||($now>$contest->voting_close_time)){
            $user_responses[$num_user_responses]=$one_response;
            $num_user_responses=$num_user_responses+1;
	 }
      }
      if (($num_user_responses>1)&&($order_by_votes)) {
         $last_index = $num_user_responses-1;
         sort_responses_by_votes($user_responses,0,$last_index);
      }
}
$next_answer_guid="";
$previous_answer_guid="";

$found=false;
if (!empty($user_responses)){
   foreach ($user_responses as $one_user_response) {
      $oneanswerpost=$one_user_response->getGUID();
      if ($found) {
         $next_answer_guid=$oneanswerpost;
         break;  
      }
      if ($oneanswerpost==$answerpost){
         $found=true;             
      } else {
         $previous_answer_guid=$oneanswerpost;
      }
   }
}

if (!empty($previous_answer_guid)){
      $previous_answer = get_entity($previous_answer_guid);
      $owner_previous_answer = $previous_answer->getOwnerEntity();
      $owner_previous_answer_guid = $owner_previous_answer->getGUID();

      if ($contest->subgroups){
         $subgroups = elgg_get_entities_from_relationship(array('type_subtype_pairs' => array('group' => 'lbr_subgroup'),'container_guids' => $container_guid,'relationship' => 'member','inverse_relationship' => false,'relationship_guid' => $owner_previous_answer_guid));
	 if ($subgroups) {
            $member_previous_answer_guid = $subgroups[0]->getGUID();
	 } else {
	    $member_previous_answer_guid = $owner_previous_answer_guid;
	 }
      } else {
         $member_previous_answer_guid = $owner_previous_answer_guid;
      }
      $member_previous_answer = get_entity($member_previous_answer_guid);

      if (($member_previous_answer_guid!=$my_guid)||(!$answering_opened)){
         $url_prev = elgg_add_action_tokens_to_url(elgg_get_site_url() . "contest/show_answer/" . $contestpost . "/" . $previous_answer_guid . "/" . $order_by);
      } else {
         $url_prev = elgg_add_action_tokens_to_url(elgg_get_site_url() . "contest/answer/" . $contestpost . "/" . $previous_answer_guid . "/" . $order_by);
      }
      $url_text_prev = elgg_echo('contest:show_previous');
      $link_prev = "<br><a style=\"float:left\" href={$url_prev}>{$url_text_prev}</a>";
   }
if (!empty($next_answer_guid)){
      $next_answer = get_entity($next_answer_guid);
      $owner_next_answer = $next_answer->getOwnerEntity();
      $owner_next_answer_guid = $owner_next_answer->getGUID();

      if ($contest->subgroups){
         $subgroups = elgg_get_entities_from_relationship(array('type_subtype_pairs' => array('group' => 'lbr_subgroup'),'container_guids' => $container_guid,'relationship' => 'member','inverse_relationship' => false,'relationship_guid' => $owner_previous_answer_guid));
	 if ($subgroups) {
            $member_next_answer_guid = $subgroups[0]->getGUID();
	 } else {
	    $member_next_answer_guid = $owner_next_answer_guid;
	 }
      } else {
         $member_next_answer_guid = $owner_next_answer_guid;
      }
      $member_next_answer = get_entity($member_next_answer_guid);

      if (($member_next_answer_guid!=$my_guid)||(!$answering_opened)){
         $url_next = elgg_add_action_tokens_to_url(elgg_get_site_url() . "contest/show_answer/" . $contestpost . "/" . $next_answer_guid . "/" . $order_by);
      } else {
         $url_next = elgg_add_action_tokens_to_url(elgg_get_site_url() . "contest/answer/" . $contestpost . "/" . $next_answer_guid . "/" . $order_by);
      }
      $url_text_next = elgg_echo('contest:show_next');
      $link_next = "<a style=\"float:right\" href={$url_next}>{$url_text_next}</a>";
}

///////////////////////////////////////////////////////////////////

   if (!empty($user_response)) {
      ////////////////////////////////////////////////////////////

      //Response
      $response_type=$contest->response_type;
      if (strcmp($response_type,"urls_files")==0){
         $response_files = elgg_get_entities_from_relationship(array('relationship' => 'response_file_link','relationship_guid' => $user_response->getGUID(),'inverse_relationship' => false,'type' => 'object','subtype' => 'contest_response_file','limit'=>0));
         $response_file_guids="";
	 if ((count($response_files)>0)&&(strcmp($response_files[0]->title,"")!=0)){
            foreach($response_files as $file){
               if (strcmp($response_file_guids,"")==0)
                  $response_file_guids .= $file->getGUID();
               else
                  $response_file_guids .= "," . $file->getGUID();
            }
	 }
      }

      $this_response= explode(Chr(25),$user_response->content);
      $this_response = array_map('trim',$this_response);

      $response_text = $this_response[0];

      if (strcmp($response_text, Chr(2))==0)
         $response_text = "";

      if (strcmp($response_type,"simple")==0){
         $response_html=$this_response[1];
	 if (strcmp($response_html, Chr(2))==0)
	    $response_html="";
      }

      if (strcmp($response_type,"urls_files")==0){
         $this_response=$this_response[1];
	 if (strcmp($this_response, Chr(2))==0){
	    $response_urls="";
	 } else {
	    $this_response_urls = explode(Chr(26),$this_response);
            $this_response_urls = array_map('trim',$this_response_urls);
	    $response_urls = $this_response_urls;
	 }
	 $response_file_guids_array=explode(",",$response_file_guids);
      }

      $form_body = "";

      //////////////////////////////////////////////////////
      //Previous information

      $form_body .= "<div class=\"contest_frame\">";

      //Response information
      $time_created = $user_response->time_created;
      $time_updated = $user_response->answer_time;
      $friendly_date_created = date('d/m/Y',$time_created);
      $friendly_time_created = date('G:i',$time_created);
      $form_body .= elgg_echo('contest:response_created') . " " . $friendly_date_created . " " . elgg_echo('contest:at') . " " . $friendly_time_created;
      if (($time_updated)&&($time_created != $time_updated)) {
         $friendly_date_updated = date('d/m/Y',$time_updated);
         $friendly_time_updated = date('G:i',$time_updated);
         $form_body .= "<br>";
         $form_body .= elgg_echo('contest:response_updated') . " " . $friendly_date_updated . " " . elgg_echo('contest:at') . " " . $friendly_time_updated;
      }
      $form_body .= "<br>";
      $form_body .= "</div>";
      $form_body .= "<br>";
      //Comments
      $num_comments =  $user_response->countComments();
      if ($num_comments>0)
         $comments_label = elgg_echo('contest:comments_label') . " (" . $num_comments . ")";
      else
         $comments_label = elgg_echo('contest:comments_label');
      $form_body .= "<p align=\"left\"><a onclick=\"contest_show_comments();\" style=\"cursor:hand;\">$comments_label</a></p>";
      $form_body .= "<div id=\"comments_responseDiv\" style=\"display:none;\">";
      $form_body .= elgg_view_comments($user_response);
      $form_body .= "</div>";

      $form_body .= "<br>";

      //////////////////////////////////////////////////////
      //Body

      switch($response_type){
         case 'simple':
	    $form_body .= elgg_view("forms/contest/show_answer_body",array('contest'=>$contest,'question_body'=>$question_body,'response_type'=>$response_type,'response_text'=>$response_text,'response_html'=>$response_html));
            break;
         case 'urls_files':
            $form_body .= elgg_view("forms/contest/show_answer_body",array('contest'=>$contest,'question_body'=>$question_body,'response_type'=>$response_type,'response_text'=>$response_text,'response_urls'=>$response_urls,'response_file_guids_array'=>$response_file_guids_array));
            break;
      }

      $form_body .= $info_votes;

      $form_body .= "<br><br>".$return_button;
      $form_body .= "<br>".$link_prev.$link_next;

      echo elgg_echo($form_body);

   } else {
      $form_body .= "<p>" . elgg_echo('contest:not_response') . "</p>";
      echo elgg_echo($form_body);
   }
}

?>
</div>

<script type="text/javascript">
   function contest_show_general_comments(){
      var commentsDiv = document.getElementById('commentsDiv');
      if (commentsDiv.style.display == 'none'){
         commentsDiv.style.display = 'block';
      } else {
         commentsDiv.style.display = 'none';
      }
   }
   function contest_show_comments(){
      var comments_responseDiv = document.getElementById('comments_responseDiv');
      if (comments_responseDiv.style.display == 'none'){
         comments_responseDiv.style.display = 'block';
      } else {
         comments_responseDiv.style.display = 'none';
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
