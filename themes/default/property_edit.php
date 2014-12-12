<?php

$property_id = __class('edit-node-property-' . $property->getName(), false);

require __path('property_label.php');

if ($property->getSchema()->isMultiValue()) {

?>
<div class="multivalue">
<?php

}

require __path('types/' . $property->getSchema()->getType() . '_input.php');
require __path('property_error.php');

if ($property->getSchema()->isMultiValue()) {

?>
</div>
<?php

}
