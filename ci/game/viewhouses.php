<?php

/* $Id$ */

/*
 * Copyright (c) 2005 Matthew Jibson <dolmant@gmail.com>
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

$query = 'select * from house order by house_lv';
$res = $db->query($query);

$array = array();

array_push($array, array(
	'House',
	'Cost',
	'Required Level'
));

for($i = 0; $i < count($res); $i++)
{
	array_push($array, array(
		makeLink($res[$i]['house_name'], 'a=viewhousedetails&house=' . $res[$i]['house_id']),
		$res[$i]['house_cost'],
		$res[$i]['house_lv']
	));
}

echo getTable($array);

update_session_action(507, '', 'Houses');

?>
