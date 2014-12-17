<?php

use Karwana\Penelope\Types\Image;

if ($property->getSchema()->isMultiValue()) {

?>
<ol class="multivalue">
<?php

	foreach ((array) $property->getValue() as $value) {

		// Nothing useful can be shown for invalid images, not even the original input.
		// The user will just have to attach the image again.
		if (!Image::isValid($value)) {
			continue;
		}

		$path = $value[Image::PATH_KEY];
		$size = getimagesize(Image::getSystemPath($path))[3];

?>
	<li><img src="/uploads/<?php __(_e($path)); ?>" alt="" <?php __($size); ?>> <input type="checkbox" name="<?php __(_e($property->getName())); ?>[]" value="<?php __(_e(Image::serialize($value))); ?>" checked></li>
<?php

	}

?>
	<li><input type="file" accept="image/*" id="<?php __($property_id); ?>" name="<?php __(_e($property->getName())); ?>[]" class="new"></li>
</ol>
<?php

} else if ($property->hasValue() and Image::isValid($value = $property->getValue())) {

	$path = $value[Image::PATH_KEY];
	$size = getimagesize(Image::getSystemPath($path))[3];

?>
<img src="/uploads/<?php __(_e($path)); ?>" alt="" <?php __($size); ?>>
<input id="<?php __($property_id); ?>" type="checkbox" name="<?php __(_e($property->getName())); ?>" value="<?php __(_e(Image::serialize($value))); ?>" checked>
<?php

} else {

?>
<input type="file" accept="image/*" id="<?php __($property_id); ?>" name="<?php __(_e($property->getName())); ?>">
<?php

}

?>
