<?php

gatekeeper();
if (is_callable('group_gatekeeper')) 
   group_gatekeeper();

$contestpost = get_input('contestpost');
$contest = get_entity($contestpost);
$offset = get_input('offset');
$order_by = get_input('order_by');

if ($contest) {
   elgg_set_page_owner_guid($contest->getContainerGUID());
   $container = elgg_get_page_owner_entity();

   if (elgg_instanceof($container, 'group')) {
      elgg_push_breadcrumb($container->name, "contest/group/$container->guid/all");
   } else {
      elgg_push_breadcrumb($container->name, "contest/owner/$container->username");
   }
   elgg_push_breadcrumb($contest->title);

   $title = elgg_echo('contest:readpost');		

   $content = elgg_view('object/contest',array('full_view' => true, 'entity' => $contest, 'offset' => $offset, 'order_by' => $order_by));

   $body = elgg_view_layout('content', array('filter' => '','content' => $content,'title' => $title));
   
   echo elgg_view_page($title, $body);
} else {
   register_error( elgg_echo('contest:notfound'));
   forward();
}

		
?>