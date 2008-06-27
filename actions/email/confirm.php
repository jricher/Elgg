<?php
	/**
	 * Action which confirms an email when it is registered or changed, based on a code.
	 * 
	 * @package Elgg
	 * @subpackage Core
	 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
	 * @author 
	 * @copyright Curverider Ltd 2008
	 * @link http://elgg.org/
	 */

	require_once(dirname(dirname(dirname(__FILE__))) . "/engine/start.php");
	global $CONFIG;

	// Get user id
	$user_guid = (int)get_input('u');
	$user = get_entity($user_guid);
	
	// And the code
	$code = sanitise_string(get_input('c'));
	
	if ( ($code) && ($user) )
	{
		if (validate_email($user_guid, $code)) {
			system_message(elgg_echo('email:confirm:success'));
		
			$user = get_entity($user_guid);
			notify_user($user_guid, $CONFIG->site->guid, elgg_echo('email:validate:success:subject'), sprintf(elgg_echo('email:validate:success:body'), $user->username), NULL, 'email');
			
		} else
			system_message(elgg_echo('email:confirm:fail'));
	}
	else
		system_message(elgg_echo('email:confirm:fail'));
	
	forward($_SERVER['HTTP_REFERER']);
	exit;

?>