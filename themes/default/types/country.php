<?php

use Karwana\Penelope\Types\Country;

if ($property->getSchema()->isMultiValue()) {

?>
<ul class="multivalue">
	<?php

	foreach ($property->getValue() as $value) {

	?>
	<li><?php __(_e(Country::getCountryName($value))); ?></li>
	<?php

	}

	?>
</ul>
<?php

} else {

	__(_e(Country::getCountryName($property->getValue())));

}

?>
