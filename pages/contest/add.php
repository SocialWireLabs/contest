<?php

gatekeeper();
if (is_callable('group_gatekeeper')) 
   group_gatekeeper();

$container_guid = (int) get_input('container_guid');
$container = get_entity($container_guid);

$question_type = get_input('question_type');
$page_owner = $container;
if (elgg_instanceof($container, 'object')) {
   $page_owner = $container->getContainerEntity();
}
elgg_set_page_owner_guid($page_owner->getGUID());

elgg_push_breadcrumb(elgg_echo('add'));

$title = elgg_echo('contest:addpost');
$content = elgg_view('forms/contest/add', array('container_guid' => $container_guid, 'question_type' => $question_type));
$body = elgg_view_layout('content', array('filter' => '','content' => $content,'title' => $title));
echo elgg_view_page($title, $body);

?>