<form action="/search" class="application-search">
	<input class="search-query" type="search" name="q" placeholder="<?php __(_m('search_placeholder')); ?>"<?php if (isset($query)) { ?> value="<?php __(_e($query)); ?>"<?php } ?>>
	<input class="search-submit" type="submit" value="<?php __(_m('search_submit')); ?>">
</form>
