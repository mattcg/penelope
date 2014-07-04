<main class="collection edges">
	<?php

	require __path('edge_header.php');

	?>
	<div class="body">
		<?php

		if (empty($edges)) {

		?>
		<p>There aren't any <?php __($edge_schema->getName()); ?> relationships.</p>
		<?php

		} else {

		?>
		<nav class="collection edges">
			<ul>
				<?php

				foreach ($edges as $edge) {

				?>
				<li><a href="<?php __($edge->getPath()); ?>" title="<?php __($edge->getTitle()); ?>"><?php __($edge->getTitle()); ?></a></li>
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
