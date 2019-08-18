<?php

global $Wcms;

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
$Wcms->addListener('settings', 'alterAdmin');

 ?>
