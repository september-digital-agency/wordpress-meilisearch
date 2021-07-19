<div class="wrap">
	<h1>Search<span>Developer-friendly plugin to add Meilisearch and indexing to Wordpress.</span></h1>

	<h2>Status</h2>

	<div class="wordpress-meilisearch-box">
		<?php

		if ($index = wordpress_meilisearch_get_index()) :

			$stats = $index->stats();
			$indexID = $index->getUid();

			echo '<p><span class="wordpress-meilisearch-emoij">✅</span>There are ' . $stats['numberOfDocuments'] . ' documents in your index.</p>';
		?>

			<div class="form-inline">

				<form action="admin-post.php" method="post">
					<input type="hidden" name="action" value="reindex">
					<input type="hidden" name="index" value="<?php echo $indexID; ?>">
					<input type="submit" value="Re-index all posts" class="button button-primary">
				</form>

				<form action="admin-post.php" method="post">
					<input type="hidden" name="action" value="clearindex">
					<input type="hidden" name="index" value="<?php echo $indexID; ?>">
					<input type="submit" value="Clear index" class="button button button-secondary">
				</form>

			</div>

		<?php

		else :

			echo '<p>❌	Can\'t create the MeiliSearch Client. Check your settings below.</p>';

		endif;

		?>
	</div>

	<h2>Settings</h2>

	<div class="wordpress-meilisearch-box">
		<form action="options.php" method="post">
			<?php
			settings_fields('wordpress_meilisearch_plugin_options');
			do_settings_sections('wordpress_meilisearch_plugin'); ?>
			<input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Save'); ?>" />
		</form>
	</div>

	<div class="box--credit">
		<small>This plugin is maintained by <a href="https://september.digital" target="_blank">september.digital</a>. Please file issues or requests at github.
		</small>
	</div>

</div>