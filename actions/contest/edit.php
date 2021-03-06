<?php

gatekeeper();

$contestpost = get_input('contestpost');
$contest = get_entity($contestpost);
$close_contest = get_input('close_contest');

$now=time();

if (strcmp($close_contest,"yes")==0){
   $contest->close_time = $now;
   $contest->close_time_voting = $now;
   // Delete evvents created with the contest (if event_manager plugin)
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
   forward("contest/edit/$contestpost");
}

$num_votes = $contest->countAnnotations('all_votes');

if ($contest->getSubtype() == "contest" && $contest->canEdit()) {

   $total_winners_changed = false;

   $user_guid = elgg_get_logged_in_user_guid();
   $user = get_entity($user_guid);

   $title = get_input('title');

   $option_activate_value = get_input('option_activate_value');
   if (strcmp($option_activate_value,'contest_activate_date')==0){
      $opendate = get_input('opendate');
      $opentime = get_input('opentime');
   }
   $closedate = get_input('closedate');
   $closetime = get_input('closetime');
   $option_opened_voting_value = get_input('option_opened_voting_value');
   $closedate_voting = get_input('closedate_voting');
   $closetime_voting = get_input('closetime_voting');

   if ($num_votes==0) {
      $number_votes_for_response_by_user = get_input('number_votes_for_response_by_user');
      $number_total_votes_by_user = get_input('number_total_votes_by_user');
   }

   $tags = get_input('contesttags');
   $access_id = get_input('access_id');

   $container_guid = $contest->container_guid;
   $container = get_entity($container_guid);

   $count_responses=$contest->countAnnotations('all_responses');

   $input_question_html = get_input('question_html');
   $input_question_type = get_input('question_type');
   switch($input_question_type){
      case 'urls_files':
         if ($count_responses==0){
            $question_urls = get_input('question_urls');
            $question_urls = array_map('trim',$question_urls);
	    $question_urls_names = get_input('question_urls_names');
            $question_urls_names = array_map('trim',$question_urls_names);
            $i=0;
            $input_question_urls = "";
	    if ((count($question_urls)>0)&&(strcmp($question_urls[0],"")!=0)) {
               foreach($question_urls as $url){
                  if ($i!=0)
                     $input_question_urls .= Chr(26);
                  $input_question_urls .= $question_urls_names[$i] . Chr(24) . $question_urls[$i];
                  $i=$i+1;
               }
	    }
            $number_question_urls = count($question_urls);
         }
         break;
   }
   $file_counter=0;

   if ($count_responses==0){
      $file_counter = count($_FILES['upload']['name']);
      $input_response_type = get_input('response_type');
      if ($container instanceof ElggGroup)
         $subgroups = get_input('subgroups');

   $several_user_responses = get_input('several_user_responses');
   $responses_visibility = get_input('responses_visibility');
   $responses_authors_visibility = get_input('responses_authors_visibility');

   }

   if ($container instanceof ElggGroup) {
      $contest_with_gamepoints = get_input('contest_with_gamepoints');
      if (strcmp($contest_with_gamepoints,"on")==0) {
         $total_gamepoints = get_input('total_gamepoints');
         $option_type_grading_value = get_input('option_type_grading_value');
         if (strcmp($option_type_grading_value,'contest_type_grading_percentage')==0){
            $number_winners_type_grading_percentage = get_input('number_winners_type_grading_percentage');
            if($number_winners_type_grading_percentage != $total_winners)
               $total_winners_changed = true;
         } else {
            $gamepoints_type_grading_prearranged = get_input('gamepoints_type_grading_prearranged');
            if(count(explode(",", $gamepoints_type_grading_prearranged)) != $total_winners)
               $total_winners_changed = true;
         }
      }
   }

   // Cache to the session
   elgg_make_sticky_form('edit_contest');

   if ($count_responses == 0) {
      if (strcmp($input_response_type,'')==0) {
         register_error(elgg_echo("contest:empty_response_type"));
	 forward("contest/edit/$contestpost");
      }
   }

   $previous_files = elgg_get_entities_from_relationship(array('relationship' => 'question_file_link','relationship_guid' => $contestpost,'inverse_relationship' => false,'type' => 'object','limit'=>0));

   if (strcmp($option_activate_value,'contest_activate_date')==0){
      $mask_time="[0-2][0-9]:[0-5][0-9]";
      if (!ereg($mask_time,$opentime,$same)){
         register_error(elgg_echo("contest:bad_times"));
	 forward("contest/edit/$contestpost");
      }
   }
   $mask_time="[0-2][0-9]:[0-5][0-9]";
   if (!ereg($mask_time,$closetime,$same)){
      register_error(elgg_echo("contest:bad_times"));
      forward("contest/edit/$contestpost");
   }
   $mask_time="[0-2][0-9]:[0-5][0-9]";
   if (!ereg($mask_time,$closetime_voting,$same)){
      register_error(elgg_echo("contest:bad_times"));
      forward("contest/edit/$contestpost");
   }
   if (strcmp($option_activate_value,'contest_activate_now')==0){
      $activate_time=$now;
   } else {
      $opentime_array = explode(':',$opentime);
      $opentime_h = trim($opentime_array[0]);
      $opentime_m = trim($opentime_array[1]);
      $opendate_array = explode('-',$opendate);
      $opendate_y = trim($opendate_array[0]);
      $opendate_m = trim($opendate_array[1]);
      $opendate_d = trim($opendate_array[2]);
      $activate_date = mktime(0,0,0,$opendate_m,$opendate_d,$opendate_y);
      $activate_time = mktime($opentime_h,$opentime_m,0,$opendate_m,$opendate_d,$opendate_y);

      if ($activate_time < 1){
         register_error(elgg_echo("contest:bad_times"));
         forward("contest/edit/$contestpost");
      }
   }
   $closetime_array = explode(':',$closetime);
   $closetime_h = trim($closetime_array[0]);
   $closetime_m = trim($closetime_array[1]);
   $closedate_array = explode('-',$closedate);
   $closedate_y = trim($closedate_array[0]);
   $closedate_m = trim($closedate_array[1]);
   $closedate_d = trim($closedate_array[2]);
   $close_date = mktime(0,0,0,$closedate_m,$closedate_d,$closedate_y);
   $close_time = mktime($closetime_h,$closetime_m,0,$closedate_m,$closedate_d,$closedate_y);

   if ($close_time < 1){
      register_error(elgg_echo("contest:bad_times"));
      forward("contest/edit/$contestpost");
   }

   if ($activate_time>=$close_time) {
      register_error(elgg_echo("contest:error_times"));
      forward("contest/edit/$contestpost");
   }

   $closetime_voting_array = explode(':',$closetime_voting);
   $closetime_voting_h = trim($closetime_voting_array[0]);
   $closetime_voting_m = trim($closetime_voting_array[1]);
   $closedate_voting_array = explode('-',$closedate_voting);
   $closedate_voting_y = trim($closedate_voting_array[0]);
   $closedate_voting_m = trim($closedate_voting_array[1]);
   $closedate_voting_d = trim($closedate_voting_array[2]);
   $close_date_voting = mktime(0,0,0,$closedate_voting_m,$closedate_voting_d,$closedate_voting_y);
   $close_time_voting = mktime($closetime_voting_h,$closetime_voting_m,0,$closedate_voting_m,$closedate_voting_d,$closedate_voting_y);

   if ($close_time_voting < 1){
      register_error(elgg_echo("contest:bad_times"));
      forward("contest/edit/$contestpost");
   }

   if ($close_time_voting<$close_time) {
      register_error(elgg_echo("contest:closing_error_times"));
      forward("contest/edit/$contestpost");
   }

   if ($num_votes==0) {
      //Integer number_votes_for_response_by_user (number>1)
      $is_integer = true;
      $mask_integer='^([[:digit:]]+)$';
      if (ereg($mask_integer,$number_votes_for_response_by_user,$same)){
         if ((substr($same[1],0,1)==0)&&(strlen($same[1])!=1)){
            $is_integer=false;
         }
      } else {
         $is_integer=false;
      }
      if (!$is_integer){
         register_error(elgg_echo("contest:bad_number_votes_for_response_by_user"));
         forward("contest/edit/$contestpost");
      }
      if ($number_votes_for_response_by_user<1){
         register_error(elgg_echo("contest:bad_number_votes_for_response_by_user"));
         forward("contest/edit/$contestpost");
      }

      $is_integer = true;
      $mask_integer='^([[:digit:]]+)$';
      if (ereg($mask_integer,$number_total_votes_by_user,$same)){
         if ((substr($same[1],0,1)==0)&&(strlen($same[1])!=1)){
            $is_integer=false;
         }
      } else {
         $is_integer=false;
      }
      if (!$is_integer){
         register_error(elgg_echo("contest:bad_number_total_votes_by_user"));
         forward("contest/edit/$contestpost");
      }
      if ($number_total_votes_by_user<1){
         register_error(elgg_echo("contest:bad_number_total_votes_by_user"));
         forward("contest/edit/$contestpost");
      }
   
      if ($number_total_votes_by_user<$number_votes_for_response_by_user){
         register_error(elgg_echo("contest:bad_numbers_votes"));
         forward("contest/edit/$contestpost");
      }
   }

   if ($container instanceof ElggGroup) {
      if (strcmp($contest_with_gamepoints,"on")==0) {
         //Integer total_gamepoints (number>1)
         $is_integer = true;
         $mask_integer='^([[:digit:]]+)$';
         if (ereg($mask_integer,$total_gamepoints,$same)){
            if ((substr($same[1],0,1)==0)&&(strlen($same[1])!=1)){
               $is_integer=false;
            }
         } else {
            $is_integer=false;
         }
         if (!$is_integer){
            register_error(elgg_echo("contest:bad_total_gamepoints"));
            forward("contest/edit/$contestpost");
         }
         if ($total_gamepoints<1){
            register_error(elgg_echo("contest:bad_total_gamepoints"));
            forward("contest/edit/$contestpost");
         }

         if (strcmp($option_type_grading_value,'contest_type_grading_percentage')==0){

            //Integer number_winners_type_grading_percentage (number>1)
            $is_integer = true;
            $mask_integer='^([[:digit:]]+)$';
            if (ereg($mask_integer,$number_winners_type_grading_percentage,$same)){
               if ((substr($same[1],0,1)==0)&&(strlen($same[1])!=1)){
                  $is_integer=false;
               }
            } else {
               $is_integer=false;
            }
            if (!$is_integer){
               register_error(elgg_echo("contest:bad_number_winners_type_grading_percentage"));
               forward("contest/edit/$contestpost");
            }
            if ($number_winners_type_grading_percentage<1){
               register_error(elgg_echo("contest:bad_number_winners_type_grading_percentage"));
               forward("contest/edit/$contestpost");
            }
         } else {
            $gamepoints_array = explode(",",$gamepoints_type_grading_prearranged);
            $max = 100;
	    $total_per = 0;
            foreach ($gamepoints_array as $gamepoints) {
               //Integer (number>1)
               $is_integer = true;
               $mask_integer='^([[:digit:]]+)$';
               if (ereg($mask_integer,$gamepoints,$same)){
                  if ((substr($same[1],0,1)==0)&&(strlen($same[1])!=1)){
                     $is_integer=false;
                  }
               } else {
                  $is_integer=false;
               }
               if (!$is_integer){
                  register_error(elgg_echo("contest:bad_gamepoints_type_grading_prearranged"));
                  forward("contest/edit/$contestpost");
               }
	       if ($gamepoints>$max) {
	          register_error(elgg_echo("contest:gamepoints_type_grading_prearranged_not_decrease"));
                  forward("contest/edit/$contestpost");
	       }
	       $total_per += $gamepoints;
	       $max = $gamepoints;
            }
	    if ($total_per!=100) {
	       register_error(elgg_echo("contest:bad_gamepoints_type_grading_prearranged"));
               forward("contest/edit/$contestpost");
	    }
         }
      }
   }

   //////////////////////////////////////////////////////////////////////////

   // Convert string of tags into a preformatted array
   $tagarray = string_to_tag_array($tags);

   // Make sure the title is not blank
   if (strcmp($title,"")==0) {
      register_error(elgg_echo("contest:title_blank"));
      forward("contest/edit/$contestpost");
   }

   // Question urls
   if ((strcmp($input_question_type,"urls_files")==0)&&($count_responses==0)){
      $blank_question_url=false;
      $questionurlsarray=array();
      $i=0;
      foreach($question_urls as $one_url){
         $questionurlsarray[$i]=$one_url;
         if (strcmp($one_url,"")==0){
            $blank_question_url=true;
            break;
         }
         $i=$i+1;
      }
      if (!$blank_question_url){
         foreach($question_urls_names as $one_url_name){
            if (strcmp($one_url_name,"")==0){
               $blank_question_url=true;
               break;
            }
         }
      }
      if (($blank_question_url)&&($number_question_urls>1)){
         register_error(elgg_echo("contest:url_blank"));
	 forward("contest/edit/$contestpost");
      }
      $same_question_url=false;
      $i=0;
      while(($i<$number_question_urls)&&(!$same_question_url)){
         $j=$i+1;
         while($j<$number_question_urls){
            if (strcmp($questionurlsarray[$i],$questionurlsarray[$j])==0){
               $same_question_url=true;
               break;
            }
            $j=$j+1;
         }
         $i=$i+1;
      }
      if ($same_question_url){
         register_error(elgg_echo("contest:url_repetition"));
         forward("contest/edit/$contestpost");
      }
      if (!$question_url_blank){
         foreach($question_urls as $url){
            $xss_contest = "<a rel=\"nofollow\" href=\"$url\" target=\"_blank\">$url</a>";
            if ($xss_contest != filter_tags($xss_contest)) {
               register_error(elgg_echo('contest:url_failed'));
               forward("contest/edit/$contestpost");
            }
         }
      }
   }

   if ($count_responses==0){

      if (!empty($previous_files))
         $previous_file_counter=count($previous_files);
      else
         $previous_file_counter=0;
      foreach($previous_files as $one_file) {
         $value = get_input($one_file->getGUID());
         if($value == '1')
            $previous_file_counter = $previous_file_counter-1;
      }

      if ((strcmp($input_question_type,"urls_files")==0)&&((($file_counter+$previous_file_counter+$number_question_urls)==0)||((($previous_file_counter+$number_question_urls)==0)&&($_FILES['upload']['name'][0] == "")))){
         register_error(elgg_echo('contest:not_question_urls_files'));
         forward("contest/edit/$contestpost");
      }
      if (($file_counter>0)&&($_FILES['upload']['name'][0] != "")){
	 $file_save_well=true;
         $file=array();
         for($i=0; $i<$file_counter; $i++){
            $file[$i] = new QuestionsContestPluginFile();
            $file[$i]->subtype = "contest_question_file";
	    $prefix = "file/";
	    $filestorename = elgg_strtolower(time().$_FILES['upload']['name'][$i]);
	    $file[$i]->setFilename($prefix.$filestorename);
            $file[$i]->setMimeType($_FILES['upload']['type'][$i]);
            $file[$i]->originalfilename = $_FILES['upload']['name'][$i];
            $file[$i]->simpletype = elgg_get_file_simple_type($_FILES['upload']['type'][$i]);
	    $file[$i]->open("write");
	    if (isset($_FILES['upload']) && isset($_FILES['upload']['error'][$i])) {
               $uploaded_file = file_get_contents($_FILES['upload']['tmp_name'][$i]);
            } else {
               $uploaded_file = false;
            }
            $file[$i]->write($uploaded_file);
            $file[$i]->close();
            $file[$i]->title = $_FILES['upload']['name'][$i];
	    $file[$i]->owner_guid = $user_guid;
	    $file[$i]->container_guid = $contest->container_guid;
            $file[$i]->access_id = $access_id;
            $file_save = $file[$i]->save();
            if (!$file_save) {
	       $file_save_well=false;
	       break;
            }
         }
	 if (!$file_save_well){
            foreach($file as $one_file){
	       $deleted=$one_file->delete();
	       if (!$deleted){
		  register_error(elgg_echo('contest:filenotdeleted'));
                  forward("contest/edit/$contestpost");
	       }
	    }
	    register_error(elgg_echo('contest:file_error_save'));
            forward("contest/edit/$contestpost");
	 }
      }
   }

   // Set its access
   $contest->access_id = $access_id;

   // Set its title
   $contest->title= $title;

   // Set its description
   $contest->description = "";

   // Before we can set metadata, we need to save the contest post
   if (!$contest->save()) {
       if ($count_responses==0){
	 if (($file_counter>0)&&($_FILES['upload']['name'][0] != "")){
	    foreach($file as $one_file){
	       $deleted=$one_file->delete();
	       if (!$deleted){
	          register_error(elgg_echo('contest:filenotdeleted'));
                  forward("contest/edit/$contestpost");
	       }
	    }
	 }
      }
      register_error(elgg_echo("contest:error_save"));
      forward("contest/edit/$contestpost");
   }

   //Set times
   $contest->option_activate_value = $option_activate_value;
   if (strcmp($option_activate_value,'contest_activate_now')!=0){
      $contest->activate_date = $activate_date;
      $contest->activate_time = $activate_time;
      $contest->form_activate_date = $activate_date;
      $contest->form_activate_time = $opentime;
   }
   $contest->close_date = $close_date;
   $contest->close_time = $close_time;
   $contest->form_close_date = $close_date;
   $contest->form_close_time = $closetime;

   $contest->option_opened_voting_value = $option_opened_voting_value;
   $contest->close_date_voting = $close_date_voting;
   $contest->close_time_voting = $close_time_voting;
   $contest->form_close_date_voting = $close_date_voting;
   $contest->form_close_time_voting = $closetime_voting;

   if ($count_responses==0){
      // Set response type
      $contest->response_type = $input_response_type;
      if ($container instanceof ElggGroup) {
         // Set subgroups
         if (strcmp($subgroups,"on")==0) {
            $contest->subgroups=true;
	    $contest->who_answers='subgroup';
         } else {
            $contest->subgroups=false;
	    $contest->who_answers='member';
         }
      }
      // Set several responses by user
      if (strcmp($several_user_responses,"on")==0) {
         $contest->several_user_responses=true;
      } else {
         $contest->several_user_responses=false;
      }
      // Set responses visibility
      if (strcmp($responses_visibility,"on")==0) {
         $contest->responses_visibility=false;
      } else {
         $contest->responses_visibility=true;
      }
      // Set responses authors visibility
      if (strcmp($responses_authors_visibility,"on")==0) {
         $contest->responses_authors_visibility=false;
      } else {
         $contest->responses_authors_visibility=true;
      }
   }
   // Set limits of votes
   if ($num_votes==0) {
      $contest->number_votes_for_response_by_user = $number_votes_for_response_by_user;
      $contest->number_total_votes_by_user = $number_total_votes_by_user;
   }

   //Set type_grading
   if ($container instanceof ElggGroup) {
      if (strcmp($contest_with_gamepoints,"on")==0) {
         $contest->contest_with_gamepoints = true;
         $contest->total_gamepoints = $total_gamepoints;
         $contest->option_type_grading_value = $option_type_grading_value;
         if (strcmp($option_type_grading_value,'contest_type_grading_percentage')==0){
            $contest->number_winners_type_grading_percentage = $number_winners_type_grading_percentage;
            $contest->total_gamepoints_type_grading_percentage = $total_gamepoints_type_grading_percentage;
         } else {
            $contest->gamepoints_type_grading_prearranged = $gamepoints_type_grading_prearranged;
          }
      } else {
         $contest->contest_with_gamepoints = false;
      }
   }

   // Now let's add tags.
   if (is_array($tagarray)) {
      $contest->tags = $tagarray;
   }

   // Previous files
   if ($count_responses==0){
      //Delete previous question files
      switch($input_question_type){
         case 'urls_files':
	    foreach($previous_files as $one_file) {
               $value = get_input($one_file->getGUID());
               if ($value == '1'){
                  $file1 = get_entity($one_file->getGUID());
                  $deleted=$file1->delete();
                  if (!$deleted){
		     register_error(elgg_echo('contest:filenotdeleted'));
                     forward("contest/edit/$contestpost");
		  }
	       } else {
                  $one_file->access_id = $access_id;
                  if (!$one_file->save()){
                     register_error(elgg_echo("contest:file_error_save"));
                     forward("contest/edit/$contestpost");
                  }
	       }
            }
	    break;
      }
   }

   //Set question fields
   $contest->question_html = $input_question_html;
   $contest->question_type = $input_question_type;
   switch($input_question_type){
      case 'urls_files':
         if ($count_responses==0){
            $contest->question_urls = $input_question_urls;
	 }
         break;
   }
   if ($count_responses==0){
      if (($file_counter>0)&&($_FILES['upload']['name'][0] != "")){
         for($i=0; $i<$file_counter; $i++){
            add_entity_relationship($contestpost,'question_file_link',$file[$i]->getGUID());
	 }
      }
   }

   //Change access_id in answers
   $options = array('relationship' => 'contest_answer', 'relationship_guid' => $contestpost,'inverse_relationship' => false, 'type' => 'object', 'subtype' => 'contest_answer','limit'=>0);
   $users_responses=elgg_get_entities_from_relationship($options);
   foreach($users_responses as $one_response){
      if ($container instanceof ElggGroup) {
         if ($contest->subgroups) {
            $response_subgroup_guid = $one_response->container_guid;
	    $response_subgroup = get_entity($response_subgroup_guid);
         }
      }
      $files_response = elgg_get_entities_from_relationship(array('relationship' => 'response_file_link','relationship_guid' => $one_response->getGUID(),'inverse_relationship' => false,'type' => 'object','limit'=>0));
      foreach($files_response as $one_file){
	 if ((!$contest->responses_visibility)&&(!$voting_opened)&&($now<$contest->voting_close_time)){
	    if ($container instanceof ElggGroup) {
	       //compañeros y profesores
	       if ($contest->subgroups)
	          $one_file->access_id = $response_subgroup->teachers_acl;
	       else
	          $one_file->access_id = $container->teachers_acl;
	    } else {
	       $one_file->access_id = $contest->access_id;
	    }
	 } else {
	    $one_file->access_id = $contest->access_id;
	 }
	 if (!$one_file->save()) {
            register_error(elgg_echo('contest:file_error_save'));
            forward("contest/edit/$contestpost");
	 }
      }

      if ((!$contest->responses_visibility)&&(!$voting_opened)&&($now<$contest->voting_close_time)){
         if ($container instanceof ElggGroup) {
	    //compañeros y profesores
	    if ($contest->subgroups)
	       $one_response->access_id = $response_subgroup->teachers_acl;
	    else
	       $one_response->access_id = $container->teachers_acl;
	 } else {
	    $one_response->access_id = $contest->access_id;
	 }
      } else {
	 $one_response->access_id = $contest->access_id;
      }
      if (!$one_response->save()) {
         register_error(elgg_echo('contest:answer_error_save'));
         forward("contest/edit/$contestpost");
      }
   }

   // Remove the contest post cache
   elgg_clear_sticky_form('edit_contest');

   // System message
   system_message(elgg_echo("contest:updated"));

   if($total_winners_changed) {
      $badges = elgg_get_entities_from_metadata(array('type' => 'object', 'subtype' => 'badge', 'limit' => false, 'container_guid' => $container_guid));

      foreach($badges as $one_badge){
         if ($one_badge->selected_contests_guids == $contestpost) {
            $one_badge->created = false;
            system_message(elgg_echo("contest:related_badges_were_deactivated"));
         }
      }

   }

   //River
   //elgg_create_river_item(array(
   //   'view'=>'river/object/contest/update',
   //   'action_type'=>'update',
   //   'subject_guid'=>$user_guid,
   //   'object_guid'=>$contestpost,
   //));

   //Events using the event_manager plugin if it is active
   if (elgg_is_active_plugin('event_manager')){

      // End response time
      $event_guid = $contest->event_guid;
      if (!($event=get_entity($event_guid))){
         $event = new Event();
      }

      $event->title = sprintf(elgg_echo('contest:event_manager_title'),$contest->title);
      $event->description = $contest->getURL();
      $event->container_guid = $container_guid;
      $event->access_id = $access_id;
      $event->save();
      $event->tags = string_to_tag_array($tags);
      $event->comments_on = 0;
      $event->registration_ended = 1;
      $event->show_attendees = 0;
      $event->max_attendees = "";
      $event->start_day = $close_date;
      $event->start_time = $close_time;
      $event->end_ts = $close_time+1;
      $event->organizer = $user->getDisplayName();
      $event->setAccessToOwningObjects($access_id);

      // Save it, if it is new
      if (!get_entity($event_guid)){
         if ($event->save()){
            $event_guid = $event->getGUID();
            $contest->event_guid = $event_guid;
         }
         else
            register_error(elgg_echo("contest:event_manager_error_save"));
      }

      // End voting time
      $event_voting_guid = $contest->event_voting_guid;
      if (!($event_voting=get_entity($event_voting_guid))){
         $event_voting = new Event();
      }

      $event_voting->title = sprintf(elgg_echo('contest:event_manager_title_voting'),$contest->title);
      $event_voting->description = $contest->getURL();
      $event_voting->container_guid = $container_guid;
      $event_voting->access_id = $access_id;
      $event_voting->save();
      $event_voting->tags = string_to_tag_array($tags);
      $event_voting->comments_on = 0;
      $event_voting->registration_ended = 1;
      $event_voting->show_attendees = 0;
      $event_voting->max_attendees = "";
      $event_voting->start_day = $close_date_voting;
      $event_voting->start_time = $close_time_voting;
      $event_voting->end_ts = $close_time_voting+1;
      $event_voting->organizer = $user->getDisplayName();
      $event_voting->setAccessToOwningObjects($access_id);

      // Save it, if it is new
      if (!get_entity($event_voting_guid)){
         if ($event_voting->save()){
            $event_voting_guid = $event_voting->getGUID();
            $contest->event_voting_guid = $event_voting_guid;
         }
         else
            register_error(elgg_echo("contest:event_manager_error_save"));
      }
   }

   //Forward
   if ($container instanceof ElggGroup) {
      forward(elgg_get_site_url() . "contest/group/" . $container->getGUID());
   } else {
      $owner = get_entity($contest->getOwnerGUID());
      forward(elgg_get_site_url() . "contest/owner/" . $owner->username);
   }
}

?>
