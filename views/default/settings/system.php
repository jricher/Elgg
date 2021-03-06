<?php

	/**
	 * Elgg system settings form
	 * The form to change system settings
	 * 
	 * @package Elgg
	 * @subpackage Core

	 * @author Curverider Ltd

	 * @link http://elgg.org/
	 * 
	 * @uses $vars['action'] If set, the place to forward the form to (usually action/systemsettings/save)
	 */

	// Set action appropriately
		if (!isset($vars['action'])) {
			$action = $vars['url'] . "action/systemsettings/save";
		} else {
			$action = $vars['action'];
		}
		
		$form_body = "";
		foreach(array('sitename','sitedescription', 'siteemail', 'wwwroot','path','dataroot', 'view') as $field) {
			$form_body .= "<p>";
			$form_body .= elgg_echo('installation:' . $field) . "<br />";
			$warning = elgg_echo('installation:warning:' . $field);
			if ($warning != 'installation:warning:' . $field) echo "<b>" . $warning . "</b><br />";
			$value = $vars['config']->$field;
			$form_body .= elgg_view("input/text",array('internalname' => $field, 'value' => $value));
			$form_body .= "</p>";
		}
		
		$languages = get_installed_translations();
		$form_body .= "<p>" . elgg_echo('installation:language') . elgg_view("input/pulldown", array('internalname' => 'language', 'value' => $vars['config']->language, 'options_values' => $languages)) . "</p>";
		
		$form_body .= "<p class=\"admin_debug\">" . elgg_echo('admin:site:access:warning') . "<br />";
		$form_body .= elgg_echo('installation:sitepermissions') . elgg_view('input/access', array('internalname' => 'default_access','value' => $vars['config']->default_access)) . "</p>";
		$form_body .= "<p class=\"admin_debug\">" . elgg_echo('installation:allow_user_default_access:description') . "<br />" .elgg_view("input/checkboxes", array('options' => array(elgg_echo('installation:allow_user_default_access:label')), 'internalname' => 'allow_user_default_access', 'value' => ($vars['config']->allow_user_default_access ? elgg_echo('installation:allow_user_default_access:label') : "") )) . "</p>";
		$form_body .= "<p class=\"admin_debug\">" . elgg_echo('installation:simplecache:description') . "<br />" .elgg_view("input/checkboxes", array('options' => array(elgg_echo('installation:simplecache:label')), 'internalname' => 'simplecache_enabled', 'value' => ($vars['config']->simplecache_enabled ? elgg_echo('installation:simplecache:label') : "") )) . "</p>";
		$form_body .= "<p class=\"admin_debug\">" . elgg_echo('installation:viewpathcache:description') . "<br />" .elgg_view("input/checkboxes", array('options' => array(elgg_echo('installation:viewpathcache:label')), 'internalname' => 'viewpath_cache_enabled', 'value' => (($vars['config']->viewpath_cache_enabled) ? elgg_echo('installation:viewpathcache:label') : "") )) . "</p>";
		
		$form_body .= "<p class=\"admin_debug\">" . elgg_echo('installation:debug') . "<br />" .elgg_view("input/checkboxes", array('options' => array(elgg_echo('installation:debug:label')), 'internalname' => 'debug', 'value' => ($vars['config']->debug ? elgg_echo('installation:debug:label') : "") )) . "</p>";
		
		$form_body .= "<p class=\"admin_debug\">" . elgg_echo('installation:httpslogin') . "<br />" .elgg_view("input/checkboxes", array('options' => array(elgg_echo('installation:httpslogin:label')), 'internalname' => 'https_login', 'value' => ($vars['config']->https_login ? elgg_echo('installation:httpslogin:label') : "") )) . "</p>";
		
		
		$form_body .= "<p class=\"admin_debug\">" . elgg_echo('installation:disableapi') . "<br />";
		$on = elgg_echo('installation:disableapi:label');
		if ((isset($CONFIG->disable_api)) && ($CONFIG->disable_api == true))
			$on = ($vars['config']->disable_api ?  "" : elgg_echo('installation:disableapi:label'));
		$form_body .= elgg_view("input/checkboxes", array('options' => array(elgg_echo('installation:disableapi:label')), 'internalname' => 'api', 'value' => $on ));
		$form_body .= "</p>";
		
		$form_body .= "<p class=\"admin_usage\">" . elgg_echo('installation:usage') . "<br />";
		$on = elgg_echo('installation:usage:label');
		
		if (isset($CONFIG->ping_home))
			$on = ($vars['config']->ping_home!='disabled' ? elgg_echo('installation:usage:label') : "");
		$form_body .= elgg_view("input/checkboxes", array('options' => array(elgg_echo('installation:usage:label')), 'internalname' => 'usage', 'value' => $on )); 
		$form_body .= "</p>";
		
		
		
		$form_body .= elgg_view('input/hidden', array('internalname' => 'settings', 'value' => 'go'));
		
		$form_body .= elgg_view('input/submit', array('value' => elgg_echo("save")));
		
		echo elgg_view('input/form', array('action' => $action, 'body' => $form_body));
		
?>
