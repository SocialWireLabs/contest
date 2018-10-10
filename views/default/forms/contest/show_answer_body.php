<?php

$contest=$vars['contest'];
$question_body=$vars['question_body'];
$response_type=$vars['response_type'];
$response_text=$vars['response_text'];
switch ($response_type){
   case 'simple':
      $response_html=$vars['response_html'];
      break;
   case 'urls_files':
      $response_urls=$vars['response_urls'];
      $response_file_guids_array=$vars['response_file_guids_array'];
      break;
}


$form_body = "";

// Response

$form_body .= "<div class=\"contest_frame_green\">";

$form_body .= "<p><b>" . elgg_echo('contest:response_label') . "</p></b>";

if (strcmp($response_text,"")!=0) {
   $form_body .= "<p><b>" . elgg_echo('contest:response_text_label_read') . "</p></b>";
   $form_body .= "<div class=\"contest_question_frame\">";
   $form_body .=  elgg_view('output/text',array('value' => $response_text));
   $form_body .= "</div><br>";
}

if ((strcmp($response_type,"simple")==0)&&(strcmp($response_html,"")!=0)){
   $form_body .= "<p><b>" . elgg_echo('contest:response_html_label_read') . "</p></b>";
   $form_body .= "<div class=\"contest_question_frame\">";
   $form_body .=  elgg_view('output/longtext',array('value' => $response_html));
   $form_body .= "</div><br>";
}

if (strcmp($response_type,"urls_files")==0){
   $form_body .= "<p><b>" . elgg_echo('contest:response_urls_files_label') . "</p></b>";
   $form_body .= "<div class=\"contest_question_frame\">";

if ((count($response_urls)>0)&&(strcmp($response_urls[0],"")!=0)) {
      foreach ($response_urls as $one_url){
         $comp_url = explode(Chr(24),$one_url);
         $comp_url = array_map('trim',$comp_url);
         $url_name = $comp_url[0];
         $url_value = $comp_url[1];
         if (elgg_is_active_plugin("sw_embedlycards")){
           $form_body .= "<div>
           <a class='embedly-card' href='$url_value'></a>
           </div>";
         }
         else if (elgg_is_active_plugin("hypeScraper"))
           $form_body .= elgg_view('output/sw_url_preview', array('value' => $url_value,));
         else
          $form_body .= "<a rel=\"nofollow\" href=\"$url_value\" target=\"_blank\">$url_value</a><br>";
         }
   }
   if ((count($response_file_guids_array)>0)&&(strcmp($response_file_guids_array[0],"")!=0)){
      foreach($response_file_guids_array as $one_file_guid){
         $response_file=get_entity($one_file_guid);
	 $params = $one_file_guid . "_response";
   $icon = questions_set_icon_url($response_file, "small");
   $url_file = elgg_get_site_url()."mod/contest/download.php?params=$params";
   $trozos = explode(".", $response_file->title);
   $ext = strtolower(end($trozos));
   if (($ext == 'jpg') || ($ext == 'png') || ($ext == 'gif') || ($ext == 'tif') || ($ext == 'tiff') || ($ext =='jpeg'))
     $form_body .= "<p align=\"center\"><a href=\"".$url_file."\">"."<img src=\"" . $url_file . "\" width=\"600px\">"."</a></p>";
   else
     $form_body .= "<p><a href=\"".$url_file."\">"."<img src=\"" . elgg_get_site_url(). $icon . "\">".$response_file->title."</a></p>";

      }
   }
   $form_body .= "</div><br>";
}

$form_body .= "</div>";

$form_body .= "<br>";

echo elgg_echo($form_body);

?>
