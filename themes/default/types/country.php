<?php

use Karwana\Penelope\Types\Country;

if ($property->getSchema()->isMultiValue()) {

?>
<ul class="multivalue">
	<?php

	foreach ($property->getValue() as $value) {

	?>
	<li><?php __(_m('countries.' . $value)); ?></li>
	<?php

	}

	?>
</ul>
<?php

} else {

	__(_m('countries.' . $property->getValue()));

}

?>
