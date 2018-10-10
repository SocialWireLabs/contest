<?php

gatekeeper();

$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);

$title = get_input('title');
$input_question_html = get_input('question_html');
$input_question_type = get_input('question_type');
switch($input_question_type){
   case 'urls_files':
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
      break;
}
$file_counter = count($_FILES['upload']['name']);
$type_delivery = get_input('type_delivery');
$input_response_type = get_input('response_type');
$several_user_responses = get_input('several_user_responses');
$responses_visibility = get_input('responses_visibility');
$responses_authors_visibility = get_input('responses_authors_visibility');

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

$number_votes_for_response_by_user = get_input('number_votes_for_response_by_user');

$number_total_votes_by_user = get_input('number_total_votes_by_user');

$tags = get_input('contesttags');
$access_id = get_input('access_id');
$container_guid = get_input('container_guid');
$container = get_entity($container_guid);
if ($container instanceof ElggGroup) {
   $subgroups = get_input('subgroups');
   $contest_with_gamepoints = get_input('contest_with_gamepoints');
   if (strcmp($contest_with_gamepoints,"on")==0) {
      $total_gamepoints = get_input('total_gamepoints');
      $option_type_grading_value = get_input('option_type_grading_value');
      if (strcmp($option_type_grading_value,'contest_type_grading_percentage')==0){
         $number_winners_type_grading_percentage = get_input('number_winners_type_grading_percentage');
      } else {
         $gamepoints_type_grading_prearranged = get_input('gamepoints_type_grading_prearranged');
      }
   }
}

// Cache to the session
elgg_make_sticky_form('add_contest');

if (strcmp($option_activate_value,'contest_activate_date')==0){
   $mask_time="[0-2][0-9]:[0-5][0-9]";
   if (!ereg($mask_time,$opentime,$same)){
	register_error(elgg_echo("contest:bad_times"));
	forward($_SERVER['HTTP_REFERER']);
   }
}
$mask_time="[0-2][0-9]:[0-5][0-9]";
if (!ereg($mask_time,$closetime,$same)){
   register_error(elgg_echo("contest:bad_times"));
   forward($_SERVER['HTTP_REFERER']);
}
$mask_time="[0-2][0-9]:[0-5][0-9]";
if (!ereg($mask_time,$closetime_voting,$same)){
   register_error(elgg_echo("contest:bad_times"));
   forward($_SERVER['HTTP_REFERER']);
}

$now=time();
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
      forward($_SERVER['HTTP_REFERER']);
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
   forward($_SERVER['HTTP_REFERER']);
}

if ($activate_time>=$close_time) {
   register_error(elgg_echo("contest:error_times"));
   forward($_SERVER['HTTP_REFERER']);
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
   forward($_SERVER['HTTP_REFERER']);
}

if ($close_time_voting<$close_time) {
   register_error(elgg_echo("contest:closing_error_times"));
   forward($_SERVER['HTTP_REFERER']);
}

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
   forward($_SERVER['HTTP_REFERER']);
}
if ($number_votes_for_response_by_user<1){
   register_error(elgg_echo("contest:bad_number_votes_for_response_by_user"));
   forward($_SERVER['HTTP_REFERER']);
}

//Integer number_total_votes_by_user (number>1)
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
   forward($_SERVER['HTTP_REFERER']);
}
if ($number_total_votes_by_user<1){
   register_error(elgg_echo("contest:bad_number_total_votes_by_user"));
   forward($_SERVER['HTTP_REFERER']);
}

if ($number_total_votes_by_user<$number_votes_for_response_by_user){
   register_error(elgg_echo("contest:bad_numbers_votes"));
   forward($_SERVER['HTTP_REFERER']);
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
         forward($_SERVER['HTTP_REFERER']);
      }
      if ($total_gamepoints<1){
         register_error(elgg_echo("contest:bad_total_gamepoints"));
         forward($_SERVER['HTTP_REFERER']);
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
            forward($_SERVER['HTTP_REFERER']);
         }
         if ($number_winners_type_grading_percentage<1){
            register_error(elgg_echo("contest:bad_number_winners_type_grading_percentage"));
            forward($_SERVER['HTTP_REFERER']);
         }
      } else {
         $gamepoints_array = explode(",",$gamepoints_type_grading_prearranged);
         $max = 100;
	 $total_per = 0;
         foreach ($gamepoints_array as $gamepoints) {
            //Integer
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
               forward($_SERVER['HTTP_REFERER']);
            }
	    if ($gamepoints>$max) {
	       register_error(elgg_echo("contest:gamepoints_type_grading_prearranged_not_decrease"));
               forward($_SERVER['HTTP_REFERER']);
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
if (strcmp($title,"")==0){
   register_error(elgg_echo("contest:title_blank"));
   forward($_SERVER['HTTP_REFERER']);
}

// Question urls
if (strcmp($input_question_type,"urls_files")==0){
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
      forward($_SERVER['HTTP_REFERER']);
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
      forward($_SERVER['HTTP_REFERER']);
   }
   if (!$question_url_blank){
      foreach($question_urls as $url){
         $xss_contest = "<a rel=\"nofollow\" href=\"$url\" target=\"_blank\">$url</a>";
         if ($xss_contest != filter_tags($xss_contest)) {
            register_error(elgg_echo('contest:url_failed'));
	    forward($_SERVER['HTTP_REFERER']);
         }
      }
   }
}

if ((strcmp($input_question_type,"urls_files")==0)&&($_FILES['upload']['name'][0] == "")&&($number_question_urls==0)){
   register_error(elgg_echo('contest:not_question_urls_files'));
   forward($_SERVER['HTTP_REFERER']);
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
      $file[$i]->container_guid = $container_guid;
      $file[$i]->access_id = $access_id;
      $file_save = $file[$i]->save();
      if(!$file_save) {
         $file_save_well=false;
	 break;
      }
   }
   if (!$file_save_well){
      foreach($file as $one_file){
	 $deleted=$one_file->delete();
	 if (!$deleted){
	    register_error(elgg_echo('contest:filenotdeleted'));
	    forward($_SERVER['HTTP_REFERER']);
	 }
      }
      register_error(elgg_echo('contest:file_error_save'));
      forward($_SERVER['HTTP_REFERER']);
   }
}

