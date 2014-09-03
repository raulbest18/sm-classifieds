<?php
/**
* This file contains all supporting and installation functions
* necessary for SM Classifieds
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

function smc_settings()
{
    global $context, $txt, $modSettings, $scripturl, $smcFunc;

    if (empty($categories))
    {
        $config_vars = array(array('desc', 'smc_no_categories'));
        $context['settings_save_dont_show'] = true;
    }
    else
    {
        $config_vars = array(
            array('text', 'smc_custom_title'),
            array('int', 'smc_custom_list_width'),
            array('int', 'smc_items_per_page'),
            array('select', 'smc_default_sort_col', $cols),
            array('check', 'smc_exclude_locked_topics'),
            array('select', 'smc_classified_boards', $boards, 'multiple'),
        );
        //!!! Still not finished....
    }
}

?>