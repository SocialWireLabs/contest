<?php

gatekeeper();

$contestpost = get_input('contestpost');
$contest = get_entity($contestpost);
$answerpost = get_input('answerpost');

if ($contest->getSubtype() == "contest") {
   $now=time();

   $opened=false;
   if (strcmp($contest->option_activate_value,'contest_activate_date')==0){
      if (($now>=$contest->activate_time)&&($now<$contest->close_time)){
         $opened=true;
      }
   } else {
      if ($now<$contest->close_time) {
         $opened=true;
      }
   }

   if ($opened){

      $container_guid  = $contest->container_guid;
      $container = get_entity($container_guid);

      if (strcmp($answerpost,"-1")!=0) {
         $user_response = get_entity($answerpost);
	 $user = $user_response->getOwnerEntity();
	 $user_guid = $user->getGUID();
      } else {
         $user_response = "";
	 $user_guid = elgg_get_logged_in_user_guid();
	 $user=get_entity($user_guid);
      }


      if ($container instanceof ElggGroup) {
         if ($contest->subgroups){
            $user_subgroup = elgg_get_entities_from_relationship(array('type_subtype_pairs' => array('group' => 'lbr_subgroup'),'container_guids' => $container_guid,'relationship' => 'member','inverse_relationship' => false,'relationship_guid' => $user_guid));
	    if ($user_subgroup) {
               $user_subgroup=$user_subgroup[0];
               $user_subgroup_guid=$user_subgroup->getGUID();
	    }
         }
      }

      $response_type=$contest->response_type;

      // Cache to the session
      elgg_make_sticky_form('answer_contest');

      //Response
      $response_text=get_input("response_text");
      if (strcmp($response_text,"")==0)
	 $response_text = Chr(2);

      if (strcmp($response_type,"simple")==0){
	 $response_html=get_input("response_html");
	 if (strcmp($response_html,"")==0) {
	     register_error(elgg_echo('contest:not_response'));
	     forward($_SERVER['HTTP_REFERER']);
	 }
	 $response = $response_html;
      }

      if (strcmp($response_type,"urls_files")==0) {
	 $response_urls = get_input('response_urls');
         $response_urls = array_map('trim',$response_urls);
	 $response_urls_names = get_input('response_urls_names');
         $response_urls_names = array_map('trim',$response_urls_names);
	 $url_failed=false;
	 if ((count($response_urls)>0)&&(strcmp($response_urls[0],"")!=0)) {
	    foreach ($response_urls as $one_url){
	       $xss_contest = "<a rel=\"nofollow\" href=\"$one_url\" target=\"_blank\">$one_url</a>";
               if ($xss_contest != filter_tags($xss_contest)) {
               	  $url_failed=true;
               }
	    }
	    $i=0;
            $comp_response_urls = "";
            foreach($response_urls as $one_url){
               if ($i!=0)
                  $comp_response_urls .= Chr(26);
	       if ($response_urls_names[$i]!="")
                  $comp_response_urls .= $response_urls_names[$i] . Chr(24) . $response_urls[$i];
	       else
	          $comp_response_urls .= $response_urls[$i] . Chr(24) . $response_urls[$i];
               $i=$i+1;
            }
	    $response = $comp_response_urls;
	 } else {
            $response = "";
	 }
	 if ($url_failed){
	    register_error(elgg_echo('contest:url_failed'));
	    forward($_SERVER['HTTP_REFERER']);
	 }
      }

  if (strcmp($response,"")!=0){
	    $this_response_content = $response_text . Chr(25) . $response;
         } else {
	    $this_response_content = $response_text . Chr(25) . Chr(2);
	 }

      $j=0;
      $file_save_well=true;
      $file_response_guid=array();
      $file_response_counter = count($_FILES['upload_response_file']['name']);

      if ((strcmp($response_type,"urls_files")==0)){
      	 if (!empty($user_response)) {
	    $previous_response_files = elgg_get_entities_from_relationship(array('relationship' => 'response_file_link','relationship_guid' => $user_response->getGUID(),'inverse_relationship' => false,'type' => 'object','subtype' => 'contest_response_file','limit'=>0));
	 }
      }

      if (strcmp($response_type,"urls_files")==0){

         $count_previous_response_files = 0;
	 $count_deleted_previous_response_files = 0;
	 foreach($previous_response_files as $one_file){
	    $count_previous_response_files = $count_previous_response_files + 1;
	    $value = get_input($one_file->getGUID());
            if($value == '1'){
	       $count_deleted_previous_response_files = $count_deleted_previous_response_files + 1;
	    }
	 }
         if ((($file_response_counter==0)||($_FILES['upload_response_file']['name'][0] == ""))&&((count($response_urls)==0)||(strcmp($response_urls[0],"")==0))&&($count_previous_response_files == $count_deleted_previous_response_files)) {
	     register_error(elgg_echo('contest:not_response'));
	     forward($_SERVER['HTTP_REFERER']);
	 }

      }

      if (((strcmp($response_type,"urls_files")==0))&&($file_response_counter>0)&&($_FILES['upload_response_file']['name'][0] != "")){
         $file_response_guids = "";
         for($k=0; $k<$file_response_counter; $k++){
            $file_response[$k] = new ResponsesContestPluginFile();
            $file_response[$k]->subtype = "contest_response_file";
            $prefix = "file/";
            $filestorename = elgg_strtolower(time().$_FILES['upload_response_file']['name'][$k]);
	    $file_response[$k]->setFilename($prefix.$filestorename);
            $file_response[$k]->setMimeType($_FILES['upload_response_file']['type'][$k]);
            $file_response[$k]->originalfilename = $_FILES['upload_response_file']['name'][$k];
            $file_response[$k]->simpletype = elgg_get_file_simple_type($_FILES['upload_response_file']['type'][$k]);
	    $file_response[$k]->open("write");
	    if (isset($_FILES['upload_response_file']) && isset($_FILES['upload_response_file']['error'][$k])) {
               $uploaded_file = file_get_contents($_FILES['upload_response_file']['tmp_name'][$k]);
            } else {
               $uploaded_file = false;
            }
            $file_response[$k]->write($uploaded_file);
            $file_response[$k]->close();
            $file_response[$k]->title = $_FILES['upload_response_file']['name'][$k];
	    $file_response[$k]->owner_guid = $user_guid;
	    if ($container instanceof ElggGroup) {
	          $file_response[$k]->access_id = $contest->access_id;
	       if ($contest->subgroups) {
	          if ($user_subgroup)
	             $file_response[$k]->container_guid = $user_subgroup_guid;
		  else
		     $file_response[$k]->container_guid = $container_guid;
	       } else {
	          $file_response[$k]->container_guid = $container_guid;
	       }
	    } else {
	       $file_response[$k]->access_id = $contest->access_id;
	       $file_response[$k]->container_guid = $container_guid;
	    }
            $file_response_save = $file_response[$k]->save();
	    if (!$file_response_save) {
	       $file_save_well=false;
	       break;
	    } else {
	       $file_response_guid[$j] = $file_response[$k]->getGUID();
	       if ($k==0)
	          $file_response_guids .= $file_response[$k]->getGUID();
	       else
	          $file_response_guids .= "," . $file_response[$k]->getGUID();
	       $j=$j+1;
	    }
         }
      }

      if (!$file_save_well){
         foreach($file_response_guid as $one_file_guid){
	    $one_file=get_entity($one_file_guid);
	    $deleted=$one_file->delete();
	    if (!$deleted){
	       register_error(elgg_echo('contest:filenotdeleted'));
	       forward($_SERVER['HTTP_REFERER']);
	    }
	 }
	 register_error(elgg_echo('contest:file_error_save'));
	 forward($_SERVER['HTTP_REFERER']);
      }

      $found=false;
      if (!empty($user_response)) {
	    //Answer content
	    $user_response->answer_time=$now;
            $user_response->content=$this_response_content;
	    $found=true;
      }

      if (!$found){
	 // Initialise a new ElggObject to be the answer
	 $answer = new ElggObject();
	 $answer->subtype = "contest_answer";
	 $answer->owner_guid = $user_guid;
	 if ($container instanceof ElggGroup) {
	       $answer->access_id = $contest->access_id;
	    if ($contest->subgroups){
	       if ($user_subgroup) {
	          $answer->container_guid = $user_subgroup_guid;
	          $answer->who_answers = 'subgroup';
	       } else {
	          $answer->container_guid = $container_guid;
	          $answer->who_answers = 'member';
	       }
	    } else {
	       $answer->container_guid = $container_guid;
	       $answer->who_answers = 'member';
	    }
	 } else {
	    $answer->access_id = $contest->access_id;
	    $answer->container_guid = $container_guid;
	    $answer->who_answers = 'member';
	 }
	 if (!$answer->save()){
	    foreach($file_response_guid as $one_file_guid){
	       $one_file=get_entity($one_file_guid);
	       $deleted=$one_file->delete();
	       if (!$deleted){
	          register_error(elgg_echo('contest:filenotdeleted'));
                  forward($_SERVER['HTTP_REFERER']);
	       }
	    }
	    register_error(elgg_echo("contest:answer_error_save"));
	    forward($_SERVER['HTTP_REFERER']);
	 }
	 //Answer content
	 $answer->answer_time = $now;
	 $answer->content = $this_response_content;
	 add_entity_relationship($contestpost,'contest_answer',$answer->getGUID());
         $contest->annotate('all_responses', "1", $contest->access_id);
      }

      if ((strcmp($response_type,"urls_files")==0)){
      	 if (!empty($user_response)) {
	          foreach($previous_response_files as $one_file){
	             $value = get_input($one_file->getGUID());
                  if($value == '1'){
	                   $file1 = get_entity($one_file->getGUID());
                     $deleted=$file1->delete();
	                if (!$deleted){
	                   register_error(elgg_echo('contest:filenotdeleted'));
                     forward($_SERVER['HTTP_REFERER']);
	                  }
                  }
	          }
	        }

	     $file_response_guids_array=explode(",",$file_response_guids);
         foreach($file_response_guids_array as $one_file_guid){
	    if (!$found) {
	       add_entity_relationship($answer->getGUID(),'response_file_link',$one_file_guid);
	    } else {
	       add_entity_relationship($user_response->getGUID(),'response_file_link',$one_file_guid);
            }
	      }
      }

      // Remove the contest post cache
      elgg_clear_sticky_form('answer_contest');

      if (!$found) {
         elgg_create_river_item(array(
            'view'=>'river/object/contest_answer/create',
            'action_type'=>'create',
            'subject_guid'=>$user_guid,
            'object_guid'=>$answer->getGUID(),
         ));
	 }else {
	  elgg_create_river_item(array(
            'view'=>'river/object/contest_answer/update',
            'action_type'=>'update',
            'subject_guid'=>$user_guid,
            'object_guid'=>$user_response->getGUID(),
         ));
	 }

      system_message(elgg_echo("contest:answered"));
      forward("contest/view/$contestpost");

   } else {
      system_message(elgg_echo("contest:closed"));
      forward($_SERVER['HTTP_REFERER']);
   }
}


?>
