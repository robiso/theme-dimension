<?php

global $Wcms;

?>
<!DOCTYPE HTML>
<!--
	Dimension by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>
	<head>
		<title><?= $Wcms->get('config', 'siteTitle') ?></title>
        <meta name="description" content="<?= $Wcms->page('description') ?>">
        <meta name="keywords" content="<?= $Wcms->page('keywords') ?>">

		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />

		<?php if($Wcms->loggedIn) { ?>
			<link rel="stylesheet" href="<?= $Wcms->asset('assets/css/adminPanel.bootstrap.min.css') ?>" />
			<link rel="stylesheet" href="<?= $Wcms->asset('assets/css/node-editor.bootstrap.min.css') ?>" />
			<link rel="stylesheet" href="<?= $Wcms->asset('assets/css/note-popover.bootstrap.min.css') ?>" />
		<?php } ?>
		<?= $Wcms->css() ?>
		<link rel="stylesheet" href="<?= $Wcms->asset('assets/css/main.css') ?>" />
		<noscript><link rel="stylesheet" href="<?= $Wcms->asset('assets/css/noscript.css') ?>" /></noscript>
		<?php

		if(isset($Wcms->get("config")->background) && !empty($Wcms->get("config")->background)) {
			$bg = $Wcms->get("config")->background;
			echo "<style>#bg:after { background-image: url('data/files/$bg'); }</style>";
		}

		$redirect = json_encode(!($Wcms->get('config', 'login') === $Wcms->currentPage || $Wcms->loggedIn));
		echo <<<HTML
		<script>var base = "{$Wcms->url()}"; var redirect = $redirect;</script>
HTML;

		?>
	</head>
	<body class="is-preload">
        <?= $Wcms->alerts() ?>
        <?= $Wcms->settings() ?>

		<!-- Wrapper -->
			<div id="wrapper">

				<!-- Header -->
					<header id="header">
						<div class="logo">
							<span class="icon fa-gem"></span>
						</div>
						<div class="content">
							<div class="inner">
								<h1><?= $Wcms->get('config', 'siteTitle') ?></h1>
								<?= $Wcms->block('subside') ?>
							</div>
						</div>
						<nav>
							<ul>
								<?php foreach ( $Wcms->db->config->menuItems as $id => $page ):
									if($page->visibility != "show") continue;?>
								<li><a href="<?=$Wcms->loggedIn?"":"#"?><?=$page->slug?>"><?=$page->name; ?></a></li>
								<?php endforeach; ?>
							</ul>
						</nav>
					</header>

				<!-- Main -->
					<div id="main">

						<?php foreach ( $Wcms->db->pages as $pageName => $page ): ?>

						<!-- Page: <?=$page->title; ?> -->
							<article id="<?=$pageName?>">
								<h2 class="major"><?=$page->title; ?></h2>

								<?= getPageBlocks("content", $pageName) ?>
							</article>

        				<?php endforeach; ?>

						<?php if($Wcms->get('config', 'login') === $Wcms->currentPage) {
							$segments = (object)$this->loginView();
							echo "<article id=\"{$Wcms->currentPage}\">
								<h2 class=\"major\">Login</h2>

								{$segments->content}
							</article>";
						} ?>

					</div>

				<!-- Footer -->
					<footer id="footer">
						<p class="copyright"><?= $Wcms->footer() ?> &nbsp; | &nbsp; Design: <a href="https://html5up.net">HTML5 UP</a>.</p>
					</footer>

			</div>

		<!-- BG -->
			<div id="bg"></div>

		<!-- Scripts -->
			<script src="<?= $Wcms->asset('assets/js/jquery.min.js') ?>"></script>
			<script src="<?= $Wcms->asset('assets/js/browser.min.js') ?>"></script>
			<script src="<?= $Wcms->asset('assets/js/breakpoints.min.js') ?>"></script>
			<script src="<?= $Wcms->asset('assets/js/util.js') ?>"></script>
			<script src="<?= $Wcms->asset('assets/js/main.js') ?>"></script>

			<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous" type="text/javascript"></script>
			<?= $Wcms->js() ?>
	</body>
</html>
