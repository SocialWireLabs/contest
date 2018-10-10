<?php

/**
* Override the ElggFile so that
*/
        
class QuestionsContestPluginFile extends ElggFile
{
        protected function initialiseAttributes()
        {
                parent::initialise_attributes();
                $this->attributes['subtype'] = "contest_question_file";
                $this->attributes['class'] = "ElggFile";
        }

        public function __construct($guid = null)
        {
                if ($guid && !is_object($guid)) {
                  // Loading entities via __construct(GUID) is deprecated, so we give it the entity row and the
                  // attribute loader will finish the job. This is necessary due to not using a custom
                  // subtype (see above).
                  $guid = get_entity_as_row($guid);
                }
                parent::__construct($guid);
        }
}

class ResponsesContestPluginFile extends ElggFile
{
        protected function initialiseAttributes()
        {
                parent::initialise_attributes();
                $this->attributes['subtype'] = "contest_response_file";
                $this->attributes['class'] = "ElggFile";
        }

        public function __construct($guid = null)
        {
                if ($guid && !is_object($guid)) {
                  // Loading entities via __construct(GUID) is deprecated, so we give it the entity row and the
                  // attribute loader will finish the job. This is necessary due to not using a custom
                  // subtype (see above).
                  $guid = get_entity_as_row($guid);
                }
                parent::__construct($guid);
        }
}

function contest_init() {

   $item = new ElggMenuItem('contest', elgg_echo('contests'), 'contest/all');
   elgg_register_menu_item('site', $item);
  
   // Extend system CSS with our own styles, which are defined in the contest/css view
   elgg_extend_view('css/elgg','contest/css');
								
   // Register a page handler, so we can have nice URLs
   elgg_register_page_handler('contest','contest_page_handler');
				
   // Register entity type
   elgg_register_entity_type('object','contest');

   // Register a URL handler for contest posts
   elgg_register_plugin_hook_handler('entity:url', 'object', 'contest_url');

    // Register a URL handler for contest_answer posts
   elgg_register_plugin_hook_handler('entity:url', 'object', 'contest_answer_url');

   // Not comments to river for contest_answer objects
  // elgg_register_plugin_hook_handler('creating', 'river', 'contest_answer_not_comments_to_river');

   // Show contests in groups
   add_group_tool_option('contest',elgg_echo('contest:enable_group_contests'),false);
   elgg_extend_view('groups/tool_latest', 'contest/group_module');

   // Add a menu item to the user ownerblock
   elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'contest_owner_block_menu');

   // Advanced permissions
   elgg_register_plugin_hook_handler('permissions_check', 'object', 'contest_permissions_check');

   // Register library
   elgg_register_library('contest', elgg_get_plugins_path() . 'contest/lib/contest_lib.php');

   run_function_once("contest_question_file_add_subtype_run_once");  
   run_function_once("contest_response_file_add_subtype_run_once");  

}

function contest_question_file_add_subtype_run_once(){
   add_subtype("object","contest_question_file","QuestionsContestPluginFile");
}

function contest_response_file_add_subtype_run_once(){
   add_subtype("object","contest_response_file","ResponsesContestPluginFile");
}

function contest_permissions_check($hook, $type, $return, $params) {
   if (($params['entity']->getSubtype() == 'contest')||($params['entity']->getSubtype() == 'contest_question')||($params['entity']->getSubtype() == 'contest_question_file')||($params['entity']->getSubtype() == 'contest_answer')||($params['entity']->getSubtype() == 'contest_response_file')) {
      $user_guid = elgg_get_logged_in_user_guid();
      $group_guid = $params['entity']->container_guid;
      $group = get_entity($group_guid);
      if ($group instanceof ElggGroup) {
         $group_owner_guid = $group->owner_guid;
         $operator=false;
         if (($group_owner_guid==$user_guid)||(check_entity_relationship($user_guid,'group_admin',$group_guid))){
            $operator=true;
         }
         if ($operator){
            return true;
	 }   
      }
   }
}


function contest_answer_not_comments_to_river($hook, $type, $params, $return) {
   if ($params['subtype'] == 'contest_answer'){
      return false;
   }    
}

