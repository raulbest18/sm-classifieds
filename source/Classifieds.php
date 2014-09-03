<?php
/**
* This file is the main controller for SM Classifieds, and handles
* requesting and displaying all necessary data.
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

function ClassifiedsMain()
{
    global $modSettings, $context, $scripturl, $sourcedir, $txt;

    loadTemplate('Classifieds');

    $listOptions = array(
        'id' => 'smc_list',
        'title' => !empty($modSettings['smc_custom_title']) ? $modSettings['smc_custom_title'] : $txt['classifieds'],
        'base_href' => $scripturl . '?action=classifieds',
        'items_per_page' => !empty($modSettings['smc_items_per_page']) ? $modSettings['smc_items_per_page'] : 25,
        'default_sort_col' => !empty($modSettings['smc_default_sort_col']) ? $modSettings['smc_default_sort_col'] : 'date',
        'default_sort_dir' => 'asc',
        'width' => !empty($modSettings['smc_custom_list_width']) ? $modSettings['smc_custom_list_width'] : '',
        'no_items_label' => $txt['smc_no_items'],
        'no_items_align' => 'center',
        'get_items' => array(
            'function' => 'list_getClassifieds',
        ),
        'get_count' => array(
            'function' => 'list_getNumClassifieds',
        )
    );

    $listOptions['columns']['date'] = array(
        'header' => array(
            'value' => $txt['smc_date']
        ),
        'data' => array(
            'function' => create_function('$rowData', '
                return date(\'m/j/Y\', forum_time(true, $rowData[\'poster_time\']));
            '),
            'style' => 'text-align: center;'
        ),
        'sort' => array(
            'default' => 'm.poster_time',
            'reverse' => 'm.poster_time DESC',
        )
    );

    $listOptions['columns']['board'] = array(
        'header' => array(
            'value' => $txt['smc_board']
        ),
        'data' => array(
            'function' => create_function('$rowData', '
                global $scripturl;

                return \'<a href="\' . $scripturl . \'?board=\' . $rowData[\'id_board\'] . \'.0">\' . $rowData[\'board_name\'] . \'</a>\';
            ')
        ),
        'sort' => array(
            'default' => 'b.board_name',
            'reverse' => 'b.board_name DESC'
        )
    );

    $listOptions['columns']['topic'] = array(
        'header' => array(
            'value' => $txt['smc_topic']
        ),
        'data' => array(
            'function' => create_function('$rowData', '
                global $scripturl;

                return \'<a href="\' . $scripturl . \'?topic=\' . $rowData[\'id_topic\'] . \'.msg\' . $rowData[\'id_msg\'] . \';topicseen#new" rel="nofollow">\' . $rowData[\'subject\'] . \'</a> <img src="/assets/img/new.gif" alt="New!" />\';
            ')
        ),
        'sort' => array(
            'default' => 'm.subject',
            'reverse' => 'm.subject DESC'
        )
    );

    $listOptions['columns']['author'] = array(
        'header' => array(
            'value' => $txt['smc_author']
        ),
        'data' => array(
            'function' => create_function('$rowData', '
                global $scripturl;

                return \'<a href="\' . $scripturl . \'?action=profile;u=\' . $rowData[\'id_member\'] . \'">\' . $rowData[\'poster_name\'] . \'</a>\';
            '),
            'style' => 'text-align: center;'
        ),
        'sort' => array(
            'default' => 'm.poster_name',
            'reverse' => 'm.poster_name DESC'
        )
    );

    $listOptions['columns']['replies'] = array(
        'header' => array(
            'value' => $txt['smc_replies']
        ),
        'data' => array(
            'db' => 'num_replies',
            'style' => 'text-align: center;'
        ),
        'sort' => array(
            'default' => 't.num_replies',
            'reverse' => 't.num_replies DESC'
        )
    );

    $listOptions['columns']['views'] = array(
        'header' => array(
            'value' => $txt['smc_views']
        ),
        'data' => array(
            'db' => 'num_views',
            'style' => 'text-align: center;'
        ),
        'sort' => array(
            'default' => 't.num_views',
            'reverse' => 't.num_views DESC'
        )
    );

    $listOptions['additional_rows'] = array(
        array(
            'position' => 'after_title',
            'value' => $txt['smc_label']
        )
    );

	require_once($sourcedir . '/Subs-List.php');
	createList($listOptions);

	$context['sub_template'] = 'show_list';
	$context['default_list'] = 'smc_list';
}

function list_getClassifieds($start, $items_per_page, $sort)
{
	global $context, $settings, $scripturl, $txt, $db_prefix, $user_info;
	global $modSettings, $smcFunc;

	$include_boards = !empty($modSettings['smc_classifieds_boards']) ? $modSettings['smc_classifieds_boards'] : array();

	// Find all the posts in distinct topics.  Newer ones will have higher IDs.
	$request = $smcFunc['db_query']('substring', '
		SELECT
			m.poster_time, ms.subject, m.id_topic, m.id_member, m.id_msg, b.id_board, b.name AS board_name, t.num_replies, t.num_views,
			IFNULL(mem.real_name, m.poster_name) AS poster_name, ' . ($user_info['is_guest'] ? '1 AS is_read, 0 AS new_from' : '
			IFNULL(lt.id_msg, IFNULL(lmr.id_msg, 0)) >= m.id_msg_modified AS is_read,
			IFNULL(lt.id_msg, IFNULL(lmr.id_msg, -1)) + 1 AS new_from') . ', SUBSTRING(m.body, 1, 384) AS body, m.smileys_enabled, m.icon
		FROM {db_prefix}topics AS t
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_last_msg)
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
			INNER JOIN {db_prefix}messages AS ms ON (ms.id_msg = t.id_first_msg)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)' . (!$user_info['is_guest'] ? '
			LEFT JOIN {db_prefix}log_topics AS lt ON (lt.id_topic = t.id_topic AND lt.id_member = {int:current_member})
			LEFT JOIN {db_prefix}log_mark_read AS lmr ON (lmr.id_board = b.id_board AND lmr.id_member = {int:current_member})' : '') . '
		WHERE t.id_last_msg >= {int:min_message_id}
			' . (empty($include_boards) ? '' : '
			AND b.id_board IN ({array_int:include_boards})') . '
            ' . (!empty($modSettings['smc_exclude_locked_topics']) ? '
            AND t.is_locked = {int:is_locked}' : '') . '
			AND {query_wanna_see_board}' . ($modSettings['postmod_active'] ? '
			AND t.approved = {int:is_approved}
			AND m.approved = {int:is_approved}' : '') . '
		ORDER BY {raw:sort}
		LIMIT {int:offset}, {int:limit}',
		array(
			'current_member' => $user_info['id'],
			'include_boards' => $include_boards,
			'min_message_id' => $modSettings['maxMsgID'] - 35 * min($items_per_page, 5),
            'is_locked' => 0,
			'is_approved' => 1,
            'sort' => $sort,
            'offset' => $start,
            'limit' => $items_per_page
		)
	);
	$posts = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$row['body'] = strip_tags(strtr(parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']), array('<br />' => '&#10;')));
		if ($smcFunc['strlen']($row['body']) > 128)
        {
			$row['body'] = $smcFunc['substr']($row['body'], 0, 128) . '...';
        }

		censorText($row['subject']);
		censorText($row['body']);

		if (empty($modSettings['messageIconChecks_disable']) && !isset($icon_sources[$row['icon']]))
        {
			$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.gif') ? 'images_url' : 'default_images_url';
        }

        $posts[] = $row;
	}
	$smcFunc['db_free_result']($request);

    return $posts;
}

function list_getNumClassifieds()
{
    global $smcFunc, $modSettings;

    $request = $smcFunc['db_query']('', '
        SELECT COUNT(*) as num_classifieds
        FROM {db_prefix}topics
        WHERE id_board IN ({array_int:boards})',
        array(
            'boards' => !empty($modSettings['smc_classifieds_boards']) ? $modSettings['smc_classifieds_boards'] : array()
        )
    );
    list ($numClassifieds) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);

    return $numClassifieds;
}

?>