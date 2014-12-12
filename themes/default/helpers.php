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

function _s($string) {

	// Strip tags. For use with outputting messages as tag attributes.
	return _e(strip_tags($string));
}

function _m() {
	global $app;

	return call_user_func_array(array($app->view(), '_m'), func_get_args());
}

function _a() {
	global $app;

	return call_user_func_array(array($app->view(), '_a'), func_get_args());
}

function __label(Penelope\Property $property) {
	$label = $property->getLabel();

	if ($property->getName() === $label) {
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

function __datetime($value, $message_key = 'property_datetime_value') {
	if (!$value) {
		return;
	}

	// Only accept valid Unix timestamp.
	// ctype_digit can't be used as value might be negative for dates before 1970.
	if (is_numeric($value)) {
		__(_m($message_key, $value));
	} else {
		__(_e($value));
	}
}

function __date($value) {
	__datetime($value, 'property_date_value');
}

function __path($file) {
	global $app;

	return $app->view()->getTemplatePathname($file);
}

function __optional_require($file) {
	global $app;

	$path = __path($file);

	if (is_file($path)) {
		require $path;
	}
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

function __locale() {
	global $app;

	return $app->view->getMessageFormat()->getLocale();
}
