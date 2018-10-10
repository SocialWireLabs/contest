<?php

gatekeeper();
if (is_callable('group_gatekeeper')) 
   group_gatekeeper();

$contestpost = get_input('contestpost');
$contest = get_entity($contestpost);
$answerpost = get_input('answerpost');
$order_by = get_input('order_by');

$user_guid = elgg_get_logged_in_user_guid();
$user = get_entity($user_guid);

$container_guid = $contest->container_guid;
$container = get_entity($container_guid);

$page_owner = $container;
if (elgg_instanceof($container, 'object')) {
   $page_owner = $container->getContainerEntity();
}
elgg_set_page_owner_guid($page_owner->getGUID());

if (elgg_instanceof($container, 'group')) {
   elgg_push_breadcrumb($container->name, "contest/group/$container->guid/all");
} else {
   elgg_push_breadcrumb($container->name, "contest/owner/$container->username");
}
elgg_push_breadcrumb($contest->title, $contest->getURL());

if ($contest){
   $title = elgg_echo('contest:showanswerpost');
   $content = elgg_view('forms/contest/show_answer', array('entity' => $contest, 'answerpost' => $answerpost, 'order_by' => $order_by));
} 

$body = elgg_view_layout('content', array('filter' => '','content' => $content,'title' => $title));
echo elgg_view_page($title, $body);
		
?>