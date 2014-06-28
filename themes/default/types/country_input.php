<?php

use Karwana\Penelope\Types\Country;

if ($property->getSchema()->isMultiValue()) {
	foreach ((array) $property->getValue() as $value) {

?>
<select name="<?php __($property->getName()); ?>[]">
	<option value=""></option>
<?php

		foreach (Country::getCountries() as $country) {

?>
	<option value="<?php __($country['code']); ?>"<?php if (0 === strcasecmp($value, $country['code'])) { ?> selected<?php } ?>><?php __($country['name']); ?></option>
<?php

		}
?>
</select>
<select id="<?php __($property_id); ?>" name="<?php __($property->getName()); ?>[]" class="new">
	<option value=""></option>
<?php

		foreach (Country::getCountries() as $country) {

?>
	<option value="<?php __($country['code']); ?>"><?php __($country['name']); ?></option>
<?php

		}
?>
</select>
<?php
	}

} else {

?>
<select id="<?php __($property_id); ?>" name="<?php __($property->getName()); ?>">
	<option value=""></option>
<?php

foreach (Country::getCountries() as $country) {

?>
	<option value="<?php __($country['code']); ?>"<?php if ($property->hasValue() and 0 === strcasecmp($property->getValue(), $country['code'])) { ?> selected<?php } ?>><?php __($country['name']); ?></option>
<?php

}

?>
</select>
<?php

}

?>
