<?php

/* $Id$ */

/*
 * Copyright (c) 2007 Matt Jibson <dolmant@gmail.com>
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

update_session_action(1001, '', 'Welcome');

?>

<p/>Welcome to iAds.
<p/><ol>
	<li><?php
	echo (LOGGED ? '<strike>Sign up.</strike>' : makeLink('Sign up', 'a=newuser', SECTION_USER) . ' (or ' . makeLink('login', 'a=login&r=' . encode($_SERVER['REQUEST_URI']), SECTION_USER) . ').');
	?></li>
	<li><?php echo (LOGGED ? makeLink('Upload ads.', 'a=upload-ad') : 'Upload ads.'); ?></li>
	<li><?php echo makeLink('Find locations.', 'a=view-locations'); ?></li>
	<li><?php echo (LOGGED ? makeLink('Buy slots.', 'a=buy-slots') : 'Buy slots.'); ?></li>
	<li>Your ads show up on our screens.</li>
</ol>
