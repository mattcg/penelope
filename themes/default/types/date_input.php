<?php

if ($property->getSchema()->isMultiValue()) {
	foreach ((array) $property->getValue() as $value) {

?>
<input type="date" name="<?php __(_e($property->getName())); ?>[]"<?php if ($value) { ?> value="<?php __(date('Y-m-d', $value)); ?>"<?php } ?>>
<?php

	}

?>
<input type="date" id="<?php __($property_id); ?>" name="<?php __(_e($property->getName())); ?>[]" class="new">
<?php

} else {

?>
<input type="date" id="<?php __($property_id); ?>" name="<?php __(_e($property->getName())); ?>"<?php if ($property->hasValue()) { ?> value="<?php __(date('Y-m-d', $property->getValue())); ?>"<?php } ?>>
<?php

}

?>
