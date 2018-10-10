<?php

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/engine/start.php');

// Get input data
$contestpost = $_POST['contestpost'];
$user_guid = $_POST['user_guid'];
$response_guid = $_POST['response_guid'];
$num_votes = $_POST['num_votes'];
$my_votes_before = $_POST['my_votes_before'];

$contest = get_entity($contestpost);
$container_guid = $contest->container_guid;
$container = get_entity($container_guid);
$response = get_entity($response_guid);

if (!empty($response)){
	$i = 0;
	if ($my_votes_before > $num_votes){
		// RESTAR
		$to_decrease = $my_votes_before - $num_votes;
		$all_response_votes = $response->getAnnotations(array('name'=>'vote'),99999,0,'desc');
		foreach ($all_response_votes as $item) {
			if ($item->value == $user_guid){
	 			$item->delete();
	 			$i++;
			}
			if ($i == $to_decrease)  {
				break;
			}   
		}
	}
	if ($my_votes_before < $num_votes){
	    // SUMAR
	    $to_increase = $num_votes - $my_votes_before; 
		for ($i=0; $i<$to_increase; $i++){
	   		$response->annotate('vote',$user_guid,$response->access_id,$user_guid);
		}
	}
}

?>