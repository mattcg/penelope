<?php

if ($property->getSchema()->isMultiValue()) {
	$values = (array) $property->getValue();

?>
<select multiple name="<?php __(_e($property->getName())); ?>[]">
	<?php

	foreach ($property->getSchema()->getOption('allowed') as $allowed_value) {

	?>
	<option <?php if (in_array($allowed_value, $values, true)) __('selected '); ?>value="<?php __(_e($allowed_value)); ?>"><?php __(_e($allowed_value)); ?></option>
	<?php

	}

	?>
</select>
<?php

} else {
	$value = $property->getValue();

?>
<select name="<?php __(_e($property->getName())); ?>">
	<option value=""><?php __(_m('option_none')); ?></option>
	<?php

	foreach ($property->getSchema()->getOption('allowed') as $allowed_value) {

	?>
	<option <?php if ($value === $allowed_value) __('selected '); ?>value="<?php __(_e($allowed_value)); ?>"><?php __(_e($allowed_value)); ?></option>
	<?php

	}

	?>
</select>
<?php

}

?>
