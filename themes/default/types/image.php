<?php

use Karwana\Penelope\Types\File;

if ($property->getSchema()->isMultiValue()) {

?>
<ul class="multivalue">
	<?php

	foreach ($property->getValue() as $value) {
		$size = getimagesize(File::getSystemPath($value))[3];

	?>
	<li><img src="/uploads/<?php __($value[File::PATH_KEY]); ?>" alt="" <?php __($size); ?>></li>
	<?php

	}

	?>
</ul>
<?php

} else {
	$path = $property->getValue()[File::PATH_KEY];
	$size = getimagesize(File::getSystemPath($path))[3];

?>
<img src="/uploads/<?php __($path); ?>" alt="" <?php __($size); ?>>
<?php

}

?>
