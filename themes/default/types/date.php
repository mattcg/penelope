<?php

if ($property->getSchema()->isMultiValue()) {

?>
<ul class="multivalue">
	<?php

	foreach ($property->getValue() as $value) {

	?>
	<li><time datetime="<?php __(date('Y-m-d', $value)); ?>"><?php __date($value); ?></time></li>
	<?php

	}

	?>
</ul>
<?php

} else {

?>
<time datetime="<?php __(date('Y-m-d', $property->getValue())); ?>"><?php __date($property->getValue()); ?></time>
<?php

}

?>
