<?php

if ($property->getSchema()->isMultiValue()) {
	foreach (array_merge($property->getValue(), array(null)) as $value) {
		if ($property->getSchema()->getOption('format.long')) {

?>
<textarea id="<?php __($property_id); ?>" name="<?php __($property->getName()); ?>"><?php __($value); ?></textarea>
<?php

		} else {

?>
<input id="<?php __($property_id); ?>" name="<?php __($property->getName()); ?>"<?php if ($value) { ?> value="<?php __($value); ?>"<?php } ?>>
<?php

		}
	}
} else {
	if ($property->getSchema()->getOption('format.long')) {

?>
<textarea id="<?php __($property_id); ?>" name="<?php __($property->getName()); ?>"><?php __($property->getValue()); ?></textarea>
<?php

	} else {

?>
<input id="<?php __($property_id); ?>" name="<?php __($property->getName()); ?>"<?php if ($property->getValue()) { ?> value="<?php __($property->getValue()); ?>"<?php } ?>>
<?php

	}
}

?>