//////////////////////////////////////////////////////////////////////////

// Initialise a new ElggObject
$contest = new ElggObject();

// Tell the system it's a contest post
$contest->subtype = "contest";

// Set its owner, container and group
$contest->owner_guid = $user_guid;
$contest->container_guid = $container_guid;
$contest->group_guid = $container_guid;

// Set its access
$contest->access_id = $access_id;

// Set its title
$contest->title = $title;

// Set its description
$contest->description = "";

// Save the contest post
if (!$contest->save()) {
   if (($file_counter>0)&&($_FILES['upload']['name'][0] != "")){
      foreach($file as $one_file){
         $deleted=$one_file->delete();
         if (!$deleted){
            register_error(elgg_echo('contest:filenotdeleted'));
	    forward($_SERVER['HTTP_REFERER']);
         }
      }
   }
   register_error(elgg_echo("contest:error_save"));
   forward($_SERVER['HTTP_REFERER']);
}

$contestpost=$contest->getGUID();

// Set question fields
$contest->question_html = $input_question_html;
$contest->question_type = $input_question_type;
switch($input_question_type){
   case 'urls_files':
      $contest->question_urls = $input_question_urls;
      break;
}
if (($file_counter>0)&&($_FILES['upload']['name'][0] != "")){
   for($i=0; $i<$file_counter; $i++){
      add_entity_relationship($contestpost,'question_file_link',$file[$i]->getGUID());
   }
}

// Set times
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

// Set limits of votes
$contest->number_votes_for_response_by_user = $number_votes_for_response_by_user;
$contest->number_total_votes_by_user = $number_total_votes_by_user;

// Set type of response
$contest->response_type = $input_response_type;
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
// Set subgroups
if ($container instanceof ElggGroup) {
   if (strcmp($subgroups,"on")==0) {
      $contest->subgroups=true;
      $contest->who_answers='subgroup';
   } else {
      $contest->subgroups=false;
      $contest->who_answers='member';
   }
} else {
   $contest->subgroups=false;
   $contest->who_answers='member';
}

//Set type_grading
if ($container instanceof ElggGroup) {
   if (strcmp($contest_with_gamepoints,"on")==0) {
      $contest->contest_with_gamepoints = true;
      $contest->total_gamepoints = $total_gamepoints;
      $contest->option_type_grading_value = $option_type_grading_value;
      if (strcmp($option_type_grading_value,'contest_type_grading_percentage')==0){
         $contest->number_winners_type_grading_percentage = $number_winners_type_grading_percentage;
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

// Remove the contest post cache
elgg_clear_sticky_form('add_contest');

// System message
system_message(elgg_echo("contest:created"));
//River
elgg_create_river_item(array(
      'view'=>'river/object/contest/create',
      'action_type'=>'create',
      'subject_guid'=>$user_guid,
      'object_guid'=>$contestpost,
));

//Events using the event_manager plugin if it is active
if (elgg_is_active_plugin('event_manager')){

   // End response time
   $event = new Event();
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

   // added because we need an update event
   if ($event->save()){
      $event_guid = $event->getGUID();
      $contest->event_guid = $event_guid;
   }
   else
      register_error(elgg_echo("contest:event_manager_error_save"));

   // End voting time
   $event_voting = new Event();
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

   // added because we need an update event
   if ($event_voting->save()){
      $event_voting_guid = $event_voting->getGUID();
      $contest->event_voting_guid = $event_voting_guid;
   }
   else
      register_error(elgg_echo("contest:event_manager_error_save"));
}

//Forward
if ($container instanceof ElggGroup) {
   forward(elgg_get_site_url() . "contest/group/" . $container->getGUID());
} else {
   $owner = get_entity($contest->getOwnerGUID());
   forward(elgg_get_site_url() . "contest/owner/" . $owner->username);
}

?>
