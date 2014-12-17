<?php

use Karwana\Penelope\Types\File;

if ($property->getSchema()->isMultiValue()) {

?>
<ol class="multivalue">
	<?php

	foreach ($property->getValue() as $value) {

	?>
	<li><a target="_blank" href="/uploads/<?php __(_e($value[File::PATH_KEY])); ?>" alt=""><?php __(_e($value[File::NAME_KEY])); ?></a></li>
	<?php

	}

	?>
</ol>
<?php

} else {
	$value = $property->getValue();

?>
<a target="_blank" href="/uploads/<?php __(_e($value[File::PATH_KEY])); ?>" alt=""><?php __(_e($value[File::NAME_KEY])); ?></a>
<?php

}

?>
