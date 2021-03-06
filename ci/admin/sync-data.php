<?php

/* $Id: sync-seqs.php 905 2005-08-02 00:49:18Z dolmant $ */

/*
 * Copyright (c) 2006 Matthew Jibson <dolmant@gmail.com>
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

$db->query('delete from data');

$ret = $db->query('select count(*) as count from stats');
$hits = $ret[0]['count'];

$ret = $db->query('select count(*) as count from stats_podcast');
$podcast = $ret[0]['count'];

$ret = $db->query('select count(*) as count from stats_rss');
$rss = $ret[0]['count'];

$db->query('insert into data values (\'hits\', null, ' . $hits . ')');
$db->query('insert into data values (\'podcast_downloads\', null, ' . $podcast . ')');
$db->query('insert into data values (\'rss_hits\', null, ' . $rss . ')');

echo '<p/>Done.';

update_session_action(200, '', 'Sync Data');

?>