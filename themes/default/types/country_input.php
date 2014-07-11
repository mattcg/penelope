<?php

use Karwana\Penelope\Types\Country;

if ($property->getSchema()->isMultiValue()) {
	foreach ((array) $property->getValue() as $value) {

?>
<select name="<?php __(_e($property->getName())); ?>[]">
	<option value=""></option>
<?php

		foreach (Country::getCountries() as $country) {

?>
	<option value="<?php __($country['code']); ?>"<?php if (0 === strcasecmp($value, $country['code'])) { ?> selected<?php } ?>><?php __(_e($country['name'])); ?></option>
<?php

		}
?>
</select>
<?php

	}

?>
<select id="<?php __($property_id); ?>" name="<?php __(_e($property->getName())); ?>[]" class="new">
	<option value=""></option>
<?php

		foreach (Country::getCountries() as $country) {

?>
	<option value="<?php __($country['code']); ?>"><?php __(_e($country['name'])); ?></option>
<?php

		}
?>
</select>
<?php

} else {

?>
<select id="<?php __($property_id); ?>" name="<?php __(_e($property->getName())); ?>">
	<option value=""></option>
<?php

foreach (Country::getCountries() as $country) {

?>
	<option value="<?php __($country['code']); ?>"<?php if ($property->hasValue() and 0 === strcasecmp($property->getValue(), $country['code'])) { ?> selected<?php } ?>><?php __(_e($country['name'])); ?></option>
<?php

}

?>
</select>
<?php

}

?>
