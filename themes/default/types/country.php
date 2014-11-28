<?php

use Karwana\Penelope\Types\Country;

if ($property->getSchema()->isMultiValue()) {

?>
<ul class="multivalue">
	<?php

	foreach ($property->getValue() as $value) {

	?>
	<li><?php __(_e(Country::getName($value, __locale()))); ?></li>
	<?php

	}

	?>
</ul>
<?php

} else {

	__(_e(Country::getName($property->getValue(), __locale())));

}

?>
