<nav class="application-menu">
	<ul>
		<?php

		global $app;

		if (isset($pages)) {
			foreach ($pages as $name => $title_key) {

		?>
		<li class="<?php __class('menu-' . $name); ?>"><a href="<?php __(_e($app->router->urlFor($name))); ?>" class="button <?php __class($name); ?>"><span class="button-label"><?php __(_m($title_key)); ?></span></a></li>
		<?php

			}
		}

		if ($app->router->hasNamedRoute('login')) {

		?>
		<li class="menu-login"><a href="<?php __(_e($app->router->urlFor('login'))); ?>" class="button login"><span class="button-label"><?php __(_m('application_login_button_text')); ?></span></a></li>
		<?php

		}

		?>
	</ul>
</nav>
