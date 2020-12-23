<?php
	$fields = get_option( 'hmr-fields' );
?>
<div id="wrap">

	<h2><?php _e( 'Search Filter', 'hmr' ); ?> 
	</h2>
	<a class="button" href="?page=search-filter-new"><?php _e( 'Create new search filter', 'hmr' ); ?></a>
	<hr />
	<div class="hmr-half">
	<?php if( is_array( $fields ) && count( $fields ) > 0 ): ?>
	<table class="wp-list-table widefat fixed pages">
		<thead>
			<tr>
				<th><?php _e( 'Name', 'hmr' ); ?></th>
				<th><?php _e( 'Shortcode', 'hmr' ); ?></th>
				<th><?php _e( 'Delete', 'hmr' ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th><?php _e( 'Name', 'hmr' ); ?></th>
				<th><?php _e( 'Shortcode', 'hmr' ); ?></th>
				<th><?php _e( 'Delete', 'hmr' ); ?></th>
			</tr>
		</tfoot>
		<tbody id="the-list">
			<?php foreach( $fields as $field ): ?>
			<tr>
				<td><a href="?page=search-filter-edit&ID=<?php echo $field['name']; ?>"><?php if( !isset( $field['title'] ) || trim( $field['title'] ) == '' )echo $field['name']; else echo $field['title'];?></a></td>
				<td><code>[search-form id="<?php echo $field['name']; ?>"]</code></td>
				<td><a href="#" class="hmr-form-delete button" data-id="<?php echo $field['name']; ?>"><?php _e( 'Delete', 'hmr' ); ?></a></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php else: ?>
	<p><?php _e( 'No Search Filter yet.', 'hmr' ); ?></p>
	<?php endif; ?>
	</div>

	
	<hr class="hmr"/>
	<p><strong>Current Version: <?php echo HMR_CURRENT_VERSION; ?></strong></p>
</div>