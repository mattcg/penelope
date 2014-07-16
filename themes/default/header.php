<!DOCTYPE html>
<html lang="en-US">
	<head>
		<meta charset="utf-8">
		<meta name="generator" content="Penelope v<?php __(Karwana\Penelope\VERSION); ?>">
		<title><?php __(_e(strip_tags($title))); ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
		<?php

		foreach ($resources['css'] as $css_url) {

		?>
		<link rel="stylesheet" href="<?php __(_e($css_url)); ?>" type="text/css">
		<?php

		}

		foreach ($resources['js'] as $js_url) {

		?>
		<script src="<?php __(_e($js_url)); ?>" async></script>
		<?php

		}

		?>
	</head>
	<body>
		<header>
			<a href="/" title="<?php __(_a('application_title')); ?>"><h1><?php __(_m('application_title')); ?></h1></a>
			<form action="/search" class="search">
				<input name="q" placeholder="<?php __(_m('search_placeholder')); ?>"<?php if (isset($query)) { ?> value="<?php __(_e($query)); ?>"<?php } ?>>
				<input type="submit" value="<?php __(_m('search_submit')); ?>">
			</form>
		</header>
