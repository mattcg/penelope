<main class="error">
	<header class="main-header error">
		<nav class="breadcrumb" itemprop="breadcrumb">
			<span class="main-type"><?php __(_m('error_breadcrumb')); ?></span>
		</nav>
	</header>
	<div class="main-body error">
		<?php

		global $app;

		if ($app->response->isNotFound()) {

		?>
		<p><?php __(_m('error_404')); ?></p>
		<?php

		} else if ($app->response->isForbidden()) {

		?>
		<p><?php __(_m('error_403')); ?></p>
		<?php

		} else {

		?>
		<p><?php __(_m('error_unknown')); ?></p>
		<?php

		}

		if (!empty($error) and $app->config('debug')) {

		?>
		<p><?php __(_m('error_details_caption')); ?></p>
		<hr>
		<pre><?php __(_e($error->getMessage())); ?></pre>
		<pre><?php __(_e($error->getTraceAsString())); ?></pre>
		<?php

		}

		?>
	</div>
</main>
