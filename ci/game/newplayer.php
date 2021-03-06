<?php

/* $Id$ */

/*
 * Copyright (c) 2003 Matthew Jibson <dolmant@gmail.com>
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

function display($name, $domain, $gender, $town)
{
	global $db;

	$res = $db->query('select domain_name, domain_id from domain order by domain_expw_time, domain_expw_max');

	$domainlist = '';

	foreach($res as $dom)
		$domainlist .= '<option value="' . $dom['domain_id'] . '"' . ($domain == $dom['domain_id'] ? ' selected' : '') . '>' . $dom['domain_name'] . '</option>';

	$genderlist = '<option ' . ($gender == 'M' ? 'selected' : '') . '>M</option>' .
		'<option ' . ($gender == 'F' ? 'selected' : '') . '>F</option>';

	$res = $db->query('select town_name, town_id from town where town_lv=0 order by random()');

	$townlist = '';

	foreach($res as $t)
		$townlist .= '<option value="' . $t['town_id'] . '"' . ($town == $t['town_id'] ? ' selected' : '') . '>' . $t['town_name'] . '</option>';

	echo
		getTableForm('New Player:', array(
			array('Name', array('type'=>'text', 'name'=>'name', 'val'=>decode($name))),
			array('Domain', array('type'=>'select', 'name'=>'domain', 'val'=>$domainlist)),
			array('Gender', array('type'=>'select', 'name'=>'gender', 'val'=>$genderlist)),
			array('Initial Town', array('type'=>'select', 'name'=>'town', 'val'=>$townlist)),

			array('', array('type'=>'submit', 'name'=>'submit', 'val'=>'Register')),
			array('', array('type'=>'hidden', 'name'=>'a', 'val'=>'newplayer'))
		));
}

if(LOGGED)
{
	$name = isset($_POST['name']) ? encode($_POST['name']) : '';
	$domain = isset($_POST['domain']) ? intval($_POST['domain']) : '0';
	$gender = isset($_POST['gender']) ? encode($_POST['gender']) : '';
	$town = isset($_POST['town']) ? intval($_POST['town']) : '0';

	if(isset($_POST['submit']))
	{
		$fail = false;

		if(!$name)
		{
			echo '<p/>No player name entered.';
			$fail = true;
		}

		$dname = getDBData('domain_name', $domain, 'domain_id', 'domain');

		if(!$dname)
		{
			echo '<p/>Invalid domain selected.';
			$fail = true;
		}

		$tname = getDBData('town_name', $town, 'town_id', 'town');

		if(!$tname)
		{
			echo '<p/>Invalid town selected.';
			$fail = true;
		}

		$player = $db->query('select player_name from player where player_user=' . ID . ' and player_domain=' . $domain);

		if(count($player))
		{
			echo '<p/>You already have the player ' . decode($player[0]['player_name']) . ' registered on this domain. You may only have one player registered on a domain.';
			$fail = true;
		}

		$existing = $db->query('select player_id from player where player_name=\'' . $name . '\' and player_domain=' . $domain);

		if(count($existing))
		{
			echo '<p/>There is already a player with this name registered in this domain.';
			$fail = true;
		}

		if($gender != 'M' && $gender != 'F')
		{
			echo '<p/>Invalid gender.';
			$fail = true;
		}

		if($fail)
		{
			echo '<p/>Player registration failed.';
			display($name, $domain, $gender, $town);
		}
		else
		{
			$db->query('insert into player (player_user, player_name, player_gender, player_domain, player_register, player_last, player_job, player_town) values (' .
			ID . ', ' .
			'\'' . $name . '\', ' .
			($gender == 'M' ? '1' : '-1') . ', ' .
			$domain . ', ' .
			TIME . ', ' .
			TIME . ', ' .
			'1' . ', ' .
			$town .
			')');

			$pid = $db->query('select player_id from player where player_user=' . ID . ' and player_domain=' . $domain);

			// create an entry in player_job
			$db->query('insert into player_job values (' . $pid[0]['player_id'] . ', 1, 1, 0)');

			// set the mod stats
			updatePlayerStats($pid[0]['player_id']);

			echo '<p/>' . decode($name) . ' has been registered in ' . $dname . ' and is living in ' . $tname . '.';
		}
	}
	else
		display($name, $domain, $gender, $town);
}
else
	echo '<p/>You must be logged in to view this page.';

update_session_action(703, '', 'New Player');

?>
