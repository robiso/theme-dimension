<?php global $Wcms ?>

<?php

function getPageBlocks(string $key, string $page): string
{
	global $Wcms;

	$segments = $Wcms->get('pages', $page);
	$segments->content = $segments->content ?? '<h2>Click here add content</h2>';
	$keys = [
		'title' => $segments->title,
		'description' => $segments->description,
		'keywords' => $segments->keywords,
		'content' => $Wcms->loggedIn && $page == $Wcms->currentPage
			? $Wcms->editable('content', $segments->content, 'pages')
			: $segments->content
	];
	$content = $keys[$key] ?? '';
	return getPageHook('page', $content, $key)[0];
}

function getPageHook(): array
{
	global $Wcms;
	$numArgs = func_num_args();
	$args = func_get_args();
	if ($numArgs < 2) {
		trigger_error('Insufficient arguments', E_USER_ERROR);
	}
	$hookName = array_shift($args);
	if (!isset($Wcms->listeners[$hookName])) {
		return $args;
	}
	foreach ($Wcms->listeners[$hookName] as $func) {
		$args = $func($args);
	}
	return $args;
}

function alterAdmin($args) {
	global $Wcms;

    if(!$Wcms->loggedIn) return $args;

    $doc = new DOMDocument();
    @$doc->loadHTML($args[0]);

    $label = $doc->createElement("p");
    $label->setAttribute("class", "subTitle");
    $label->nodeValue = "Main background image";

    $doc->getElementById("general")->insertBefore($label, $doc->getElementById("general")->childNodes->item(8));

	$form_group = $doc->createElement("div");
    $form_group->setAttribute("class", "form-group");

    $wrapper = $doc->createElement("div");
    $wrapper->setAttribute("class", "change");

    $input = $doc->createElement("select");
    $input->setAttribute("class", "form-control");
    $input->setAttribute("onchange", "fieldSave('background',this.value,'config');");
    $input->setAttribute("name", "backgroundSelect");

	$option = $doc->createElement("option");
	$option->setAttribute("value", "");
	$option->nodeValue = "Theme default";
	$input->appendChild($option);

	$files = glob($Wcms->filesPath . "/*");
	foreach($files as $file) {
		if(!in_array(getimagesize($file)[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP))) continue;

		$file = basename($file);

		$option = $doc->createElement("option");
	    $option->setAttribute("value", $file);
		$option->nodeValue = $file;

		if($Wcms->get("config")->background == $file)
			$option->setAttribute("selected", "selected");

		$input->appendChild($option);
	}

    $wrapper->appendChild($input);
    $form_group->appendChild($wrapper);

    $doc->getElementById("general")->insertBefore($form_group, $doc->getElementById("general")->childNodes->item(9));

    $args[0] = preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $doc->saveHTML());
    return $args;
}
wCMS::addListener('settings', 'alterAdmin');

?>

<!DOCTYPE HTML>
<!--
	Dimension by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>
	<head>
		<title><?= $Wcms->get('config', 'siteTitle') ?> - <?= $Wcms->page('title') ?></title>
        <meta name="description" content="<?= $Wcms->page('description') ?>">
        <meta name="keywords" content="<?= $Wcms->page('keywords') ?>">

		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />

		<?php if($Wcms->loggedIn){ ?>
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

		?>
	</head>
	<body class="<?= $Wcms->currentPage == 'home' ? "is-preload" : "" ?>">
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
								<li><a href="<?=$Wcms->loggedIn?"/":"#"?><?=$page->slug?>"><?=$page->name; ?></a></li>
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

						<?php if($this->get('config', 'login') === $Wcms->currentPage) {
							$segments = (object)$this->loginView();
							echo "<article id=\"{$Wcms->currentPage}\">
								<h2 class=\"major\">Login</h2>

								{$segments->content}
							</article>";
						} ?>

					</div>

				<!-- Footer -->
					<footer id="footer">
						<p class="copyright"><?= $Wcms->footer() ?> Design: <a href="https://html5up.net">HTML5 UP</a>.</p>
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
