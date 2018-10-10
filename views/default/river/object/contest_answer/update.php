<?php
    $object = $vars['item']->getObjectEntity();
    $user = $object->getOwnerEntity();
    $user_guid = $user->getGUID();
    $object_guid = $object->getGUID();
    $options = array('relationship' => 'contest_answer', 'relationship_guid' => $object_guid,'inverse_relationship' => true, 'type' => 'object', 'subtype' => 'contest');
    $contests=elgg_get_entities_from_relationship($options);
    if (!empty($contests)){
      $contest=$contests[0];
      $contest_guid = $contest->getGUID();
      $title = $contest->title;
      $url_text = elgg_echo("contest:response");
      $url = elgg_get_site_url() . "contest/show_answer/" . $contest_guid . "/" . $object_guid;
      $url_link = "<a href=\"{$url}\">{$url_text}</a>";
      $url_user_text = $user->name;
      $url_user = elgg_get_site_url() . "profile/" . $url_user_text;
      $url_user_link = "<a href=\"{$url_user}\">{$url_user_text}</a>";	
      $url_link_new = "<a href=\"{$url}\">{$title}</a>";
    }
    else {
       $title = "";
       $url = "";
    }
    $summary = sprintf(elgg_echo('river:update:object:contest_answer'),$url_user_link,$url_link_new);
    echo elgg_view('river/elements/layout', array('item' => $vars['item'],'summary' => $summary));
    //echo elgg_view('river/elements/layout', array('item' => $vars['item']));
?>
