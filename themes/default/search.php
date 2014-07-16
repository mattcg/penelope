<main class="collection nodes">
	<header class="search">
		<h1><?php __($title); ?></h1>
	</header>
	<div class="body">
		<?php

		if (empty($nodes)) {

		?>
		<p><?php __(_m('search_no_results', $query)); ?></p>
		<?php

		} else {

		?>
		<nav class="collection nodes">
			<ul>
				<?php

				foreach ($nodes as $node) {

				?>
				<li class="object"><a href="<?php __(_e($node->getPath())); ?>" title="<?php __(_e($node->getTitle())); ?>"><?php __(_e($node->getTitle())); ?></a></li>
				<?php

				}

				?>
			</ul>
		</nav>
		<?php

		}

		?>
	</div>
</main>
