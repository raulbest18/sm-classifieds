<?php
/**
* This file lets SMF know which hooks are going to be utilized
*
* LICENSE: MIT (http://opensource.org/licenses/mit-license.html)
*
* @category     Simple Machines
* @package      SM Classifieds
* @copyright    Copyright (c) 2014 Jason Clemons
* @license      MIT License
* @version      0.1
* @link         http://github.com/jasonclemons/SM-Classifieds
* @since        File available since 0.1
*/

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	require_once(dirname(__FILE__) . '/SSI.php');
}
elseif(!defined('SMF'))
{
	die('<b>Error:</b> Cannot install - please verify that you put this file in the same place as SMF\'s index.php and SSI.php files.');
}

if ((SMF == 'SSI') && !$user_info['is_admin'])
{
	die('Admin privileges required.');
}
	
$hooks = array(
	'integrate_pre_include' =>          '$sourcedir/Classifieds.php',
	'integrate_load_theme' =>           'smc_load_theme',
	'integrate_actions' =>              'smc_actions',
	'integrate_menu_buttons' =>         'smc_menu_buttons',
	'integrate_admin_areas' =>          'smc_admin_areas',
	'integrate_modify_modifications' => 'smc_modifications',
	'integrate_whos_online' =>          'smc_whos_online'
);

if (!empty($context['uninstalling']))
{
	$call = 'remove_integration_function';
}
else
{
	$call = 'add_integration_function';
}

foreach ($hooks as $hook => $function)
{
	$call($hook, $function);
}
	
if (SMF == 'SSI')
{
	echo 'Database changes are complete! Please wait...';
}

?>