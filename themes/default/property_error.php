<?php

use Karwana\Penelope\TransientProperty;

if ($property instanceof TransientProperty and $property->getError()) {

?>
<p class="error"><?php __(_e($property->getError()->getMessage())); ?></p>
<?php

}

?>
