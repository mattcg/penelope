<?php

if ($property->getSchema()->isMultiValue()) {
	foreach (array_merge($property->getValue(), array(null)) as $value) {

?>
<input type="url" id="<?php __($property_id); ?>" name="<?php __($property->getName()); ?>"<?php if ($value) { ?> value="<?php __($value); ?>"<?php } ?>>
<?php

	}
} else {

?>
<input type="url" id="<?php __($property_id); ?>" name="<?php __($property->getName()); ?>"<?php if ($property->getValue()) { ?> value="<?php __($property->getValue()); ?>"<?php } ?>>
<?php

}

?>
