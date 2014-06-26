<?php

if ($property->getSchema()->isMultiValue()) {

?>
<ul class="multivalue">
	<?php

	foreach ($property->getValue() as $value) {

	?>
	<li><?php __($value); ?></li>
	<?php

	}

	?>
</ul>
<?php

} else {

	__($property->getValue());

}

?>
