<?php

if ($property->hasValue()) {

	$type_class = 'type-' . $property->getSchema()->getType();
	if ($property->getSchema()->isMultiValue()) {
		$type_class .= ' multivalue';
	}

?>
<dt class="<?php __($type_class); ?>"><?php __label($property); ?></dt>
<dd class="<?php __($type_class); ?>">
<?php

require __path('types/' . $property->getSchema()->getType() . '.php');

?>
</dd>
<?php

}

?>
