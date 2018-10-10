<?php

$contest=$vars['contest'];
$question_body=$vars['question_body'];
$response_type=$vars['response_type'];
$response_text=$vars['response_text'];
switch($response_type){
   case 'simple':
      $response_html=$vars['response_html'];
      break;
   case 'urls_files':
      $response_urls=$vars['response_urls'];
      $response_file=$vars['response_file'];
      $response_files=$vars['response_files'];
      break;
}

if (strcmp($question_body,"")!=0){
   ?>
   <div class="contest_frame_blue">
   <?php
   //Question body
   if (strcmp($question_body,"")!=0){
      echo $question_body;
      echo "<br>";
   }
   ?>
   </div>
   <br>
   <?php
}
?>

<div class="contest_frame_green">
<?php
//Response
?>

<?php
switch($response_type){
   case 'simple':
      ?>
      <p><b><?php echo elgg_echo("contest:response_html_label"); ?></b></p>
      <p><?php echo $response_html; ?></p>
      <?php
   break;
   case 'urls_files':
      ?>
      <p><b><?php echo elgg_echo("contest:response_urls_label"); ?></b></p>
      <p><?php echo $response_urls; ?></p>

      <p><b><?php echo elgg_echo("contest:response_files_label"); ?></b></p>
      <p><?php echo $response_file; ?></p>
      <?php
      if ($response_files){
         if ((count($response_files)>0)&&(strcmp($response_files[0]->title,"")!=0)){
            foreach($response_files as $file) {
	       ?>
               <div class="file_wrapper">
	       <a class="bold" onclick="changeFormValue(<?php echo $file->getGUID(); ?>), changeImage(<?php echo $file->getGUID(); ?>)">
               <img id ="image_<?php echo $file->getGUID(); ?>" src="<?php echo elgg_get_site_url(); ?>mod/contest/graphics/tick.jpeg">
               </a>
               <span><?php echo $file->title ?></span>
               <?php
               echo elgg_view("input/hidden",array('name' => $file->getGUID(), 'internalid'=> $file->getGUID(), 'value' => '0'));
               ?>
               </div>
	       <br>
               <?php
            }
         }
      }
   break;
}
?>
<br>

</div>
<br>

<script type="text/javascript">

    function changeImage(num) {
        if (document.getElementById('image_'+num).src == "<?php echo elgg_get_site_url(); ?>mod/contest/graphics/tick.jpeg")
            document.getElementById('image_'+num).src = "<?php echo elgg_get_site_url(); ?>mod/contest/graphics/delete_file.jpeg";
        else
            document.getElementById('image_'+num).src = "<?php echo elgg_get_site_url(); ?>mod/contest/graphics/tick.jpeg";
    }

</script>
