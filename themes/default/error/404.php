<main class="error error-404">
	<div class="body">
		<h1>Not Found</h1>
		<?php

		global $app;

		if (!empty($error) and $app->config('debug')) {

		?>
		<p><?php __(_e($error->getMessage())); ?></p>
		<pre><?php __(_e($error->getTraceAsString())); ?></pre>
		<?php

		} else {

		?>
		<p>The requested object was not found.</p>
		<?php

		}

		?>
	</div>
</main>
