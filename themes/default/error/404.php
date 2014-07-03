<main class="error error-404">
	<div class="body">
		<h1>Not Found</h1>
		<p><?php if (!empty($error)) echo $error->getMessage(); else 'The requested object was not found.'; ?></p>
	</div>
</main>
