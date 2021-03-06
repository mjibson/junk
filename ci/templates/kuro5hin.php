<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
	<!-- $Id$ -->
	<meta http-equiv="Content-Type" content="text/html; charset=us-ascii" />
	<link rel="alternate" type="application/rss+xml" title="<?php echo ARC_TITLE; ?> RSS" href="<?php echo ARC_WWW_PATH; ?>rss.php" />
	<title>
		<?php echo strtolower(ARC_TITLE); ?> ||
		<?php echo strtolower(ARC_SECTION); ?>
		<?php echo $GLOBALS['PAGE_TITLE'] ? '- ' . $GLOBALS['PAGE_TITLE'] : ''; ?>
	</title>
	<style type="text/css">
		<!--

		body {
			background-color: #ffffff;
		}

		p, body, td, li, input, textarea, select {
			font-family: sans-serif;
			font-size: 8pt;
			color: #000000;
		}

		a {
			color: #003767;
		}

		a:visited {
			color: #21659a;
		}

		a:active {
			color: #c0c0c0;
		}

		.ciNavTable, .ciNavTable a {
			border-spacing: 0px;
			padding: 0px;
			text-align: center;
			width: 100%;
			background-color: #eeeeee;
			text-decoration: none;
			color: #000000;
		}

		.tableMain {
			border-spacing: 0px;
			border-collapse: collapse;
		}

		.tableHeaderCellL, .tableHeaderCell {
			padding: 2px;
			background-color: #eeeeee;
			font-weight: bold;
			text-align: center;
			border-left: 1px solid #000000;
			border-top: 1px solid #000000;
		}

		.tableHeaderCellR {
			padding: 2px;
			background-color: #eeeeee;
			font-weight: bold;
			text-align: center;
			border-left: 1px solid #000000;
			border-top: 1px solid #000000;
			border-right: 1px solid #000000;
		}

		.tableCell, .tableCellL, .tableCellTL {
			padding: 2px;
			border-left: 1px solid #000000;
			border-top: 1px solid #000000;
		}

		.tableCellTR, .tableCellR {
			padding: 2px;
			border-left: 1px solid #000000;
			border-top: 1px solid #000000;
			border-right: 1px solid #000000;
		}

		.tableCellB, .tableCellBL {
			padding: 2px;
			border-left: 1px solid #000000;
			border-top: 1px solid #000000;
			border-bottom: 1px solid #000000;
		}

		.tableCellBR {
			padding: 2px;
			border: 1px solid #000000;
		}

		td.ciNavTableTd {
			border-top: 1px solid #000000;
			border-right: 1px solid #000000;
			border-bottom: 1px solid #000000;
		}

		td.box, td.box a {
			width: 100%;
			color: #ffffff;
		}

		table.box {
			border-spacing: 0px;
			padding: 0px;
			width: 100%;
			background-color: #000000;
		}

		table.boxinner {
			border-spacing: 0px;
			padding: 1px;
			width: 100%;
			background-color: #006699;
		}

		table.content {
			border-spacing: 3px;
			padding: 0px;
			width: 100%;
			background-color: #ffffff;
		}

		table.boxcontents {
			border: 0px;
			border-spacing: 0px;
			padding: 1px;
			width: 100%;
		}

		table.maintable {
			border: 0px;
			border-spacing: 5px;
			padding: 0px;
			width: 100%;
		}

		blockquote {
			border-left: 4px #c7d2ea solid;
			padding:  0 0 0 10px;
			margin: 10px 20px 10px 20px;
			letter-spacing: 0px;
			white-space:inherit;
		}

		blockquote[cite]:after {
			content: "Quote from: " attr(cite);
			display: block;
			border-top: 1px solid #999;
			margin: 1em 0 0;
			padding: .5em 0 0;
			font-size: .8em;
			font-weight: bold;
		}

		-->
	</style>
	<ARC_HEAD/>
	</head>

<body <ARC_BODYTAG/> >

<br/>
<a href="http://crescentisland.com"><b><?php echo ARC_TITLE; ?></b></a>

