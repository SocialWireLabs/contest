<?php

function sort_responses_by_votes(&$responses_sorted_by_votes,$a,$b){
   $from = $a;
   $to = $b;
   $temp=$responses_sorted_by_votes[($from+$to)/2];
   $pivot=$temp->countAnnotations('vote');
   do {
      $temp=$responses_sorted_by_votes[$from];
      $votes=$temp->countAnnotations('vote');
      while($votes>$pivot){
         $from=$from+1;
	 $temp=$responses_sorted_by_votes[$from];
	 $votes=$temp->countAnnotations('vote');
      }
      $temp=$responses_sorted_by_votes[$to];
      if ($temp instanceof ElggObject)
      $votes=$temp->countAnnotations('vote');
      while($votes<$pivot){
         $to=$to-1;
         $temp=$responses_sorted_by_votes[$to];
	 $votes=$temp->countAnnotations('vote');
      }
      if ($from<=$to){
         $response_aux=$responses_sorted_by_votes[$from];
	 $responses_sorted_by_votes[$from]=$responses_sorted_by_votes[$to];
	 $responses_sorted_by_votes[$to]=$response_aux;
	 $from=$from+1;
	 $to=$to-1;
      }
   }while($from<=$to);
   if ($a<$to)
      sort_responses_by_votes($responses_sorted_by_votes,$a,$to);
   if ($from<$b)
      sort_responses_by_votes($responses_sorted_by_votes,$from,$b);	
   return;
}

?>