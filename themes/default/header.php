<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
		<meta name="generator" content="Penelope v<?php __(Karwana\Penelope\VERSION); ?>">
		<title><?php __($title); ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
		<?php

		foreach ($resources['css'] as $css_url) {

		?>
		<link rel="stylesheet" href="<?php __($css_url); ?>" type="text/css">
		<?php

		}

		foreach ($resources['js'] as $js_url) {

		?>
		<script src="<?php __($js_url); ?>" async></script>
		<?php

		}

		?>
	</head>
	<body>
		<header>
			<a href="/"><h1>Penelope</h1></a>
		</header>
