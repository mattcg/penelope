<?php

if ($property->getSchema()->isMultiValue()) {

?>
<ul>
	<?php

	foreach ($property->getValue() as $value) {

	?>
	<li><a href="<?php __($value); ?>"><?php __($value); ?></a></li>
	<?php

	}

	?>
</ul>
<?php

} else {

?>
<a href="<?php __($property->getValue()); ?>"><?php __($property->getValue()); ?></a>
<?php

}

?>
