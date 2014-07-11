<?php

if ($property->getSchema()->isMultiValue()) {
	foreach ((array) $property->getValue() as $value) {
		if ($property->getSchema()->getOption('format.long')) {

?>
<textarea name="<?php __(_e($property->getName())); ?>[]"><?php __(_e($value)); ?></textarea>
<?php

		} else {

?>
<input name="<?php __(_e($property->getName())); ?>[]"<?php if ($value) { ?> value="<?php __(_e($value)); ?>"<?php } ?>>
<?php

		}
	}

	if ($property->getSchema()->getOption('format.long')) {

?>
<textarea id="<?php __($property_id); ?>" name="<?php __(_e($property->getName())); ?>[]" class="new"><?php __(_e($value)); ?></textarea>
<?php

	} else {

?>
<input id="<?php __($property_id); ?>" name="<?php __(_e($property->getName())); ?>[]" class="new">
<?php

	}
} else {
	if ($property->getSchema()->getOption('format.long')) {

?>
<textarea id="<?php __($property_id); ?>" name="<?php __(_e($property->getName())); ?>"><?php __(_e($property->getValue())); ?></textarea>
<?php

	} else {

?>
<input id="<?php __($property_id); ?>" name="<?php __(_e($property->getName())); ?>"<?php if ($property->hasValue()) { ?> value="<?php __(_e($property->getValue())); ?>"<?php } ?>>
<?php

	}
}

?>
