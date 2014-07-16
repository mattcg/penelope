<?php

if ($property->getSchema()->isMultiValue()) {

?>
<ul>
	<?php

	foreach ($property->getValue() as $value) {

	?>
	<li><a href="<?php __(_e($value)); ?>"><?php __(_e($value)); ?></a></li>
	<?php

	}

	?>
</ul>
<?php

} else {

?>
<a href="<?php __(_e($property->getValue())); ?>"><?php __(_e($property->getValue())); ?></a>
<?php

}

?>
