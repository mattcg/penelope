<?php

use Karwana\Penelope\Types\File;

if ($property->getSchema()->isMultiValue()) {

?>
<ul class="multivalue">
	<?php

	foreach ($property->getValue() as $value) {

	?>
	<li><a href="/uploads/<?php __(_e($value[File::PATH_KEY])); ?>" alt=""><?php __(_e($value[File::NAME_KEY])); ?></a></li>
	<?php

	}

	?>
</ul>
<?php

} else {
	$value = $property->getValue();

?>
<a href="/uploads/<?php __(_e($value[File::PATH_KEY])); ?>" alt=""><?php __(_e($value[File::NAME_KEY])); ?></a>
<?php

}

?>
