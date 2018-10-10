<div class="contentWrapper">

<?php

$contestpost=$vars['entity']->getGUID();
$contest = get_entity($contestpost);
$action = "contest/edit";
$container_guid = $contest->container_guid;
$container = get_entity($container_guid);

$now=time();
$opened=false;
$voting_opened=false;

if (strcmp($contest->option_activate_value,'contest_activate_date')==0){
   if (($now>=$contest->activate_time)&&($now<$contest->close_time)){
      $opened=true;
   }
} else {
   if ($now<$contest->close_time) {
      $opened=true;
   }
}
if (((($now>=$contest->activate_time)&&(strcmp($contest->option_opened_voting_value,'contest_opened_voting_while_answering')==0))||(($now>$contest->close_time)&&(strcmp($contest->option_opened_voting_value,'contest_opened_voting_while_answering')!=0)))&&($now<$contest->close_time_voting)){
   $voting_opened=true;
}

$num_votes = $contest->countAnnotations('all_votes');

if (($opened)||($voting_opened)) {

   $contest_opened = elgg_echo('contest:answering_opened_or_voting_opened');
   $close_contest = elgg_echo('contest:close');
   $form_body = "";
   $form_body .= "<p>" . $contest_opened . "</p>";
   $entity_hidden = elgg_view('input/hidden', array('name' => 'contestpost', 'value' => $contestpost));
   $entity_hidden .= elgg_view('input/hidden', array('name' => 'close_contest', 'value' => "yes"));
   $submit_input = elgg_view('input/submit', array('name' => 'submit', 'value' => $close_contest));
   $form_body .= "<p>" . $submit_input . $entity_hidden . "</p>";
   $vars_url = elgg_get_site_url();
   echo elgg_view('input/form', array('action' => "{$vars_url}action/$action", 'body' => $form_body));

} else {

   $user_guid = elgg_get_logged_in_user_guid();
   $user = get_entity($user_guid);
   $count_responses=$contest->countAnnotations('all_responses');

   if (!elgg_is_sticky_form('edit_contest')) {
      $title = $contest->title;
      $question_html = $contest->question_html;
      $question_type=$contest->question_type;
      switch($question_type){
         case 'urls_files':
            $question_comp_urls = explode(Chr(26),$contest->question_urls);
            $question_comp_urls = array_map('trim',$question_comp_urls);
            break;
      }
      $response_type=$contest->response_type;
      $several_user_responses = $contest->several_user_responses;
      $responses_visibility = $contest->responses_visibility;
      $responses_authors_visibility = $contest->responses_authors_visibility;
      $opendate = $contest->form_activate_date;
      $opentime = $contest->form_activate_time;
      $closedate = $contest->form_close_date;
      $closetime = $contest->form_close_time;
      $option_activate_value = $contest->option_activate_value;
      $option_opened_voting_value = $contest->option_opened_voting_value;
      $closedate_voting = $contest->form_close_date_voting;
      $closetime_voting = $contest->form_close_time_voting;
      $number_votes_for_response_by_user = $contest->number_votes_for_response_by_user;
      $number_total_votes_by_user = $contest->number_total_votes_by_user;
      if ($container instanceof ElggGroup) {
         $subgroups = $contest->subgroups;
	 $contest_with_gamepoints = $contest->contest_with_gamepoints;
	 $total_gamepoints = $contest->total_gamepoints;
	 $option_type_grading_value = $contest->option_type_grading_value;
         $number_winners_type_grading_percentage = $contest->number_winners_type_grading_percentage;
         $gamepoints_type_grading_prearranged = $contest->gamepoints_type_grading_prearranged;
      }
      $tags = $contest->tags;
      $access_id = $contest->access_id;

   } else {
      $title = elgg_get_sticky_value('edit_contest','title');
      $question_html = elgg_get_sticky_value('edit_contest','question_html');
      $opendate = elgg_get_sticky_value('edit_contest','opendate');
      $closedate = elgg_get_sticky_value('edit_contest','closedate');
      $opentime = elgg_get_sticky_value('edit_contest','opentime');
      $closetime = elgg_get_sticky_value('edit_contest','closetime');
      $option_activate_value = elgg_get_sticky_value('edit_contest','option_activate_value');
      $option_opened_voting_value = elgg_get_sticky_value('edit_contest','option_opened_voting_value');
      $closedate_voting = elgg_get_sticky_value('edit_contest','closedate_voting');
      $closetime_voting = elgg_get_sticky_value('edit_contest','closetime_voting');
      $tags = elgg_get_sticky_value('edit_contest','contesttags');
      $access_id = elgg_get_sticky_value('edit_contest','access_id');
      $question_type=$contest->question_type;
      if ($count_responses==0){
         switch($question_type){
            case 'urls_files':
               $question_urls_names = elgg_get_sticky_value('edit_contest','question_urls_names');
               $question_urls = elgg_get_sticky_value('edit_contest','question_urls');
               $i=0;
               $question_comp_urls = array();
               foreach ($question_urls as $url){
                  $question_comp_urls[$i] = $question_urls_names[$i] . Chr(24) . $question_urls[$i];
                  $i=$i+1;
               }
	       break;
         }
         $response_type = elgg_get_sticky_value('edit_contest','response_type');
	 $several_user_responses = elgg_get_sticky_value('edit_contest','several_user_responses');
         $responses_visibility = elgg_get_sticky_value('edit_contest','responses_visibility');
         $responses_authors_visibility = elgg_get_sticky_value('edit_contest','responses_authors_visibility');
	 if ($container instanceof ElggGroup)
	    $subgroups = elgg_get_sticky_value('edit_contest','subgroups');
      } else {
         switch($question_type){
            case 'urls_files':
               $question_comp_urls = explode(Chr(26),$contest->question_urls);
               $question_comp_urls = array_map('trim',$question_comp_urls);
               break;
         }
	 $response_type = $contest->response_type;
	 $several_user_responses = $contest->several_user_responses;
         $responses_visibility = $contest->responses_visibiliby;
         $responses_authors_visibility = $contest->responses_authors_visibility;
	 if ($container instanceof ElggGroup)
            $subgroups = $contest->subgroups;
      }
      if ($container instanceof ElggGroup) {
         $contest_with_gamepoints = elgg_get_sticky_value('edit_contest','contest_with_gamepoints');
         $total_gamepoints = elgg_get_sticky_value('edit_contest','total_gamepoints');
         $option_type_grading_value = elgg_get_sticky_value('edit_contest','option_type_grading_value');
         $number_winners_type_grading_percentage = elgg_get_sticky_value('edit_contest','number_winners_type_grading_percentage');
         $gamepoints_type_grading_prearranged = elgg_get_sticky_value('edit_contest','gamepoints_type_grading_prearranged');
      }
      if ($num_votes==0) {
	 $number_votes_for_response_by_user = elgg_get_sticky_value('edit_contest','number_votes_for_response_by_user');
         $number_total_votes_by_user = elgg_get_sticky_value('edit_contest','number_total_votes_by_user');
      } else {
	 $number_votes_for_response_by_user = $contest->number_votes_for_response_by_user;
         $number_total_votes_by_user = $contest->number_total_votes_by_user;
      }
   }

   elgg_clear_sticky_form('edit_contest');

   if (strcmp($opentime,"")==0)
      $opentime = "00:00";

   if (strcmp($closetime,"")==0)
      $closetime = "00:00";

   if (strcmp($closetime_voting,"")==0)
      $closetime_voting = "00:00";

   if (strcmp($number_votes_for_response_by_user,"")==0)
      $number_votes_for_response_by_user = "1";

   if (strcmp($number_total_votes_by_user,"")==0)
      $number_total_votes_by_user = "1";

   if (strcmp($number_winners_type_grading_percentage,"")==0)
      $number_winners_type_grading_percentage = "1";

   if (strcmp($total_gamepoints,"")==0)
      $total_gamepoints = "100";

   if (strcmp($gamepoints_type_grading_prearranged,"")==0)
      $gamepoints_type_grading_prearranged = "60,30,10";

   if ($count_responses>0) {
      $disabled = "disabled";
   } else {
      $disabled = "";
   }

   if ($num_votes>0) {
      $disabled_voting_opened = "disabled";
   } else {
      $disabled_voting_opened = "";
   }

   $options_response_type=array();
   $options_response_type[0]=elgg_echo('contest:response_type_simple');
   $options_response_type[1]=elgg_echo('contest:response_type_urls_files');
   $op_response_type=array();
   $op_response_type[0]="simple";
   $op_response_type[1]="urls_files";
   $checked_radio_response_type_0 = "";
   $checked_radio_response_type_1 = "";
   switch($response_type){
      case 'simple':
         $checked_radio_response_type_0 = "checked = \"checked\"";
         break;
      case 'urls_files':
         $checked_radio_response_type_1 = "checked = \"checked\"";
         break;
   }

   $opendate_label = elgg_echo('contest:opendate');
   $opentime_label = elgg_echo('contest:opentime');
   $closedate_label = elgg_echo('contest:closedate');
   $closetime_label = elgg_echo('contest:closetime');
   $closedate_voting_label = elgg_echo('contest:closedate_voting');
   $closetime_voting_label = elgg_echo('contest:closetime_voting');

   $options_activate=array();
   $options_activate[0]=elgg_echo('contest:activate_now');
   $options_activate[1]=elgg_echo('contest:activate_date');
   $op_activate=array();
   $op_activate[0]='contest_activate_now';
   $op_activate[1]='contest_activate_date';
   if (strcmp($option_activate_value,$op_activate[0])==0){
       $checked_radio_activate_0 = "checked = \"checked\"";
       $checked_radio_activate_1 = "";
       $style_display_activate = "display:none";
   } else {
      $checked_radio_activate_0 = "";
      $checked_radio_activate_1 = "checked = \"checked\"";
      $style_display_activate = "display:block";
   }

   $options_opened_voting=array();
   $options_opened_voting[0]=elgg_echo('contest:opened_voting_while_answering');
   $options_opened_voting[1]=elgg_echo('contest:opened_voting_after_answering');
   $op_opened_voting=array();
   $op_opened_voting[0]='contest_opened_voting_while_answering';
   $op_opened_voting[1]='contest_opened_voting_after_answering';
   if (strcmp($option_opened_voting_value,$op_opened_voting[0])==0){
      $checked_radio_opened_voting_0 = "checked = \"checked\"";
      $checked_radio_opened_voting_1 = "";
   } else {
      $checked_radio_opened_voting_0 = "";
      $checked_radio_opened_voting_1 = "checked = \"checked\"";
   }

   $several_user_responses_label = elgg_echo('contest:several_user_responses_label');

   if ($several_user_responses){
      $selected_several_user_responses = "checked = \"checked\"";
   } else {
      $selected_several_user_responses = "";
   }

   $responses_visibility_label = elgg_echo('contest:responses_visibility_label');

   if (!$responses_visibility){
      $selected_responses_visibility = "checked = \"checked\"";
   } else {
      $selected_responses_visibility = "";
   }

   $responses_authors_visibility_label = elgg_echo('contest:responses_authors_visibility_label');

   if (!$responses_authors_visibility){
      $selected_responses_authors_visibility = "checked = \"checked\"";
   } else {
      $selected_responses_authors_visibility = "";
   }

   if ($container instanceof ElggGroup) {
      $subgroups_label = elgg_echo('contest:subgroups_label');
      if ($subgroups){
         $selected_subgroups = "checked = \"checked\"";
      } else {
         $selected_subgroups = "";
      }
      $contest_with_gamepoints_label = elgg_echo('contest:contest_with_gamepoints_label');
      if ($contest_with_gamepoints){
         $selected_contest_with_gamepoints = "checked = \"checked\"";
         $style_display_contest_with_gamepoints = "display:block";
      } else {
         $selected_contest_with_gamepoints = "";
         $style_display_contest_with_gamepoints = "display:none";
      }
      $options_type_grading=array();
      $options_type_grading[0]=elgg_echo('contest:type_grading_percentage');
      $options_type_grading[1]=elgg_echo('contest:type_grading_prearranged');
      $op_type_grading=array();
      $op_type_grading[0]='contest_type_grading_percentage';
      $op_type_grading[1]='contest_type_grading_prearranged';
      if (strcmp($option_type_grading_value,$op_type_grading[0])==0){
         $checked_radio_type_grading_0 = "checked = \"checked\"";
         $checked_radio_type_grading_1 = "";
         $style_display_type_grading = "display:block";
         $style_display_type_grading_2 = "display:none";
      } else {
         $checked_radio_type_grading_0 = "";
         $checked_radio_type_grading_1 = "checked = \"checked\"";
         $style_display_type_grading = "display:none";
         $style_display_type_grading_2 = "display:block";
      }
   }

   $tag_label = elgg_echo('tags');
   $tag_input = elgg_view('input/tags', array('name' => 'contesttags', 'value' => $tags));
   $access_label = elgg_echo('access');
   $access_input = elgg_view('input/access', array('name' => 'access_id', 'value' => $access_id));

   ?>

   <form action="<?php echo elgg_get_site_url()."action/".$action?>" name="edit_contest" enctype="multipart/form-data" method="post">

   <?php echo elgg_view('input/securitytoken'); ?>

   <p>
   <b><?php echo elgg_echo("contest:title_label"); ?></b><br>
   <?php echo elgg_view("input/text", array('name' => 'title', 'value' => $title)); ?>
   </p>
   <p>
   <b> <?php echo elgg_echo("contest:form_question_simple"); ?></b>
   <?php echo elgg_view("input/longtext" ,array('name' => 'question_html', 'value' => $question_html)); ?>
   </p>

   <?php
   switch ($question_type) {
      case 'urls_files':
         if ($count_responses>0){
            if ((count($question_comp_urls)>0)&&(strcmp($question_comp_urls[0],"")!=0)){
    	       ?>
               <p>
               <b><?php echo elgg_echo("contest:question_urls"); ?></b><br>
               <?php
	       foreach ($question_comp_urls as $url){
	          $comp_url = explode(Chr(24),$url);
                  $comp_url = array_map('trim',$comp_url);
                  $url_name = $comp_url[0];
                  $url_value = $comp_url[1];
                  echo ("<b>" . elgg_echo("contest:question_url_name") . "</b>");
                  echo elgg_view("input/text" ,array('disabled' => 'yes', 'name' => 'question_url_name', 'value' => $url_name));
                  echo ("<b>" . elgg_echo("contest:question_url") . "</b>");
                  echo elgg_view("input/text" ,array('disabled' => 'yes', 'name' => 'question_url', 'value' => $url_value));
               }
	       ?>
	       </p>
               <?php
            }
         } else {
            ?>
            <p>
            <b> <?php echo elgg_echo("contest:form_question_urls"); ?> </b><br>
	    <?php
            if ((count($question_comp_urls)>0)&&(strcmp($question_comp_urls[0],"")!=0)) {
	       $i=0;
               foreach ($question_comp_urls as $url) {
                  ?>
                  <p class="clone_urls">
                  <?php
	          $comp_url = explode(Chr(24),$url);
                  $comp_url = array_map('trim',$comp_url);
                  $url_name = $comp_url[0];
                  $url_value = $comp_url[1];
                  echo ("<b>" . elgg_echo("contest:form_question_url_name") . "</b>");
                  echo elgg_view("input/text", array("name" => "question_urls_names[]","value" => $url_name));
                  echo ("<b>" . elgg_echo("contest:form_question_url") . "</b>");
                  echo elgg_view("input/text", array("name" => "question_urls[]","value" => $url_value));
	          if ($i>0){
	             ?>
		     <!-- remove url -->
                     <a class="remove" href="#" onclick="$(this).parent().slideUp(function(){ $(this).remove() }); return false"><?php echo elgg_echo("delete"); ?></a>
                     <?php
	          }
	          ?>
		  </p>
		  <?php
	          $i=$i+1;
               }
            } else {
               ?>
               <p class="clone_urls">
               <?php
               $comp_url = explode(Chr(24),$question_comp_urls);
               $comp_url = array_map('trim',$comp_url);
               $url_name = $comp_url[0];
               $url_value = $comp_url[1];
               echo ("<b>" . elgg_echo("contest:form_question_url_name") . "</b>");
               echo elgg_view("input/text", array("name" => "question_urls_names[]","value" => $url_name));
               echo ("<b>" . elgg_echo("contest:form_question_url") . "</b>");
               echo elgg_view("input/text", array("name" => "question_urls[]","value" => $url_value));
               ?>
               </p>
               <?php
            }
            ?>
            <!-- add link to add more urls which triggers a jquery clone function -->
            <a href="#" class="add" rel=".clone_urls"><?php echo elgg_echo("contest:add_url"); ?></a>
            <br /><br>
            </p>
            <?php
         }

         $files = elgg_get_entities_from_relationship(array('relationship' => 'question_file_link','relationship_guid' => $contestpost,'inverse_relationship' => false,'type' => 'object','subtype' => 'contest_question_file','limit'=>0));
         if ($count_responses>0){
            if ((count($files)>0)&&(strcmp($files[0]->title,"")!=0)){
               ?>
               <b><?php echo elgg_echo("contest:question_files"); ?></b><br><br>
               <?php
               $question_files = "";
               foreach($files as $file) {
	          $params = $file->getGUID() . "_question";
		  $question_files .= "<p><a href=\"" . elgg_get_site_url() . "mod/contest/download.php?params=$params" . "\">" . $file->title . "</a></p>";
               }
               echo $question_files;
            }
         } else {
            ?>
            <p>
            <b> <?php echo elgg_echo("contest:form_question_files"); ?></b><br>
            <?php echo elgg_view("input/file",array('name' => 'upload[]', 'class' => 'multi'));
            foreach($files as $file) {
               ?>
               <div class="file_wrapper">
	          <a class="bold" onclick="changeFormValue(<?php echo $file->getGUID(); ?>), changeImage(<?php echo $file->getGUID(); ?>)">
                  <img id ="image_<?php echo $file->getGUID(); ?>" src="<?php echo elgg_get_site_url(); ?>mod/contest/graphics/tick.jpeg">
                  </a>
                  <span><?php echo $file->title ?></span>
                  <?php echo elgg_view("input/hidden",array('name' => $file->getGUID(), 'internalid'=> $file->getGUID(), 'value' => '0'));?>
               </div>
               <br>
               <?php
            }
	    ?>
	    </p>
	    <?php
         }
         break;
   }
   ?>

   <!-- add the add_response/delete_response functionality  -->
   <script type="text/javascript">
   // remove function for the jquery clone plugin
   $(function(){
      var removeLink = '<a class="remove" href="#" onclick="$(this).parent().slideUp(function(){ $(this).remove() }); return false"><?php echo elgg_echo("delete");?></a>';
      $('a.add').relCopy({ append: removeLink});
   });
   </script>

   <br>
   <p>
   <b><?php echo elgg_echo("contest:response_type_label"); ?></b><br>
   <?php echo "<input type=\"radio\" name=\"response_type\" $disabled value=$op_response_type[0] $checked_radio_response_type_0>$options_response_type[0]";?><br>
   <?php echo "<input type=\"radio\" name=\"response_type\" $disabled value=$op_response_type[1] $checked_radio_response_type_1>$options_response_type[1]";?><br>
   </p><br>

   <br>
   <p>
   <b>
   <?php echo "<input type = \"checkbox\" $disabled name = \"several_user_responses\" $selected_several_user_responses> $several_user_responses_label"; ?>
   </b>
   </p><br>

   <p>
   <b>
   <?php echo "<input type = \"checkbox\" $disabled name = \"responses_visibility\" $selected_responses_visibility> $responses_visibility_label"; ?>
   </b>
   </p><br>

   <p>
   <b>
   <?php echo "<input type = \"checkbox\" $disabled name = \"responses_authors_visibility\" $selected_responses_authors_visibility> $responses_authors_visibility_label"; ?>
   </b>
   </p><br>

   <table class="contest_dates_table">
   <tr>
   <td>
   <p>
   <b><?php echo elgg_echo('contest:activate_label'); ?></b><br>
   <?php echo "<input type=\"radio\" name=\"option_activate_value\" value=$op_activate[0] $checked_radio_activate_0 onChange=\"contest_show_activate_time()\">$options_activate[0]"; ?><br>
   <?php echo "<input type=\"radio\" name=\"option_activate_value\" value=$op_activate[1] $checked_radio_activate_1 onChange=\"contest_show_activate_time()\">$options_activate[1]"; ?><br>
   <div id="resultsDiv_activate" style="<?php echo $style_display_activate;?>;">
      <?php echo $opendate_label; ?><br>
      <?php echo elgg_view('input/date',array('autocomplete'=>'off','class'=>'contest-compressed-date','name'=>'opendate','value'=>$opendate)); ?>
      <?php echo "<br>" . $opentime_label; ?> <br>
      <?php echo "<input type = \"text\" name = \"opentime\" value = $opentime>"; ?>
   </div>
   </p><br>
   </td>
   <td>
   <p>
   <b><?php echo elgg_echo('contest:close_label'); ?></b><br>
   <?php echo $closedate_label; ?><br>
   <?php echo elgg_view('input/date',array('autocomplete'=>'off','class'=>'contest-compressed-date','name'=>'closedate','value'=>$closedate)); ?>
   <?php echo "<br>" . $closetime_label; ?> <br>
   <?php echo "<input type = \"text\" name = \"closetime\" value = $closetime>"; ?>
   </p><br>
   </td>
   </tr>
   </table>

   <table class="contest_dates_table">
   <tr>
   <td>
   <p>
   <b><?php echo elgg_echo('contest:opened_voting_label'); ?></b><br>
   <?php echo "<input type=\"radio\" name=\"option_opened_voting_value\" value=$op_opened_voting[0] $checked_radio_opened_voting_0>$options_opened_voting[0]";?><br>
   <?php echo "<input type=\"radio\" name=\"option_opened_voting_value\" value=$op_opened_voting[1] $checked_radio_opened_voting_1>$options_opened_voting[1]";?><br>
   </p><br>
   </td>
   <td>
   <p>
   <b><?php echo elgg_echo('contest:close_voting_label'); ?></b><br>
   <?php echo $closedate_voting_label; ?><br>
   <?php echo elgg_view('input/date',array('autocomplete'=>'off','class'=>'contest-compressed-date','name'=>'closedate_voting','value'=>$closedate_voting)); ?>
   <?php echo "<br>" . $closetime_voting_label; ?> <br>
   <?php echo "<input type = \"text\" name = \"closetime_voting\" value = $closetime_voting>"; ?>
   </p><br>
   </td>
   </tr>
   </table>

   <b><?php echo elgg_echo('contest:votes_for_response_by_user_label'); ?></b><br>
   <div id="resultsDiv_votes_for_response_by_user" style="block">
      <?php echo elgg_echo('contest:number_votes_for_response_by_user_label'); ?> <br>
      <?php 
      if ($disabled_voting_opened) {
         echo elgg_view('input/dropdown',array('name'=>'number_votes_for_response_by_user', 'disabled'=> $disabled_voting_opened, 'options_values'=>array("0","1","2","3","4","5"), 'value'=> $number_votes_for_response_by_user));
      } else {
         echo elgg_view('input/dropdown',array('name'=>'number_votes_for_response_by_user', 'options_values'=>array("0","1","2","3","4","5"), 'value'=> $number_votes_for_response_by_user));
      }?>
   </div>

   <br>
   <b><?php echo elgg_echo('contest:total_votes_by_user_label'); ?></b><br>
   <div id="resultsDiv_total_votes_by_user" style="block">
      <?php echo elgg_echo('contest:number_total_votes_by_user_label'); ?> <br>
      <?php echo "<input type = \"text\" $disabled_voting_opened name = \"number_total_votes_by_user\" value = $number_total_votes_by_user>"; ?>
   </div><br>

   <?php
   if ($container instanceof ElggGroup) {
   ?>
      <p>
      <b>
      <?php echo "<input type = \"checkbox\" $disabled name = \"subgroups\" $selected_subgroups> $subgroups_label"; ?>
      </b>
      </p><br>

      <p>
      <b>
      <?php echo "<input type = \"checkbox\" name = \"contest_with_gamepoints\" onChange=\"contest_show_contest_with_gamepoints()\" $selected_contest_with_gamepoints> $contest_with_gamepoints_label"; ?>
      </b>
      </p>
      <div id="resultsDiv_contest_with_gamepoints" style="<?php echo $style_display_contest_with_gamepoints;?>;">
         <?php echo elgg_echo('contest:total_gamepoints_label'); ?> <br>
         <?php echo "<input type = \"text\" name = \"total_gamepoints\" value = $total_gamepoints>"; ?>
         <p><br>
         <b><?php echo elgg_echo('contest:type_grading_label'); ?></b><br>
         <?php echo "<input type=\"radio\" name=\"option_type_grading_value\" value=$op_type_grading[0] $checked_radio_type_grading_0 onChange=\"contest_show_type_grading()\">$options_type_grading[0]";?><br>
         <?php echo "<input type=\"radio\" name=\"option_type_grading_value\" value=$op_type_grading[1] $checked_radio_type_grading_1 onChange=\"contest_show_type_grading()\">$options_type_grading[1]";?><br>
         </p>
         <div id="resultsDiv_type_grading" style="<?php echo $style_display_type_grading;?>;">
            <?php echo elgg_echo('contest:number_winners_type_grading_percentage_label'); ?> <br>
            <?php echo "<input type = \"text\" name = \"number_winners_type_grading_percentage\" value = $number_winners_type_grading_percentage>"; ?>
         </div>
         <div id="resultsDiv_type_grading_2" style="<?php echo $style_display_type_grading_2;?>;">
            <?php echo elgg_echo('contest:gamepoints_type_grading_prearranged_label'); ?> <br>
            <?php echo "<input type = \"text\" name = \"gamepoints_type_grading_prearranged\" value = $gamepoints_type_grading_prearranged>"; ?>
         </div>
      </div>
   <?php
   }
   ?>
   <p><br>
   <b>
   <?php echo $tag_label; ?></b><br>
   <?php echo $tag_input; ?></p><br>
   <p>
   <b><?php echo $access_label; ?></b><br>
   <?php echo $access_input; ?>
   </p>

   <?php
   $submit_input_save = elgg_view('input/submit', array('name' => 'submit', 'value' => elgg_echo("contest:save")));
   echo ($submit_input_save);
   ?>

   <input type="hidden" name="contestpost" value="<?php echo $contestpost; ?>">
   <input type="hidden" name="question_type" value="<?php echo $question_type; ?>">

</form>

<?php
}
?>

