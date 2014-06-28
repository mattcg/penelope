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
	<li><?php __($value); ?></li>
	<?php

	}

	?>
</ul>
<?php

} else {

?>
<div class="format-<?php __class($format); ?>"><?php __($property->getValue()); ?></div>
<?php

}

?>
