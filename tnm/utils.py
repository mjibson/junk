# Copyright (c) 2011 Matt Jibson <matt.jibson@gmail.com>

import logging
import os
import re
import urlparse

from google.appengine.api import files
from google.appengine.api import urlfetch
from google.appengine.ext import ndb
from google.appengine.ext.webapp import template
import jinja2
import webapp2

from settings import *
import filters
import themes

import sys
sys.path.append(os.path.join(os.path.dirname(__file__), 'lib'))

import stripe

stripe.api_key = STRIPE_SECRET

env = jinja2.Environment(loader=jinja2.FileSystemLoader('templates'))
env.filters.update(filters.filters)

JQUERY_VERSION = '1.7.2'
JQUERY_UI_VERSION = '1.8.20'
ANGULAR_VERSION = '1.0.2'

if os.environ['SERVER_SOFTWARE'].startswith('Google'):
	JQUERY_URL = "//ajax.googleapis.com/ajax/libs/jquery/%(version)s/jquery.min.js" %{ 'version': JQUERY_VERSION }
	JQUERY_UI_URL = "//ajax.googleapis.com/ajax/libs/jqueryui/%(version)s/jquery-ui.min.js" %{ 'version': JQUERY_UI_VERSION }
	JQUERY_UI_CSS_URL = "//ajax.googleapis.com/ajax/libs/jqueryui/%(version)s/themes/base/jquery.ui.all.css" %{ 'version': JQUERY_UI_VERSION }
	ANGULAR_URL = "//ajax.googleapis.com/ajax/libs/angularjs/%(version)s/angular.min.js" %{ 'version': ANGULAR_VERSION }
else:
	JQUERY_URL = "/static/js/jquery-%(version)s.min.js" %{ 'version': JQUERY_VERSION }
	JQUERY_UI_URL = "/static/js/jquery-ui-%(version)s.min.js" %{ 'version': JQUERY_UI_VERSION }
	JQUERY_UI_CSS_URL = "/static/css/jquery-ui/jquery-ui-%(version)s.css" %{ 'version': JQUERY_UI_VERSION }
	ANGULAR_URL = "/static/js/angular-%(version)s.min.js" %{ 'version': ANGULAR_VERSION }

JQUERY = """<script src="%s"></script>""" %JQUERY_URL
JQUERY_UI = """<script src="%(js)s"></script>
<link rel="stylesheet" href="%(css)s" type="text/css">""" %{
	'js': JQUERY_UI_URL,
	'css': JQUERY_UI_CSS_URL,
}
ANGULAR = """<script src="%s"></script>""" %ANGULAR_URL

def render(_template, c):
	context = {
		'angular': ANGULAR,
		'google_api': GOOGLE_API,
		'jquery': JQUERY,
		'jquery_ui': JQUERY_UI,
	}
	context.update(c)
	return env.get_template(_template).render(**context)

def stripe_set_plan(user, site, token=None, plan=None):
	# called for new users
	if not user.stripe_id and token and plan:
		cust = stripe.Customer.create(
			email=user.email,
			description=user.key.id(),
			card=token,
			plan=plan,
		)

		def callback():
			u, s = ndb.get_multi([user.key, site.key])
			u.stripe_id = cust['id']
			u.stripe_last4 = cust['active_card']['last4']
			s.plan = plan
			ndb.put_multi([u, s])
			return u, s
	# called when changing card on plan
	elif user.stripe_id and (token or plan):
		cust = stripe.Customer.retrieve(user.stripe_id)
		kwargs = {'user': {}, 'site': {}}

		if token:
			cust.card=token
			cust = cust.save()
			kwargs['user']['stripe_last4'] = cust['active_card']['last4']
		if plan and plan != site.plan:
			kwargs['site']['plan'] = plan
			cust.update_subscription(plan=plan, prorate='True')

		def callback():
			u, s = ndb.get_multi([user.key, site.key])
			for k, v in kwargs['user'].items():
				setattr(u, k, v)
			for k, v in kwargs['site'].items():
				setattr(s, k, v)
			ndb.put_multi([u, s])
			return u, s
	else:
		raise ValueError('no card specified')

	user, site = ndb.transaction(callback, xg=True)
	return user, site

def make_options(options, default=None):
	ret = []

	for opt in options:
		if isinstance(opt, basestring):
			key = opt
			val = opt
		else:
			key = opt[0]
			val = opt[1]

		selected = ' selected' if default == key else ''

		ret.append('<option value="%s"%s>%s</option>' %(key, selected, val))

	return ret

def make_plan_options(default=None):
	return make_options(zip(USER_PLAN_CHOICES, PLAN_COSTS_DESC), default)

def gs_write(name, mime, content, cache='no-cache'):
	if os.environ.get('SERVER_SOFTWARE').startswith('Google App Engine'):
		fn = files.gs.create(
			'/gs/' + name,
			mime_type=mime,
			acl='public-read',
			cache_control=cache,
		)
		with files.open(fn, 'a') as f:
			f.write(content)
		files.finalize(fn)
	else:
		logging.info('gs write: %s, %s, %s', mime, name, len(content))

def slugify(value):
	value = value.strip().lower()
	words = re.findall('[a-z0-9]+', value)
	return '-'.join(words)

def style_colors(theme):
	c = themes.COLORS[theme][0]
	f = open(os.path.join('styles', theme, c + '.less')).read()
	colors = {}
	for c in f.splitlines():
		m = re.match('@([-a-z]+): ?#([0-9a-f]{6});', c, re.I)
		if m:
			colors[m.group(1)] = int(m.group(2), 16)

	return colors

def check_url(url):
	rpcs = []

	up = urlparse.urlparse(url)

	# facebook seems to require using graph.facebook.com - otherwise always returns 404
	if up.hostname.endswith('facebook.com'):
		url = url.replace(up.hostname, 'graph.facebook.com', 1)

	rpc = urlfetch.create_rpc()
	urlfetch.make_fetch_call(rpc, url, allow_truncated=True)

	try:
		result = rpc.get_result()
		return result.status_code == 200
	except urlfetch.DownloadError:
		return False
