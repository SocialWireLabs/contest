<?php

gatekeeper();
if (is_callable('group_gatekeeper')) 
   group_gatekeeper();

$user_guid = elgg_get_logged_in_user_guid();

$owner = elgg_get_page_owner_entity();
if (!$owner) {
   forward('contest/all');
}
$owner_guid = $owner->getGUID();

elgg_push_breadcrumb($owner->name);

$group_owner_guid = $owner->owner_guid;

$operator=false;
if (($group_owner_guid==$user_guid)||(check_entity_relationship($user_guid,'group_admin',$owner_guid))){
   $operator=true;
}

if ($operator)
   elgg_register_title_button('contest','add');

$offset = get_input('offset');
if (empty($offset)) {
   $offset = 0;
}
$limit = 10;

$contests = elgg_get_entities(array('type'=>'object','subtype'=>'contest','limit'=>false,'container_guid'=>$owner_guid,'order_by'=>'e.time_created desc'));

if (empty($contests)) {
   $num_contests=0;
} else {
   $num_contests=count($contests);
}

$k=0;
$item=$offset;
$contests_range=array();
while (($k<$limit)&&($item<$num_contests)){
   $contests_range[$k]=$contests[$item];
   $k=$k+1;
   $item=$item+1;
}

if ($num_contests>0){	
   $vars=array('count'=>$num_contests,'limit'=>$limit,'offset'=>$offset,'full_view'=>false);
   $content .= elgg_view_entity_list($contests_range,$vars);
} else {
   $content .= '<p>' . elgg_echo('contest:none') . '</p>';
}

$title = elgg_echo('contest:user', array($owner->name));

$filter_context = '';
if ($owner_guid == $user_guid) {
   $filter_context = 'mine';
}
					
$params = array('filter_context' => $filter_context,'content' => $content,'title' => $title);

if (elgg_instanceof($owner, 'group')) {
   $params['filter'] = '';
}

$body = elgg_view_layout('content', $params);
echo elgg_view_page($title, $body);
		
?>