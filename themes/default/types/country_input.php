<?php

use Karwana\Penelope\Types\Country;

if ($property->getSchema()->isMultiValue()) {
	foreach ((array) $property->getValue() as $value) {

?>
<select name="<?php __(_e($property->getName())); ?>[]">
	<option value=""></option>
<?php

		foreach (Country::getCodes() as $code) {

?>
	<option value="<?php __($code); ?>"<?php if (0 === strcasecmp($value, $code)) { ?> selected<?php } ?>><?php __(_e(Country::getName($code, __locale()))); ?></option>
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

		foreach (Country::getCodes() as $code) {

?>
	<option value="<?php __($code); ?>"><?php __(_e(Country::getName($code, __locale()))); ?></option>
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

foreach (Country::getCodes() as $code) {

?>
	<option value="<?php __($code); ?>"<?php if ($property->hasValue() and 0 === strcasecmp($property->getValue(), $code)) { ?> selected<?php } ?>><?php __(_e(Country::getName($code, __locale()))); ?></option>
<?php

}

?>
</select>
<?php

}

?>
