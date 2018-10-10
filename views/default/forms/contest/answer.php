<div class="contentWrapper">

<?php

$now=time();

if (isset($vars['entity'])){

   $contestpost=$vars['entity']->getGUID();
   $contest=$vars['entity'];
   $answerpost=$vars['answerpost'];
   
   $action = "contest/answer";

   $container_guid  = $contest->container_guid;
   $container = get_entity($container_guid);

   if (strcmp($answerpost,"-1")!=0) {
      $user_response = get_entity($answerpost);
      $answer = get_entity($answerpost);
   } else {
      $user_response = "";
   }

   $response_type=$contest->response_type;

////////////////////////////////////////////////////////////////////////

$order_by = $vars['order_by'];
if (strcmp($order_by,'votes')==0) {
   $order_by_votes = true;
} else {
   $order_by_votes = false;
} 

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

  if (strcmp($answerpost,"-1")!=0) {
      $number_previous_votes = $answer->countAnnotations('vote');
   } else {
      $number_previous_votes = 0;
   }


//$number_previous_votes = $answer->countAnnotations('vote');
$votes_label = elgg_echo("contest:votes");
$info_votes = "<b><span style=\"color:#FF0000\">" . $votes_label . ": " . $number_previous_votes . "</span></b>&nbsp;&nbsp;&nbsp;" ;


//////////////////////////////////////////////////////////////////////////////////

   $return_button_text = elgg_echo('contest:return');
   $return_button_link = elgg_get_site_url() . 'contest/view/'. $contestpost . '/' . $order_by;
   $return_button = elgg_view('input/button', array('name' => 'return', 'class' => 'elgg-button-cancel', 'value' => $return_button_text));
   $return_button = "<a href=" . $return_button_link . ">" . $return_button. "</a>";


$options = array('relationship' => 'contest_answer', 'relationship_guid' => $contestpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'contest_answer', 'limit' => 0);
$user_responses=elgg_get_entities_from_relationship($options);
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
   //Question body

   $question_body="";
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
              $url_file = elgg_get_site_url()."mod/test/download.php?params=$params";
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

   if (!empty($user_response)) {
      $user_response_guid = $user_response->getGUID();

      $time_created = $user_response->time_created;
      $time_updated = $user_response->answer_time;

      $friendly_date_created = date('d/m/Y',$time_created);
      $friendly_time_created = date('G:i',$time_created);

      echo elgg_echo('contest:response_created') . " " . $friendly_date_created . " " . elgg_echo('contest:at') . " " . $friendly_time_created;

      if (($time_updated)&&($time_created != $time_updated)) {
         $friendly_date_updated = date('d/m/Y',$time_updated);
         $friendly_time_updated = date('G:i',$time_updated);

         echo "<br>";
         echo elgg_echo('contest:response_updated') . " " . $friendly_date_updated . " " . elgg_echo('contest:at') . " " . $friendly_time_updated;

      }
      echo "<br><br>";

      $num_votes_user_response = $user_response->countAnnotations('all_votes');

      //Delete answer
      if (($now>=$contest->activate_time)&&($now<$contest->close_time)&&($num_votes_user_response==0)){
         $url_delete_answer=elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/contest/delete_answer?contestpost=$contestpost&answerpost=$answerpost");
         $text_delete_answer=elgg_echo("delete");
         $confirm_delete_msg = elgg_echo('contest:delete_answer_confirm');
	 $img_template = '<img border="0" width="16" height="16" alt="%s" title="%s" src="'.elgg_get_config('wwwroot').'mod/contest/graphics/%s" />';
         $img_delete_answer = sprintf($img_template,$text_delete_answer,$text_delete_answer,"delete.gif");
         echo "<a  onclick=\"return confirm('$confirm_delete_msg')\" href=\"{$url_delete_answer}\">{$img_delete_answer}</a>";
      }

      //Comments
      $num_comments =  $user_response->countComments();
      if ($num_comments>0)
         $comments_label = elgg_echo('contest:comments_label') . " (" . $num_comments . ")";
      else
         $comments_label = elgg_echo('contest:comments_label');
      ?>
      <p align="left"><a onclick="contest_show_comments();" style="cursor:hand;"><?php echo $comments_label; ?></a></p>
      <div id="comments_responseDiv" style="display:none;">
         <?php echo elgg_view_comments($user_response);?>
      </div>
   <?php
   }

   ////////////////////////////////////////////////////////////////////////aaa
   //Form
   ?>

   <form action="<?php echo elgg_get_site_url()."action/".$action?>" name="answer_contest" enctype="multipart/form-data" method="post">

   <?php
   echo elgg_view('input/securitytoken');

   $this_response= explode(Chr(25),$user_response->content);
   $this_response = array_map('trim',$this_response);
   if (elgg_is_sticky_form('answer_contest')) {
      $this_response_text = elgg_get_sticky_value('answer_contest','response_text');
   } else {
      $this_response_text = $this_response[0];
      if (strcmp($this_response_text,Chr(2))==0)
         $this_response_text = "";
   }

   $response_text = elgg_view('input/text', array('name' => 'response_text','value' => $this_response_text));

   if (strcmp($response_type,"simple")==0){
      if (elgg_is_sticky_form('answer_contest')) {
         $this_response_html = elgg_get_sticky_value('answer_contest','response_html');
      } else {
         $this_response_html = $this_response[1];
         if (strcmp($this_response_html, Chr(2))==0)
            $this_response_html = "";
      }
      $response_html = elgg_view('input/longtext', array('name' => 'response_html','value' => $this_response_html));
   }

   if (strcmp($response_type,"urls_files")==0){
      $name_response="response_urls"."[]";
      $name_response_names="response_urls_names"."[]";
      if (elgg_is_sticky_form('answer_contest')) {
         $this_response_urls_names = elgg_get_sticky_value('answer_contest','response_urls_names');
         $this_response_urls = elgg_get_sticky_value('answer_contest','response_urls');
         $i=0;
         $this_response_comp_urls = array();
         foreach ($this_response_urls as $url){
            $this_response_comp_urls[$i] = $this_response_urls_names[$i] . Chr(24) . $this_response_urls[$i];
            $i=$i+1;
         }
      } else {
         $this_response_comp_urls = $this_response[1];
         if (strcmp($this_response_comp_urls, Chr(2))!=0) {
            $this_response_comp_urls = explode(Chr(26),$this_response_comp_urls);
            $this_response_comp_urls = array_map('trim',$this_response_comp_urls);
         } else {
            $this_response_comp_urls = "";
         }
      }
      $response_urls = "";
      if ((count($this_response_comp_urls)>0)&&(strcmp($this_response_comp_urls[0],"")!=0)) {
         $j=0;
         foreach ($this_response_comp_urls as $url) {
            $response_urls .= "<p class=\"clone_this_response_urls_" . "\">";
	    $comp_url = explode(Chr(24),$url);
            $comp_url = array_map('trim',$comp_url);
            $url_name = $comp_url[0];
            $url_value = $comp_url[1];
            $response_urls .= elgg_echo("contest:response_url_label");
            $response_urls .= elgg_view("input/text", array('name' => $name_response,'value' => $url_value));
	    if ($j>0){
	       $response_urls .= "<!-- remove url --><a class=\"remove\" href=\"#\" onclick=\"$(this).parent().slideUp(function(){ $(this).remove() }); return false\">" . elgg_echo("delete") . "</a>";
	    }
	    $response_urls .= "<br></p>";
	    $j=$j+1;
         }
      } else {
         $response_urls .= "<p class=\"clone_this_response_urls_" . "\">";
         $comp_url = explode(Chr(24),$this_response_comp_urls);
         $comp_url = array_map('trim',$comp_url);
         $url_name = $comp_url[0];
         $url_value = $comp_url[1];
         $response_urls .= elgg_echo("contest:response_url_label");
         $response_urls .= elgg_view("input/text", array('name' => $name_response,'value' => $url_value));
         $response_urls .= "</p>";
      }
      $response_urls .= "<!-- add link to add more urls which triggers a jquery clone function --><a href=\"#\" class=\"add\" rel=\".clone_this_response_urls_" . "\">" . elgg_echo("contest:add_url") . "</a>";
      $response_urls .= "<br /><br /></p>";

      if (!empty($user_response)){
         $response_files = elgg_get_entities_from_relationship(array('relationship' => 'response_file_link','relationship_guid' => $user_response->getGUID(),'inverse_relationship' => false,'type' => 'object','subtype' => 'contest_response_file','limit'=>0));
      } else {
         $response_files = "";
      }
      $name_response="upload_response_file"."[]";
      $response_file = elgg_view("input/file",array( 'name' => $name_response, 'class' => 'multi'));
   }

   elgg_clear_sticky_form('answer_contest');

   ////////////////////////////////////////////////////////////////////////
   //Body

   switch($response_type){
      case 'simple':
         echo elgg_view("forms/contest/answer_body",array('contest'=>$contest,'question_body'=>$question_body,'response_type'=>$response_type,'response_text'=>$response_text,'response_html'=>$response_html));
         break;
      case 'urls_files':
         echo elgg_view("forms/contest/answer_body",array('contest'=>$contest,'question_body'=>$question_body,'response_type'=>$response_type,'response_text'=>$response_text,'response_urls'=>$response_urls,'response_file'=>$response_file,'response_files'=>$response_files));
         break;
   }

   ////////////////////////////////////////////////////////////////////
   //Submit

   $contest_answer = elgg_echo('contest:answer');
   $submit_input_answer = elgg_view('input/submit', array('name' => 'submit', 'value' => $contest_answer));
   $entity_hidden = elgg_view('input/hidden', array('name' => 'contestpost', 'value' => $contestpost));
   $entity_hidden .= elgg_view('input/hidden', array('name' => 'answerpost', 'value'=> $answerpost));

   ?>
   <p><?php echo  $info_votes . "<br><br>" . $submit_input_answer . $entity_hidden . " ".$return_button."<br>".$link_prev.$link_next;
   ?></p><br>


   <!-- add the add_response/delete_response functionality  -->
   <script type="text/javascript">
   // remove function for the jquery clone plugin
   $(function(){
      var removeLink = '<a class="remove" href="#" onclick="$(this).parent().slideUp(function(){ $(this).remove() }); return false"><?php echo elgg_echo("delete");?></a>';
      $('a.add').relCopy({ append: removeLink});
   });
   </script>

   </form>

<?php
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

</script>

<script type="text/javascript" src="<?php echo elgg_get_site_url(); ?>mod/contest/lib/jquery.MultiFile.js"></script><!-- multi file jquery plugin -->
<script type="text/javascript" src="<?php echo elgg_get_site_url(); ?>mod/contest/lib/reCopy.js"></script><!-- copy field jquery plugin -->
<script type="text/javascript" src="<?php echo elgg_get_site_url(); ?>mod/contest/lib/js_functions.js"></script>
