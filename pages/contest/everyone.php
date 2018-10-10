<?php

$title = elgg_echo('contest:all');
elgg_pop_breadcrumb();
elgg_push_breadcrumb(elgg_echo('contests'));

elgg_register_title_button();

$offset = get_input('offset');          
if (empty($offset)) {
   $offset = 0;
}               
$limit = 10;

$contests = elgg_get_entities(array('type'=>'object','subtype'=>'contest','limit'=>false,'order_by'=>'e.time_created desc'));

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
   $content = '<p>' . elgg_echo('contest:none') . '</p>';
}

$body = elgg_view_layout('content', array('filter_context' => 'all','content' => $content,'title' => $title));

echo elgg_view_page($title, $body);

?>