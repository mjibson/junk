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

function display($name, $email)
{
	echo
		getTableForm('New User:', array(
			array('Name', array('type'=>'text', 'name'=>'name', 'val'=>decode($name))),
			array('Password', array('type'=>'password', 'name'=>'pass1')),
			array('Verify password', array('type'=>'password', 'name'=>'pass2')),
			array('Email', array('type'=>'text', 'name'=>'email', 'val'=>decode($email))),
			array('', array('type'=>'disptext', 'val'=>'Your email address will never be used publicly. It is used <b>only</b> to recover passwords.')),

			array('', array('type'=>'submit', 'name'=>'submit', 'val'=>'Register')),
			array('', array('type'=>'hidden', 'name'=>'a', 'val'=>'newuser'))
		));
}

$name = isset($_POST['name']) ? encode($_POST['name']) : '';
$email = isset($_POST['email']) ? encode($_POST['email']) : '';
$pass1 = isset($_POST['pass1']) ? encode($_POST['pass1']) : '';
$pass2 = isset($_POST['pass2']) ? encode($_POST['pass2']) : '';

if(isset($_POST['submit']))
{
	$fail = false;

	$res = $db->query('select count(*) as count from users where user_name=\'' . $name . '\'');
	if(!$name)
	{
		echo '<p/>No name: enter a name.';
		$fail = true;
	}
	else if($res[0]['count'] != '0')
	{
		echo '<p/>Username already registered: try another name.';
		$fail = true;
	}

	if(!$pass1)
	{
		echo '<p/>No password: enter a password';
		$fail = true;
	}
	else if(!$pass2)
	{
		echo '<p/>No verification password: fill in both fields.';
		$fail = true;
	}
	else if($pass1 != $pass2)
	{
		echo '<p/>Passwords do not match.';
		$fail = true;
	}

	$res = $db->query('select count(*) as count from users where user_email=\'' . $email . '\'');
	if(!$email)
	{
		echo '<p/>No email address: if you lose your password, there is no way to recover it. You can set your password in the User CP.';
	}
	else if(!ereg("^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$", decode($email)))
	{
		echo '<p/>Invalid email address.';
		$fail = true;
	}
	else if($res[0]['count'] != '0')
	{
		echo '<p/>Email address already registered: try another address.';
		$fail = true;
	}

	if($fail)
	{
		echo '<p/>User registration failed.';
		display($name, $email);
	}
	else
	{
		$md5pass = md5($pass1);
		$uid = $db->insert('insert into users (user_name, user_email, user_pass, user_register) values (\'' . $name . '\', \'' . $email . '\', \'' . $md5pass . '\', ' . TIME . ')', 'user');

		setARCcookie('id', $uid);
		setARCcookie('pass', $md5pass);

		echo '<p/>User &quot;' . decode($name) . '&quot; successfully registered. You have been automatically logged in.';
		echo '<p/>Complete your profile in the ' . makeLink('User Control Panel', 'a=usercp') . '.';
	}
}
else
	display($name, $email);

update_session_action(305, '', 'New User');

?>
