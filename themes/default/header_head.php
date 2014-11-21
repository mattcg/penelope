<head>
	<meta charset="utf-8">
	<meta name="generator" content="Penelope v<?php __(Karwana\Penelope\VERSION); ?>">
	<title><?php __(_e(strip_tags($title))); ?></title>
	<?php

	if (isset($resources['css'])) {
		foreach ($resources['css'] as $css_url) {

	?>
	<link rel="stylesheet" href="<?php __(_e($css_url)); ?>" type="text/css">
	<?php

		}
	}

	if (isset($resources['js'])) {
		foreach ($resources['js'] as $js_url) {

	?>
	<script src="<?php __(_e($js_url)); ?>" async></script>
	<?php

		}
	}

	?>
</head>
