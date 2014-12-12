<?php

use Karwana\Penelope\Types\Country;

$countries = array();
foreach (Country::getCodes() as $code) {
	$countries[$code] = Country::getName($code, __locale());
}

// Sort by country name using Unicode Collation Algorithm rules.
$collator = \Collator::create('root');
uasort($countries, function($a, $b) use ($collator) {
	return $collator->compare($a, $b);
});

if ($property->getSchema()->isMultiValue()) {
	foreach ((array) $property->getValue() as $value) {

?>
<select name="<?php __(_e($property->getName())); ?>[]">
	<option value=""><?php __(_m('option_none')); ?></option>
<?php

		foreach ($countries as $code => $name) {

?>
	<option value="<?php __($code); ?>"<?php if (0 === strcasecmp($value, $code)) { ?> selected<?php } ?>><?php __(_e($name)); ?></option>
<?php

		}
?>
</select>
<?php

	}

?>
<select id="<?php __($property_id); ?>" name="<?php __(_e($property->getName())); ?>[]" class="new">
	<option value=""><?php __(_m('option_none')); ?></option>
<?php

		foreach ($countries as $code => $name) {

?>
	<option value="<?php __($code); ?>"><?php __(_e($name)); ?></option>
<?php

		}
?>
</select>
<?php

} else {

?>
<select id="<?php __($property_id); ?>" name="<?php __(_e($property->getName())); ?>">
	<option value=""><?php __(_m('option_none')); ?></option>
<?php

foreach ($countries as $code => $name) {

?>
	<option value="<?php __($code); ?>"<?php if ($property->hasValue() and 0 === strcasecmp($property->getValue(), $code)) { ?> selected<?php } ?>><?php __(_e($name)); ?></option>
<?php

}

?>
</select>
<?php

}

?>