<script language="javascript">
   function contest_show_activate_time(){
      var resultsDiv_activate = document.getElementById('resultsDiv_activate');
      if (resultsDiv_activate.style.display == 'none'){
         resultsDiv_activate.style.display = 'block';
      } else {
         resultsDiv_activate.style.display = 'none';
      }
   }
   function contest_show_contest_with_gamepoints(){
      var resultsDiv_contest_with_gamepoints = document.getElementById('resultsDiv_contest_with_gamepoints');
      if (resultsDiv_contest_with_gamepoints.style.display == 'none'){
         resultsDiv_contest_with_gamepoints.style.display = 'block';
      } else {
         resultsDiv_contest_with_gamepoints.style.display = 'none';
      }
   }
   function contest_show_type_grading(){
      var resultsDiv_type_grading = document.getElementById('resultsDiv_type_grading');
      var resultsDiv_type_grading_2 = document.getElementById('resultsDiv_type_grading_2');
      if (resultsDiv_type_grading.style.display == 'none'){
         resultsDiv_type_grading.style.display = 'block';
         resultsDiv_type_grading_2.style.display = 'none';
      } else {
         resultsDiv_type_grading.style.display = 'none';
         resultsDiv_type_grading_2.style.display = 'block';
      }
   }
</script>

<script type="text/javascript" src="<?php echo elgg_get_site_url(); ?>mod/contest/lib/jquery.MultiFile.js"></script><!-- multi file jquery plugin -->
<script type="text/javascript" src="<?php echo elgg_get_site_url(); ?>mod/contest/lib/reCopy.js"></script><!-- copy field jquery plugin -->
<script type="text/javascript" src="<?php echo elgg_get_site_url(); ?>mod/contest/lib/js_functions.js"></script>
<script type="text/javascript">
    function changeImage(num) {
        if (document.getElementById('image_'+num).src == "<?php echo elgg_get_site_url(); ?>mod/contest/graphics/tick.jpeg")
            document.getElementById('image_'+num).src = "<?php echo elgg_get_site_url(); ?>mod/contest/graphics/delete_file.jpeg";
        else
            document.getElementById('image_'+num).src = "<?php echo elgg_get_site_url(); ?>mod/contest/graphics/tick.jpeg";
    }
</script>


</div>
