<?php

if ($property->getSchema()->getOption('format.long')) {
	$format = 'long';
} else {
	$format = 'short';
}

if ($property->getSchema()->isMultiValue()) {

?>
<ul class="multivalue format-<?php __class($format); ?>">
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
<div class="format-<?php __class($format); ?>"><?php __(_e($property->getValue())); ?></div>
<?php

}

?>
