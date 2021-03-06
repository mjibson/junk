<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<!-- $Id$ -->
	<meta http-equiv="Content-Type" content="text/html; charset=us-ascii" />
	<title>
		<?php echo strtolower(ARC_TITLE); ?> -
		<?php echo strtolower(ARC_SECTION); ?>
		<?php echo $GLOBALS['PAGE_TITLE'] ? '- ' . $GLOBALS['PAGE_TITLE'] : ''; ?>
	</title>
	<ARC_HEAD/>
	<link rel="stylesheet" type="text/css" href="<ARC_TEMPLATE_DIR/>/main.css" />
	<link rel="alternate" type="application/rss+xml" title="<?php echo ARC_TITLE; ?> RSS" href="<?php echo ARC_WWW_PATH; ?>rss.php" />
</head>

<body <ARC_BODYTAG/> >
	<div id="globalWrapper">
		<div id="column-content">
			<div id="content">
				<h1 class="firstHeading"><?php echo ARC_SECTION; ?></h1>
				<div id="bodyContent">
					<div id="contentSub"><?php echo $GLOBALS['PAGE_TITLE']; ?></div>
					<ARCCONTENT/>
					<div class="visualClear"></div>
				</div>
			</div>
		</div>
		<div id="column-one">
			<div id="p-cactions" class="portlet">
				<ul>
					<?php
						$items = getSiteArray('NAV');
						$count = count($items);

						for($i = 0; $i < $count; $i++)
						{
							echo '<li';

							if(strcasecmp(ARC_SECTION, $items[$i]['site_main']) == 0)
								echo ' class="selected"';

							echo '>' . createSiteString($items, $i) . '</li>';
						}
					?>
				</ul>
			</div>
			<div class="portlet" id="p-logo">
				<a style="background-image: url(<ARC_TEMPLATE_DIR/>/wikiisland.gif);"
					href="<?php echo ARC_WWW_PATH; ?>"
					title=""></a>
			</div>
			<div class="portlet">
				<h5>section nav</h5>
				<div class="pBody">
					<ul>
						<li><ARCSECTION_NAV><li>INSERT</li></ARCSECTION_NAV></li>
					</ul>
				</div>
			</div>
			<?php if(LOGGED) { ?>
			<div class="portlet">
				<h5><ARC_USER/></h5>
				<div class="pBody">
					<ul>
						<?php
						$cart = makeCartLink();
						echo ($cart ? '<li>' . $cart . '</li>' : '');

						$pms = makePMLink();
						echo ($pms ? '<li class="usermessage">' . $pms . '</li>' : '<li>0 new PMs</li>');

						if(MODULE_GAME)
						{
							$res = $db->query('select player_name, player_lv, player_id, player_battle, domain_id, domain_abrev from player, domain where player_user=' . ID . ' and player_domain=domain_id');
							for($i = 0; $i < count($res); $i++)
							{
								echo '<li';

								if($res[$i]['player_id'] == $PLAYER['player_id'])
									echo ' class="usermessage"';

								echo '>' . makeLink(decode($res[$i]['player_name']), 'a=viewplayerdetails&player=' . $res[$i]['player_id'], SECTION_GAME);

								if($res[$i]['player_battle'])
									echo '*';

								echo ' (' . $res[$i]['player_lv'] . ') [' . makeLink($res[$i]['domain_abrev'], $_SERVER['QUERY_STRING'] . '&domain=' . $res[$i]['domain_id']) . ']</li>';
							}
						}
					?>
					</ul>
				</div>
			</div>
			<?php } ?>
			<div class="portlet">
				<h5>server time</h5>
				<div class="pBody">
					<ARC_SERVERTIME/>
				</div>
			</div>
			<div class="portlet">
				<h5><?php echo makeLink('who\'s online', 'a=whosonline', SECTION_USER); ?></h5>
				<div class="pBody">
					<ARC_WHOSONLINE/>
				</div>
			</div>
			<div class="portlet">
				<h5>render stats</h5>
				<div class="pBody">
					<ARC_PROFILE/>
					<br/><ARC_RSS/>
				</div>
			</div>
		</div>
	</div>

<ARC_PREENDBODY/>

</body>

</html>
