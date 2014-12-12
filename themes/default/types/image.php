<?php

use Karwana\Penelope\Types\File;

if ($property->getSchema()->isMultiValue()) {

?>
<ol class="multivalue">
	<?php

	foreach ($property->getValue() as $value) {
		$size = getimagesize(File::getSystemPath($value[File::PATH_KEY]))[3];

	?>
	<li><figure><img src="/uploads/<?php __(_e($value[File::PATH_KEY])); ?>" alt="" <?php __($size); ?> title="<?php __(_e($value[File::NAME_KEY])); ?>"></figure></li>
	<?php

	}

	?>
</ol>
<?php

} else {
	$value = $property->getValue();
	$path = $value[File::PATH_KEY];
	$size = getimagesize(File::getSystemPath($path))[3];

?>
<figure>
	<img src="/uploads/<?php __(_e($path)); ?>" alt="" <?php __($size); ?> title="<?php __(_e($value[File::NAME_KEY])); ?>">
</figure>
<?php

}

?>
