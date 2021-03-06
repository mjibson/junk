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

function display($user)
{
	echo getTableForm('Login:', array(
		array('Username', array('type'=>'text', 'name'=>'user', 'val'=>decode($user))),
		array('Password', array('type'=>'password', 'name'=>'pass')),

		array('', array('type'=>'submit', 'name'=>'submit', 'val'=>'Login')),
		array('', array('type'=>'hidden', 'name'=>'a', 'val'=>'login')),
		array('', array('type'=>'hidden', 'name'=>'s', 'val'=>(isset($_GET['s']) ? $_GET['s'] : ''))),
		array('', array('type'=>'hidden', 'name'=>'r', 'val'=>(isset($_GET['r']) ? $_GET['r'] : (isset($_POST['r']) ? $_POST['r'] : ''))))
	));
}

$user = isset($_POST['user']) ? encode($_POST['user']) : '';
$pass = isset($_POST['pass']) ? encode($_POST['pass']) : '';

if(isset($_POST['submit']))
{
	$fail = false;

	if(!$user)
	{
		echo '<p/>No username specified.';
		$fail = true;
	}
	if(!$pass)
	{
		echo '<p/>No password specified.';
		$fail = true;
	}

	$ret = $db->query('select user_id, user_pass from users where user_name=\'' . $user . '\' and user_pass=\'' . md5($pass) . '\'');
	if(count($ret) == 1)
	{
		$last = isset($_POST['r']) ? decode($_POST['r']) : '';

		setARCcookie('id', $ret[0]['user_id']);
		setARCcookie('pass', $ret[0]['user_pass']);
		$id = $ret[0]['user_id'];
		$pass = $ret[0]['user_pass'];

		$db->query('update session set session_uid=' . $id . ' where session_id=\'' . SESSION . '\' and session_uid=0');

		echo '<p/>Logged in successfully as ' . decode($user) . '.';

		if($last && strpos($last, 'logout') === false)
		{
			echo '<p/>Redirecting to <a href="' . $last . '">last location</a>...';
			$GLOBALS['ARC_HEAD'] = '<meta http-equiv="refresh" content="0; url=' . $last . '">';
		}
		else
		{
			$GLOBALS['ARC_HEAD'] = '<meta http-equiv="refresh" content="0; url=index.php?a=login">';
		}
	}
	else if($user && $pass)
	{
		echo '<p/>Not a valid username/password combination. Try again.';
		$fail = true;
	}

	if($fail)
	{
		echo '<p/>Login failed.';
		display($user);
	}
}
else if(LOGGED)
{
	echo '<p/>Logged in successfully as ' . $USER['user_name'] . '.';
}
else
	display($user);

update_session_action(302, '', 'Login');

?>