/**
 * Add a menu item to the user ownerblock
*/
function contest_owner_block_menu($hook, $type, $return, $params) {
   if (elgg_instanceof($params['entity'], 'user')) {
      $url = "contest/owner/{$params['entity']->username}";
      $item = new ElggMenuItem('contest', elgg_echo('contests'), $url);
      $return[] = $item;
   } else {
      if ($params['entity']->contest_enable != "no") {
         $url = "contest/group/{$params['entity']->guid}/all";
         $item = new ElggMenuItem('contest', elgg_echo('contest:group'), $url);
         $return[] = $item;
      }
   }
   return $return;
}

/**
* Contest page handler; allows the use of fancy URLs
*
* @param array $page From the page_handler function
* @return true|false Depending on success
*/
function contest_page_handler($page) {
   if (isset($page[0])) {
      elgg_push_breadcrumb(elgg_echo('contests'));
      $base_dir = elgg_get_plugins_path() . 'contest/pages/contest';
      switch ($page[0]) {
         case "view":
            set_input('contestpost', $page[1]);
	    set_input('order_by', $page[2]);
            include "$base_dir/read.php";
            break;
         case "owner":
            set_input('username', $page[1]);
            include "$base_dir/index.php";
            break;
         case "group":
            set_input('container_guid', $page[1]);
            include "$base_dir/index.php";
            break;
         case "friends":
            include "$base_dir/friends.php";
            break;
         case "all":
            include "$base_dir/everyone.php";
            break;
         case "add":
            set_input('container_guid', $page[1]);
            include "$base_dir/add.php";
            break;
         case "edit":
            set_input('contestpost', $page[1]);
            include "$base_dir/edit.php";
            break;
	 case "answer":
            set_input('contestpost', $page[1]);
	          set_input('answerpost', $page[2]);
	          set_input('order_by', $page[3]);
            include "$base_dir/answer.php";
            break;
	 case "show_answer":
            set_input('contestpost', $page[1]);
	          set_input('answerpost', $page[2]);
	          set_input('order_by', $page[3]);
            include "$base_dir/show_answer.php";
            break;
         default:
            return false;
      }
   } else {
      forward();
   }
   return true;
}

/**
 * Returns the URL from a contest entity
 *
 * @param string $hook   'entity:url'
 * @param string $type   'object'
 * @param string $url    The current URL
 * @param array  $params Hook parameters
 * @return string
 */
function contest_url($hook, $type, $url, $params) {
   $contest = $params['entity'];
   // Check that the entity is a contest object
   if ($contest->getSubtype() !== 'contest') {
        // This is not a contest object, so there's no need to go further
        return;
   }
   $title = elgg_get_friendly_title($contest->title);
   return $url . "contest/view/" . $contest->getGUID() . "/" . $title;
}

/**
 * Returns the URL from a contest_answer entity
 *
 * @param string $hook   'entity:url'
 * @param string $type   'object'
 * @param string $url    The current URL
 * @param array  $params Hook parameters
 * @return string
 */
function contest_answer_url($hook, $type, $url, $params) {
    
   $contest_answer = $params['entity'];
   // Check that the entity is a contest_answer object
   if ($contest_answer->getSubtype() !== 'contest_answer') {
        // This is not a contest_answer object, so there's no need to go further
        return;
   }
   $options = array('relationship' => 'contest_answer', 'relationship_guid' => $contest_answer->getGUID(),'inverse_relationship' => true, 'type' => 'object', 'subtype' => 'contest');
   $contests=elgg_get_entities_from_relationship($options);
   if (!empty($contests)){
      $contest=$contests[0];
      $title = elgg_get_friendly_title($contest->title);
     
      return $url . "contest/view/" . $contest->getGUID() . "/" . $title;
    }
    else
      return false;
}

// Make sure the contest initialisation function is called on initialisation
elgg_register_event_handler('init','system','contest_init');
		
// Register actions
$action_base = elgg_get_plugins_path() . 'contest/actions/contest';
elgg_register_action("contest/add","$action_base/add.php");
elgg_register_action("contest/edit","$action_base/edit.php");
elgg_register_action("contest/delete","$action_base/delete.php");
elgg_register_action("contest/answer","$action_base/answer.php");
elgg_register_action("contest/delete_answer","$action_base/delete_answer.php");
elgg_register_action("contest/update_votes","$action_base/update_votes.php");
elgg_register_action("contest/vote","$action_base/vote.php");
elgg_register_action("contest/assign_game_points","$action_base/assign_game_points.php");
elgg_register_action("contest/close_answering","$action_base/close_answering.php");
elgg_register_action("contest/close_voting","$action_base/close_voting.php");
?>