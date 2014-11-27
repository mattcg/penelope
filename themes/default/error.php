<main class="error">
	<header class="main-header error">
		<nav class="breadcrumb" itemprop="breadcrumb">
			<span class="main-type"><?php __(_m('error_breadcrumb')); ?></span>
		</nav>
	</header>
	<div class="main-body error">
		<p><?php __(_e($exception->getMessage())); ?></p>
	</div>
</main>
