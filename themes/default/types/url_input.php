<?php

if ($property->getSchema()->isMultiValue()) {
	foreach ((array) $property->getValue() as $value) {

?>
<input type="url" name="<?php __($property->getName()); ?>[]"<?php if ($value) { ?> value="<?php __($value); ?>"<?php } ?>>
<?php

	}

?>
<input type="url" id="<?php __($property_id); ?>" name="<?php __($property->getName()); ?>[]" class="new">
<?php

} else {

?>
<input type="url" id="<?php __($property_id); ?>" name="<?php __($property->getName()); ?>"<?php if ($property->hasValue()) { ?> value="<?php __($property->getValue()); ?>"<?php } ?>>
<?php

}

?>
