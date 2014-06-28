<?php

use Karwana\Penelope\Property;

function __() {
	global $app;

	call_user_func_array(array($app->view(), '__'), func_get_args());
}

function __label(Property $property) {
	$label = $property->getSchema()->getOption('label');

	if (!$label) {
		$label = ucfirst(str_replace('_', ' ', $property->getName()));
	}

	__($label);
}

function __class($string, $echo = true) {
	$string = str_replace(array(' ', '_'), '-', $string);
	$string = strtolower($string);

	// Strip out anything that isn't an underscore or a Unicode letter.
	$string = preg_replace('/[^\p{L}\-]+/', '', $string);
	if ($echo) {
		__($string);
	} else {
		return $string;
	}
}

function __datetime($value, $format = 'j M Y H:i:s e') {
	if (!$value) {
		return;
	}

	if (ctype_digit($value)) {
		__(date($format, $value));
	} else {
		__($value);
	}
}

function __date($value) {
	__datetime($value, 'j M Y');
}

function __path($file) {
	global $app;

	return $app->view()->getTemplatePathname($file);
}