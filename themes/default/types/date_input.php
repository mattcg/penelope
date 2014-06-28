<?php

if ($property->getSchema()->isMultiValue()) {
	foreach ((array) $property->getValue() as $value) {

?>
<input name="<?php __($property->getName()); ?>"<?php if ($value) { ?> value="<?php __date($value); ?>"<?php } ?>>
<?php

	}

?>
<input id="<?php __($property_id); ?>" name="<?php __($property->getName()); ?>" class="new">
<?php

} else {

?>
<input id="<?php __($property_id); ?>" name="<?php __($property->getName()); ?>"<?php if ($property->hasValue()) { ?> value="<?php __date($property->getValue()); ?>"<?php } ?>>
<?php

}

?>
