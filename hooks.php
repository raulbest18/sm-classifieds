<?php
/**
 * This file is used to run database queries when installing
 * a modification
 *
 * (c) Jason Clemons <hello@jasonclemons.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Check to make sure SMF exists in this directory
 */
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	die('<strong>Error:</strong> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

/**
 * Define the hooks in an array
 * 
 * Example:
 * $hook_functions = array('integrate_actions', 'modification_actions');
 */
$hook_functions = array(
	'integrate_pre_include' =>          '$sourcedir/Classifieds.php',
	'integrate_load_theme' =>           'smc_load_theme',
	'integrate_actions' =>              'smc_actions',
	'integrate_menu_buttons' =>         'smc_menu_buttons',
	'integrate_admin_areas' =>          'smc_admin_areas',
	'integrate_modify_modifications' => 'smc_modifications',
	'integrate_whos_online' =>          'smc_whos_online'
);

/**
 * This determines whether we're installing or uninstalling
 */
if ($context['uninstall'])
    $call = 'remove_integration_function';
else
    $call = 'add_integration_function';

/**
 * Execute the hook install/uninstall
 */
foreach ($hook_functions as $hook => $function)
	$call($hook, $function);
