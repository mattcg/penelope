<?php

if ($property->getSchema()->isMultiValue()) {

?>
<ul class="multivalue">
	<?php

	foreach ($property->getValue() as $value) {

	?>
	<li><?php __(_e($value)); ?></li>
	<?php

	}

	?>
</ul>
<?php

} else {

?>
<p><?php __(_e($property->getValue())); ?></p>
<?php

}

?>