<table class="ciNavTable">
	<tr>
		<td style="border: 1px solid #000000;">
			<ARCNAV><td class="ciNavTableTd">INSERT</td></ARCNAV>
		</td>
	</tr>
</table>

<p/>

<table class="maintable">
	<tr>
		<td valign="top" width="15%">

		<?php
		if(LOGGED)
		{
			?>
			<table class="box">
				<tr>
					<td width="100%">
						<table class="boxinner">
							<tr>
							<td class="box">
								<ARC_USER/>
							</td>
						</tr>
						</table>
					</td>
				</tr>
			</table>

			<table class="boxcontents">
				<tr>
					<td>
						<?php
							$cart = makeCartLink();
							echo ($cart ? $cart . '<br/>' : '');

							$pms = makePMLink();
							echo ($pms ? $pms : '0 new PMs') . '<br/>';

							if(MODULE_GAME)
							{
								$res = $db->query('select player_name, player_lv, player_id, player_battle, domain_id, domain_abrev from player, domain where player_user=' . ID . ' and player_domain=domain_id');

								for($i = 0; $i < count($res); $i++)
								{
									echo makeLink(decode($res[$i]['player_name']), 'a=viewplayerdetails&player=' . $res[$i]['player_id'], SECTION_GAME);

									if($res[$i]['player_battle'])
										echo '*';

									echo ' (' . $res[$i]['player_lv'] . ') [' . makeLink($res[$i]['domain_abrev'], $_SERVER['QUERY_STRING'] . '&domain=' . $res[$i]['domain_id']) . ']';
								}
							}
						?>
					</td>
				</tr>
			</table>
			<p/>
			<?php
			}
		?>

		<table class="box">
			<tr>
				<td width="100%">
					<table class="boxinner">
						<tr>
						<td class="box">
							<?php echo substr(ARC_SECTION, 0, 1) . strtolower(substr(ARC_SECTION, 1)); ?> Navigation
						</td>
					</tr>
					</table>
				</td>
			</tr>
		</table>

		<table class="boxcontents">
			<tr>
				<td>
					<ARCSECTION_NAV>INSERT<br/></ARCSECTION_NAV>
				</td>
			</tr>
		</table>
		<p/>

		<table class="box">
			<tr>
				<td width="100%">
					<table class="boxinner">
						<tr>
						<td class="box">
							Server Time
						</td>
					</tr>
					</table>
				</td>
			</tr>
		</table>

		<table class="boxcontents">
			<tr>
				<td>
					<ARC_SERVERTIME/>
				</td>
			</tr>
		</table>
		<p/>

		<table class="box">
			<tr>
				<td width="100%">
					<table class="boxinner">
						<tr>
						<td class="box">
							<?php echo makeLink('Who\'s Online', 'a=whosonline', SECTION_USER); ?>
						</td>
					</tr>
					</table>
				</td>
			</tr>
		</table>

		<table class="boxcontents">
			<tr>
				<td>
					<ARC_WHOSONLINE/>
				</td>
			</tr>
		</table>
		<p/>

		<table class="box">
			<tr>
				<td width="100%">
					<table class="boxinner">
						<tr>
						<td class="box">
							Render Stats
						</td>
					</tr>
					</table>
				</td>
			</tr>
		</table>

		<table class="boxcontents">
			<tr>
				<td>
						<ARC_PROFILE/>
						<br/><ARC_RSS/>
				</td>
			</tr>
		</table>
		<p/>

		</td>

		<td valign="top" width="85%">
			<table class="box">
				<tr>
					<td>
						<table class="boxinner">
							<tr>
								<td valign="middle" class="box">
									<b><?php echo strtolower(ARC_SECTION);?></b>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>

			<table class="content">
				<tr>
					<td valign="top" width="100%" align="left">
						<ARCCONTENT/>
					</td>
				</tr>
			</table>
		</td>

	</tr>
</table>

<ARC_PREENDBODY/>

</body></html>
