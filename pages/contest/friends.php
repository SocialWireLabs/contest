<?php

$owner = elgg_get_page_owner_entity();
if (!$owner) {
   forward('contest/all');
}

elgg_push_breadcrumb($owner->name, "contest/owner/$owner->username");
elgg_push_breadcrumb(elgg_echo('friends'));

elgg_register_title_button();

$offset = get_input('offset');          
if (empty($offset)) {
   $offset = 0;
}
$limit = 10;
      
$contests = elgg_get_entities_from_relationship(array(
   'type'=>'object',
   'subtype'=>'contest',
   'limit'=>false,
   'offset'=>0,
   'relationship'=>'friend',
   'relationship_guid'=>$owner->getGUID(),
   'relationship_join_on'=>'container_guid'
));

if ($contests) {
   $num_contests = count($contests);
} else {
   $num_contests = 0;
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
   $content = elgg_echo('contest:none');
}

$title = elgg_echo('contest:user:friends',array($owner->name));

$params = array('filter_context' => 'friends','content' => $content,'title' => $title);

$body = elgg_view_layout('content', $params);

echo elgg_view_page($title, $body);

?>