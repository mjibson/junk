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

require_once ARC_HOME_MOD . 'objects/Entity.inc.php';
require_once ARC_HOME_MOD . 'objects/Player.inc.php';
require_once ARC_HOME_MOD . 'objects/Monster.inc.php';
require_once ARC_HOME_MOD . 'utility/GameMath.inc.php';
require_once ARC_HOME_MOD . 'utility/Battle.inc.php';

/* NOTE: DO NOTE USE foreach here. I haven't done extensive testing, but I
 * think that it uses a copy constructor, so modified values aren't saved.
 * Thus, you should use $entities[$i] so that the actual data is modified.
 */

if(!LOGGED)
	echo '<p/>You must be logged in to fight your battles.';
else if(!$PLAYER)
	echo '<p/>No player in this domain. Switch to a domain containing a registered player.';
else if(!$PLAYER['player_battle'])
	echo '<p/>You do not have an active battle. Create a new one.';
else
{
	$ret = $db->query('select * from battle_entity where battle_entity_battle=' . $PLAYER['player_battle'] . ' order by battle_entity_dead asc');

	if(count($ret) < 2)
	{
		// not a battle without atleast two entities
		$db->query('update player set player_battle=0 where player_battle=' . $PLAYER['player_battle']);
		exit('battle with less than two entities: exiting');
	}

	$entities = array();
	$teams = array();

	foreach($ret as $e)
	{
		// init team index to false for end of battle checking later on
		$teams[$e['battle_entity_team']] = false;

		switch($e['battle_entity_type'])
		{
			case ENTITY_PLAYER: array_push($entities, new Player($e, $entities)); break;
			case ENTITY_MONSTER: array_push($entities, new Monster($e, $entities)); break;
			default: array_push($entities, new Entity($e, $entities)); break;
		}
	}

	// figure out who has the next turn
	while(1)
	{
		$max = CT_TURN - 1;
		$turn = -1;

		for($i = 0; $i < count($entities); $i++)
		{
			if($entities[$i]->ct > $max && !$entities[$i]->dead)
			{
				$max = $entities[$i]->ct;
				$turn = $i;
			}
		}

		if($turn != -1)
			break;

		for($i = 0; $i < count($entities); $i++)
			$entities[$i]->accelTurn();
	}

	// the next entity can now take its turn

	$entities[$turn]->preTurn();

	// if the timers didn't set turnDone
	if(!$entities[$turn]->turnDone)
		$entities[$turn]->takeTurn();

	$entities[$turn]->postTurn();
	$entities[$turn]->endTurn();

	// turn is over, print current stats

	$stats = array(array('Entity', 'HP', 'MP'));

	if($USER['user_battle_verbose'])
		array_push($stats[0], 'CT', 'STR', 'MAG', 'DEF', 'MGD', 'AGL', 'ACC');

	for($i = 0; $i < count($entities); $i++)
	{
		$a = array();

		switch($entities[$i]->type)
		{
			case ENTITY_PLAYER:
				$name = makeLink($entities[$i]->name, 'a=viewplayerdetails&player=' . $entities[$i]->id, SECTION_GAME);
				break;
			case ENTITY_MONSTER:
				$name = makeLink($entities[$i]->name, 'a=viewmonsterdetails&monster=' . $entities[$i]->id, SECTION_GAME);
				break;
			default:
				$name = $entities[$i]->name;
		}

		array_push($a, $name);

		if(!$USER['user_battle_verbose'])
			array_push($a, $entities[$i]->hp, $entities[$i]->mp);
		else
			array_push($a,
				'<b>' . $entities[$i]->hp . '</b>/' . $entities[$i]->maxhp,
				$entities[$i]->mp . '/' . $entities[$i]->maxmp,
				$entities[$i]->ct,
				$entities[$i]->str,
				$entities[$i]->mag,
				$entities[$i]->def,
				$entities[$i]->mgd,
				$entities[$i]->agl,
				$entities[$i]->acc
			);

		array_push($stats, $a);
	}

	echo getTable($stats);

	/* In a multi player battle, turnDone = -1 means that the current player does
	 * not control this turn, and must wait. Thus, don't do worry about end
	 * battle.
	 */
	if($entities[$turn]->turnDone != -1)
	{
		// sync data back into table

		for($i = 0; $i < count($entities); $i++)
			$entities[$i]->sync();

		// check for battle end - only one team has entities that are not dead

		for($i = 0; $i < count($entities); $i++)
		{
			if($entities[$i]->dead != 1)
				$teams[$entities[$i]->team] = true;
		}

		$t = array_keys($teams, true);

		// one or zero teams are still alive, end battle and clean up
		if(count($t) <= 1)
		{
			$db->query('update battle set battle_end=' . TIME . ' where battle_id=' . $PLAYER['player_battle']);
			$db->query('update player set player_battle=0, player_expw=player_expw+1 where player_battle=' . $PLAYER['player_battle']);
			echo '<p/>Battle ended.';
			echo '<p/>' . makeLink('Start a new battle in the same area.', 'a=newbattle&area=' . getDBDataNum('battle_area', $PLAYER['player_battle'], 'battle_id', 'battle'));
			$done = true;

			// clear entities and timers
			$res = $db->query('select battle_entity_uid from battle_entity where battle_entity_battle=' . $PLAYER['player_battle']);
			for($i = 0; $i < count($res); $i++)
				$db->query('delete from battle_timer where battle_timer_uid=' . $res[$i]['battle_entity_uid']);
			$db->query('delete from battle_entity where battle_entity_battle=' . $PLAYER['player_battle']);
		}
	}

	// turnDone set to 0 means that the entitiy who went needs to do something next time
	if(!isset($done) && $entities[$turn]->turnDone != 0)
	{
		echo getTableForm('', array(
			array('', array('type'=>'submit', 'name'=>'submit', 'val'=>'Continue')),
			array('', array('type'=>'hidden', 'name'=>'a', 'val'=>'battle'))
		));
	}
}

update_session_action(801, '', 'Battle');

?>
