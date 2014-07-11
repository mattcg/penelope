<?php

use Karwana\Penelope;

function __($string) {
	global $app;

	$app->view()->__($string);
}

function _e($string) {
	global $app;

	return $app->view()->_e($string);
}

function _m() {
	global $app;

	return call_user_func_array(array($app->view(), '_m'), func_get_args());
}

function __label(Penelope\Property $property) {
	$label = $property->getSchema()->getOption('label');

	if (!$label) {
		$label = ucfirst(str_replace('_', ' ', $property->getName()));
	}

	__(_e($label));
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
		__(_e($value));
	}
}

function __date($value) {
	__datetime($value, 'j M Y');
}

function __path($file) {
	global $app;

	return $app->view()->getTemplatePathname($file);
}

function __formenc(Penelope\ObjectSchema $object_schema) {
	foreach ($object_schema->getProperties() as $property_schema) {
		if (in_array($property_schema->getType(), array('file', 'image'))) {
			__('multipart/form-data');
			return;
		}
	}

	__('application/x-www-form-urlencoded');
}
