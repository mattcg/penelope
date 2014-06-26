<?php

if ($property->getSchema()->isMultiValue()) {

?>
<ul class="multivalue">
	<?php

	foreach ($property->getValue() as $value) {

	?>
	<li><time datetime="<?php __(date('Y-m-d\TH:i:sP', $value)); ?>"><?php __datetime($value); ?></time></li>
	<?php

	}

	?>
</ul>
<?php

} else {

?>
<time datetime="<?php __(date('Y-m-d\TH:i:sP', $property->getValue())); ?>"><?php __datetime($property->getValue()); ?></time>
<?php

}

?>
