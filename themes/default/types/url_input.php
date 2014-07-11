<?php

if ($property->getSchema()->isMultiValue()) {
	foreach ((array) $property->getValue() as $value) {

?>
<input type="url" name="<?php __(_e($property->getName())); ?>[]"<?php if ($value) { ?> value="<?php __(_e($value)); ?>"<?php } ?>>
<?php

	}

?>
<input type="url" id="<?php __($property_id); ?>" name="<?php __(_e($property->getName())); ?>[]" class="new">
<?php

} else {

?>
<input type="url" id="<?php __($property_id); ?>" name="<?php __(_e($property->getName())); ?>"<?php if ($property->hasValue()) { ?> value="<?php __(_e($property->getValue())); ?>"<?php } ?>>
<?php

}

?>
