<!DOCTYPE html>
<html lang="<?php __($locale); ?>"<?php

	if (isset($document_classes)) {

	?> class="<?php

	foreach ($document_classes as $document_class) {
		__class($document_class);
	}

	?>"<?php

	}

	?>>
	<?php

	require __path('header_head.php');

	?>
	<body itemscope itemtype="http://schema.org/WebPage">
		<?php

		require __path('header_header.php');

		?>