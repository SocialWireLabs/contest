<?php

elgg_load_library('contest');

$full = elgg_extract('full_view', $vars, FALSE);
$contest = elgg_extract('entity', $vars, FALSE);
if (!$contest) {
   return TRUE;
}

$owner = $contest->getOwnerEntity();
$owner_icon = elgg_view_entity_icon($owner, 'tiny');
$owner_link = elgg_view('output/url', array('href' => $owner->getURL(),'text' => $owner->name,'is_trusted' => true));
$author_text = elgg_echo('byline', array($owner_link));
$tags = elgg_view('output/tags', array('tags' => $contest->tags));
$date = elgg_view_friendly_time($contest->time_created);
$metadata = elgg_view_menu('entity', array('entity' => $contest,'handler' => 'contest','sort_by' => 'priority','class' => 'elgg-menu-hz'));
$subtitle = "$author_text $date $comments_link";

//
$owner_guid = $owner->getGUID();
$container_guid = $contest->container_guid;
$container = get_entity($container_guid);
$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);
//

$contestpost = $contest->getGUID();

$now=time();

//
$operator=false;

if($container instanceof ElggGroup){
   
   $group_guid=$contest->container_guid;
   $group = get_entity($group_guid);
   $group_owner_guid = $group->owner_guid;

   if (($owner_guid==$user_guid)||($group_owner_guid==$user_guid)||(check_entity_relationship($user_guid,'group_admin',$group_guid))){
      $operator=true;
   }

}

$answering_opened = false;
$voting_opened = false;

if(strcmp($contest->option_activate_value,'contest_activate_date') == 0){

   if(($now>=$contest->activate_time) && ($now<$contest->close_time)){
      $answering_opened = true;
   }

}
else{

   if(($now<$contest->close_time)){
      $answering_opened = true;
   }

}

if(((($now>=$contest->activate_time)&&(strcmp($contest->option_opened_voting_value,'contest_opened_voting_while_answering')==0))||(($now>$contest->close_time)&&(strcmp($contest->option_opened_voting_value,'contest_opened_voting_while_answering')!=0)))&&($now<$contest->close_time_voting)){
   $voting_opened = true;
}


if(($contest->canEdit()) && ($operator)){
   if($answering_opened){
      $url_close = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/contest/close_answering?edit=no&contestpost=" . $contestpost);
      $word_close = elgg_echo("contest:close_in_listing");
      $link_open_close = "<a href=\"{$url_close}\">{$word_close}</a>";
   }

   if((!$answering_opened) && ($voting_opened)){
      $url_close_voting = elgg_add_action_tokens_to_url(elgg_get_site_url() . "action/contest/close_voting?edit=no&contestpost=" . $contestpost);
      $word_close_voting = elgg_echo("contest:close_voting_in_listing");
      $link_open_close_voting = "<a href=\"{$url_close_voting}\">{$word_close_voting}</a>";
   }

}

if(!$answering_opened){
   if (elgg_is_active_plugin('event_manager')){
            $event_guid=$contest->event_guid;
            if ($event=get_entity($event_guid)){
               if($now>$contest->close_time){
                  $deleted=$event->delete();
               }
            }
   }
}

if(!$voting_opened){
   if (elgg_is_active_plugin('event_manager')){
            $event_guid=$contest->event_voting_guid;
            if ($event=get_entity($event_guid)){
               if($now>$contest->close_time_voting){
                  $deleted=$event->delete();
               }
            }
   }
}
//

if (($now>=$contest->activate_time)&&($now<$contest->close_time_voting)) {
   if ($now<$contest->close_time) {
      $title="<div class=\"contest_title\"><a class=\"opened_title_contest\" href=\"{$contest->getURL()}\">{$contest->title}</a></div>";
   } else {
      $title="<div class=\"contest_title\"><a class=\"opened_voting_title_contest\" href=\"{$contest->getURL()}\">{$contest->title}</a></div>";
   }
} else {
   $title="<div class=\"contest_title\"><a class=\"closed_title_contest\" href=\"{$contest->getURL()}\">{$contest->title}</a></div>";
}

if ($full) {
   
   $params = array('entity' => $contest,'title' => $title,'metadata' => $metadata,'subtitle' => $subtitle,'tags' => $tags);
   $params = $params + $vars;
   $summary = elgg_view('object/elements/summary', $params);
	
   $body = "<br>";

   if(($contest->canEdit())&&($operator)){
      if($answering_opened){
         $body .= $link_open_close . "<br>";
      }
      else{
         if($voting_opened){
            $body .= $link_open_close_voting . "<br>";
         }
      }
   }
	 
   $body .= elgg_view('contest/show_answers', array('entity' => $contest, 'offset' => $vars['offset'], 'order_by' => $vars['order_by']));
   
   echo elgg_view('object/elements/full', array('summary' => $summary,'icon' => $owner_icon,'body' => $body));

} else {

   $params = array('entity' => $contest,'title' => $title,'metadata' => $metadata,'subtitle' => $subtitle,'tags' => $tags);
   $params = $params + $vars;
   $list_body = elgg_view('object/elements/summary', $params);

   if(($contest->canEdit()) && ($operator)){

      if($answering_opened){
         $body = $link_open_close;
      }
      else{
         if($voting_opened){
            $body = $link_open_close_voting;
         }
      }

   }

   $list_body .= $body;

   echo elgg_view_image_block($owner_icon, $list_body);
}

?>
