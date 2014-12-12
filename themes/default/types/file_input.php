<?php

use Karwana\Penelope\Types\File;

if ($property->getSchema()->isMultiValue()) {

?>
<ol class="multivalue">
<?php

	foreach ((array) $property->getValue() as $value) {

		// Nothing useful can be shown for invalid files, not even the original input.
		// The user will just have to attach the file again.
		if (!File::isValid($value)) {
			continue;
		}

?>
	<li><a href="/uploads/<?php __(_e($value[File::PATH_KEY])); ?>" alt=""><?php __(_e($value[File::NAME_KEY])); ?></a> <input type="checkbox" name="<?php __(_e($property->getName())); ?>[]" value="<?php __(_e(File::serialize($value))); ?>" checked></li>
<?php

	}

?>
	<li><input type="file" id="<?php __($property_id); ?>" name="<?php __(_e($property->getName())); ?>[]" class="new"></li>
</ol>
<?php

} else if ($property->hasValue() and File::isValid($value = $property->getValue())) {

?>
<a href="/uploads/<?php __(_e($value[File::PATH_KEY])); ?>" alt=""><?php __(_e($value[File::NAME_KEY])); ?></a>
<input id="<?php __(_e($property_id)); ?>" type="checkbox" name="<?php __(_e($property->getName())); ?>" value="<?php __(_e(File::serialize($value))); ?>" checked>
<?php

} else {

?>
<input id="<?php __($property_id); ?>" type="file" id="<?php __($property_id); ?>" name="<?php __(_e($property->getName())); ?>" class="new">
<?php

}

?>
