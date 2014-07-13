<?php

use Karwana\Penelope\Types\File;

if ($property->getSchema()->isMultiValue()) {

?>
<ul class="multivalue">
	<?php

	foreach ($property->getValue() as $value) {
		$size = getimagesize(File::getSystemPath($value[File::PATH_KEY]))[3];

	?>
	<li><a href="/uploads/<?php __(_e($value[File::PATH_KEY])); ?>" title="<?php __(_e($value[File::NAME_KEY])); ?>"><img src="/uploads/<?php __(_e($value[File::PATH_KEY])); ?>" alt="" <?php __($size); ?>></a></li>
	<?php

	}

	?>
</ul>
<?php

} else {
	$value = $property->getValue();
	$path = $value[File::PATH_KEY];
	$size = getimagesize(File::getSystemPath($path))[3];

?>
<a href="/uploads/<?php __(_e($path)); ?>" title="<?php __(_e($value[File::NAME_KEY])); ?>"><img src="/uploads/<?php __(_e($path)); ?>" alt="" <?php __($size); ?>></a>
<?php

}

?>
