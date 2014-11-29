<?php

if ($property->getSchema()->isMultiValue()) {
	foreach ((array) $property->getValue() as $value) {

?>
<input type="datetime" name="<?php __(_e($property->getName())); ?>[]"<?php if ($value) { ?> value="<?php __(date('Y-m-d\TH:i:sP', $value)); ?>"<?php } ?>>
<?php

	}

?>
<input type="datetime" id="<?php __($property_id); ?>" name="<?php __(_e($property->getName())); ?>[]" class="new">
<?php

} else {

?>
<input type="datetime" id="<?php __($property_id); ?>" name="<?php __(_e($property->getName())); ?>"<?php if ($property->hasValue()) { ?> value="<?php __(date('Y-m-d\TH:i:sP', $property->getValue())); ?>"<?php } ?>>
<?php

}

?>
