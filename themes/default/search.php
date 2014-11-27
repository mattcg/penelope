<main class="collection nodes search">
	<header class="main-header search">
		<nav class="breadcrumb" itemprop="breadcrumb">
			<a class="main-type" href="/"><?php __(_m('search_breadcrumb')); ?></a>
			<?php

			if ($query) {

			?>
			<span class="divider">→</span>
			<?php

				__(_e($query));
			}

			?>
		</nav>
		<p class="search-result-count"><?php __(_m('search_result_count', $result_count)); ?></p>
	</header>
	<div class="main-body search">
		<?php

		if (empty($nodes)) {

		?>
		<p><?php __(_m('search_no_results', $query)); ?></p>
		<?php

		} else {

		?>
		<nav class="collection nodes search">
			<ul>
				<?php

				foreach ($nodes as $node) {

				?>
				<li class="object <?php __class('object-type-' . $node->getSchema()->getName()); ?>">
					<a class="object-link" href="<?php __(_e($node->getPath())); ?>" title="<?php __(_e($node->getTitle())); ?>">
						<h1 class="object-title"><?php __(_e($node->getTitle())); ?></h1>
						<h2 class="object-subtitle"><?php __(_e($node->getSchema()->getDisplayName())); ?></h2>
					</a>
				</li>
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
