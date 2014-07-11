<?php

use Karwana\Penelope\Types\File;

if ($property->getSchema()->isMultiValue()) {

	foreach ((array) $property->getValue() as $value) {

		// Nothing useful can be shown for invalid files, not even the original input.
		// The user will just have to attach the file again.
		if (!File::isValid($value)) {
			continue;
		}

?>
<a href="/uploads/<?php __($value[File::PATH_KEY]); ?>" alt=""><?php __($value[File::NAME_KEY]); ?></a>
<input type="checkbox" name="<?php __($property->getName()); ?>[]" value="<?php __(File::serialize($value)); ?>" checked>
<?php

	}

?>
<input type="file" id="<?php __($property_id); ?>" name="<?php __($property->getName()); ?>[]" class="new">
<?php

} else if ($property->hasValue() and File::isValid($value = $property->getValue())) {

?>
<a href="/uploads/<?php __($value[File::PATH_KEY]); ?>" alt=""><?php __($value[File::NAME_KEY]); ?></a>
<input id="<?php __($property_id); ?>" type="checkbox" name="<?php __($property->getName()); ?>" value="<?php __(File::serialize($value)); ?>" checked>
<?php

} else {

?>
<input id="<?php __($property_id); ?>" type="file" id="<?php __($property_id); ?>" name="<?php __($property->getName()); ?>" class="new">
<?php

}

?>
