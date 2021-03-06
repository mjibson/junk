<?php

/* $Id$ */

/*
 * Copyright (c) 2004 Matthew Jibson <dolmant@gmail.com>
 *
 * Permission to use, copy, modify, and distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

if($PLAYER)
{
	if(isset($_POST['submit']))
	{
		foreach($_POST as $key=>$val)
		{
			// only use the order values, we'll get the display later during the query
			if(substr($key, 0, 1) == 'o')
			{
				// make sure we have an integer as the id
				$a = intval(substr($key, 1));

				// set display only if the appropriate field is on
				$d = (isset($_POST['d' . $a]) && $_POST['d' . $a] == 'on') ? '1' : '0';

				// commit
				$db->query('update player_ability set player_ability_order=' . intval($val) . ', player_ability_display=' . $d . ' where player_ability_player=' . $PLAYER['player_id'] . ' and player_ability_id=' . $a);
			}
		}

		echo '<p/>Abilities updated.';
	}

	$res = $db->query('select player_ability.*, ability_name from player_ability, ability where player_ability_player=' . $PLAYER['player_id'] . ' and player_ability_ability=ability_id order by player_ability_order');

	$array = array(array(
		'Ability',
		'Level',
		'Order',
		'Display'
	));

	for($i = 0; $i < count($res); $i++)
	{
		array_push($array, array(
			makeLink($res[$i]['ability_name'], 'a=viewabilitydetails&ability=' . $res[$i]['player_ability_ability'], SECTION_GAME),
			$res[$i]['player_ability_level'],
			getFormField(array('type'=>'text', 'parms'=>'size="3" maxlength="3" style="width:30px"', 'name'=>('o' . $res[$i]['player_ability_id']), 'val'=>$res[$i]['player_ability_order'])),
			getFormField(array('type'=>'checkbox', 'name'=>('d' . $res[$i]['player_ability_id']), 'val'=>($res[$i]['player_ability_display'] ? 'checked' : '')))
		));
	}

	$table = getTable($array);

	echo getForm('Manage battle abilities:', array(
		array('<p/>', array('type'=>'disptext', 'val'=>$table)),
		array('<p/>', array('type'=>'submit', 'name'=>'submit', 'val'=>'Save')),
		array('', array('type'=>'hidden', 'name'=>'a', 'val'=>'abilities'))
	));

	echo '<p/>This form is used to set what abilities will be shown in what order during battle. Set the order to some positive integer in the order that you want them to be shown during battle (i.e. 1, 2, 3). If you do not want an ability to be available during battle, uncheck the box in the display column.';
}
else
	echo '<p/>You must switch to a domain with a player, or create a new one in this domain.';

update_session_action(704, '', 'Manage Abilities');

?>
