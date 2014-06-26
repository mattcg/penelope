<?php

use Karwana\Penelope\Types\Country;

if ($property->getSchema()->isMultiValue()) {
	foreach (array_merge($property->getValue(), array(null)) as $value) {

?>
<select id="<?php __($property_id); ?>" name="<?php __($property->getName()); ?>[]">
	<option value=""></option>
<?php

		foreach (Country::getCountries() as $country) {

?>
	<option value="<?php __($country['code']); ?>"<?php if (0 === strcasecmp($value, $country['code'])) { ?> selected<?php } ?>><?php __($country['name']); ?></option>
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
	<option value="<?php __($country['code']); ?>"<?php if (0 === strcasecmp($property->getValue(), $country['code'])) { ?> selected<?php } ?>><?php __($country['name']); ?></option>
<?php

}

?>
</select>
<?php

}

?>
