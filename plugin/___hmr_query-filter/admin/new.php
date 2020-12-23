<?php
	
	if( isset( $_POST['hmr'] ) )
		$hmr = $_POST['hmr'];
?>

<div id="wrap" class="hmr-wrap">
	<small><a href="?page=search-filter"><?php _e( 'Search Filter', 'hmr' ); ?></a> &raquo;</small>
	<h2><?php _e( 'New search filter', 'hmr' ); ?></h2>
	<hr />
	<?php if( !isset( $_POST['hmr_step'] ) ): ?>
	<form method="post" class="hmr-form">
		<input type="hidden" value="1" name="hmr_step" />
		<?php if( isset( $hmr ) ): foreach( $hmr as $key => $val ):
			if( is_array( $val ) ):
			foreach( $val as $v ):
			?>
			<input type="hidden" name="hmr[<?php echo $key;?>][]" value="<?php echo $v; ?>" />			
			<?php
			endforeach;
			else:
			?>
			<input type="hidden" name="hmr[<?php echo $key;?>]" value="<?php echo $val; ?>" />
			<?php
			endif;
		endforeach; endif; ?>
		<fieldset>
		<legend><?php _e( 'General Settings' ,'hmr' ); ?></legend>
		<section>
			<label for="hmr_id"><?php _e( 'ID', 'hmr' ); ?>:</label>
			<input id="hmr_id" name="hmr[name]" value="" />		
		</section>
		<section>
			<label for="hmr_name"><?php _e( 'Name', 'hmr' ); ?>:</label>
			<input id="hmr_name" name="hmr[title]" value="" />		
		</section>
		<section>
			<label for="hmr_posttype"><?php _e( 'Post Type', 'hmr' ); ?>:</label>
			<select id="hmr_posttype" name="hmr[posttype][]">
				<option></option>
				<?php 				
				$args = array(
					'public'	=> true
				);
				$posttypes = get_post_types( $args, 'objects' );
				foreach( $posttypes as $key => $p ): ?>
				<option value="<?php echo $key; ?>"><?php echo $p->labels->name; ?></option>
				<?php endforeach; ?>
			</select>
		</section>
		<hr />
		<input class="button" type="submit" value="<?php _e( 'Next Step', 'hmr' ); ?> &#10148;" />
		</fieldset>
	</form>
	<?php elseif( $_POST['hmr_step'] == 1 ): ?>
		<h3><?php _e( 'Taxonomies & Postmetas' ,'hmr' ); ?></h3>
		<p><?php _e( 'Please drag taxonomies and PostMeta, you want to use in your form from the left into the right-field search.', 'hmr' ); ?></p>
		<?php
			$metas = get_all_postmetas_from_post_type( $hmr['posttype'] );
		?>
		<ul class="hmr-group1">
			<li><?php _e( 'Taxonomies', 'hmr' ); ?>
				<?php $tax = get_all_post_taxonomies( $hmr['posttype'] ); ?>
				<ul class="hmr-tax-ul">
				<?php foreach( $tax as $key => $t ): ?>
				<li class="hmr-drag"><input name="hmr[tax][]" value="<?php echo $key; ?>" type="hidden" /><?php echo $t->labels->name; ?> (<?php echo $key; ?>)</li>
				<?php endforeach; ?>
				</ul>
			</li>
			
			<li><?php _e( 'Postmeta', 'hmr' ); ?><ul class="hmr-meta-ul">
				<?php foreach( $metas as $key => $val ): ?>
				<li class="hmr-drag"><input name="hmr[meta][]" value="<?php echo $key; ?>" type="hidden" /><?php echo ucfirst( $key ); ?></li>
				<?php endforeach; ?>
			</ul></li>
		</ul>
		
		<form method="post" class="hmr-form">
		<input type="hidden" value="2" name="hmr_step" />
				<?php if( isset( $hmr ) ): foreach( $hmr as $key => $val ):
			if( is_array( $val ) ):
			foreach( $val as $v ):
			?>
			<input type="hidden" name="hmr[<?php echo $key;?>][]" value="<?php echo $v; ?>" />			
			<?php
			endforeach;
			else:
			?>
			<input type="hidden" name="hmr[<?php echo $key;?>]" value="<?php echo $val; ?>" />
			<?php
			endif;
		endforeach; endif; ?>
		<ul class="hmr-group2">
		
		</ul>
		<div class="hmr-clear"></div>
		<hr />
		
		
		<hr />
		<input class="button" type="submit" value="<?php _e( 'Next Step', 'hmr' ); ?> &#10148;" />
	</form>
	<?php elseif( $_POST['hmr_step'] == 3 ): ?>	
	<?php
		global $wpdb;
		if( ! wp_verify_nonce( $_POST['hmr-wpnonce'], 'save-new-hmr' ) )
			wp_die( 'Wrong parameter.' );
		if( !function_exists('is_multisite') || !is_multisite() )
			$file = HMR_DIR . 'templates/template-' . $hmr['name'] . '.php';
		else
			$file = HMR_DIR . 'templates/template-' . $wpdb->blogid . '-' . $hmr['name'] . '.php';
		$fp = fopen( $file, 'w' );
		fwrite( $fp, stripslashes( $_POST['template']['result'] ) );
		fclose( $fp );
		
		
		if( !function_exists('is_multisite') || !is_multisite() )
			$file = HMR_DIR . 'templates/template-' . $hmr['name'] . '-noresult.php';
		else
			$file = HMR_DIR . 'templates/template-' . $wpdb->blogid . '-' . $hmr['name'] . '-noresult.php';
		$fp = fopen( $file, 'w' );
		fwrite( $fp, stripslashes( $_POST['template']['noresult'] ) );
		fclose( $fp );
	?>
		<h3><?php _e( 'Form Elements' ,'hmr' ); ?></h3>
		<p><?php _e( 'Moving form elements that you want to have in the form on the right in the left pane. You can edit the attributes of the elements by clicking on it in the "Selected Form elements" panel. In this dialog you can set the necessary attributes.', 'hmr' ); ?></p>
		<div style="display:none;">
			<select id="hmr-datasource">
				<optgroup label="<?php _e( 'Taxonomies', 'hmr' ); ?>">
					<?php foreach( $hmr['tax'] as $meta ): ?>
					<option value="tax[<?php echo $meta; ?>]"><?php echo $meta; ?></option>
					<?php endforeach; ?>
				</optgroup><optgroup label="<?php _e( 'Postmetas', 'hmr' ); ?>">
				<?php foreach( $hmr['meta'] as $meta ): ?>
					<option value="meta[<?php echo $meta; ?>]"><?php echo $meta; ?></option>
				<?php endforeach; ?></optgroup>
			</select>
			
			<div id="hmr-orderbysource">
					<?php 
					$i = 0;
					if( is_array( $hmr['meta'] ) ):
					foreach( $hmr['meta'] as $meta ): ?>
					<?php echo $meta; ?> <?php _e( 'ascending', 'hmr' ); ?>:<br /><input class="hmr-array" type="checkbox" checked="checked" name="orderby[<?php echo $i; ?>]" value="meta[<?php echo $meta; ?>|asc]"> <input class="hmr-orderbylabel hmr-array" name="orderbylabel[<?php echo $i; ?>]" value="<?php echo $meta; ?> <?php _e( 'ascending', 'hmr' ); ?>" /><br />
					<?php echo $meta; ?> <?php _e( 'descending', 'hmr' ); ?>:<br /><input class="hmr-array" type="checkbox" checked="checked" name="orderby[<?php echo $i; ?>]" value="meta[<?php echo $meta; ?>|desc]"> <input class="hmr-orderbylabel hmr-array" name="orderbylabel[<?php echo $i; ?>]" value="<?php echo $meta; ?> <?php _e( 'descending', 'hmr' ); ?>" /><br /><br />
					<?php $i++; endforeach; endif;?>	
					<?php _e( 'Date ascending', 'hmr' ); ?>:<br /><input class="hmr-array" type="checkbox" checked="checked" name="orderby[<?php echo $i; ?>]" value="post[date|asc]"> <input class="hmr-orderbylabel hmr-array" name="orderbylabel[<?php echo $i++; ?>]" value="<?php _e( 'Date ascending', 'hmr' );  ?>" /><br />
					<?php _e( 'Date descending', 'hmr' ); ?>:<br /><input class="hmr-array" type="checkbox" checked="checked" name="orderby[<?php echo $i; ?>]" value="post[date|desc]"> <input class="hmr-orderbylabel hmr-array" name="orderbylabel[<?php echo $i++; ?>]" value="<?php _e( 'Date descending', 'hmr' ); ?>" /><br />
			</div>
			
			<select id="hmr-allpostmeta">
				<?php foreach( $hmr['meta'] as $meta ): ?>
					<option value="<?php echo $meta; ?>"><?php echo $meta; ?></option>
				<?php endforeach; ?></optgroup>
			</select>
		</div>	

		<form method="post" class="hmr-form">
			<?php wp_nonce_field( 'save-new-hmr', 'hmr-wpnonce', false ); ?>
			<input name="hmr_step" value="4" type="hidden" />
					<?php if( isset( $hmr ) ): foreach( $hmr as $key => $val ):
			if( is_array( $val ) ):
			foreach( $val as $v ):
			?>
			<input type="hidden" name="hmr[<?php echo $key;?>][]" value="<?php echo $v; ?>" />			
			<?php
			endforeach;
			else:
			?>
			<input type="hidden" name="hmr[<?php echo $key;?>]" value="<?php echo $val; ?>" />
			<?php
			endif;
		endforeach; endif; ?>	
			<div class="field filter">
				<p><strong><?php _e( 'Chosen Form Elements', 'hmr' ); ?></strong></p>
		
			</div>
		
			<div class="field elements">
				<p><strong><?php _e( 'All Form Elements', 'hmr' ); ?></strong></p>
				<div data-attr='{"type":"fulltext"}'>
					<img src="<?php echo HMR_URL ?>res/admin/input-fulltext.png" alt="<?php __( 'Fulltext Search', 'hmr' ); ?>" />
					<span><?php _e( 'Fulltext Search', 'hmr' ); ?></span>
				</div>
				<div data-attr='{"type":"select"}'>
					<img src="<?php echo HMR_URL ?>res/admin/select.png" alt="<?php __( 'Selectbox', 'hmr' ); ?>" />
					<span><?php _e( 'Selectbox', 'hmr' ); ?></span>
				</div>
				<div data-attr='{"type":"checkbox"}'>
					<img src="<?php echo HMR_URL ?>res/admin/checkbox.png" alt="<?php __( 'Checkbox', 'hmr' ); ?>" />
					<span><?php _e( 'Checkbox', 'hmr' ); ?></span>
				</div>
				<div data-attr='{"type":"radiobox"}'>
					<img src="<?php echo HMR_URL ?>res/admin/radiobox.png" alt="<?php __( 'Radiobox', 'hmr' ); ?>" />
					<span><?php _e( 'Radiobox', 'hmr' ); ?></span>
				</div>
				
			</div>
			<div class="hmr-clear"></div>
			<hr />
		<input class="button" type="submit" value="<?php _e( 'Next Step', 'hmr' ); ?> &#10148;" />
		</form>
	<?php elseif( $_POST['hmr_step'] == 2 ): ?>
	<h3><?php _e( 'Layout' ,'hmr' ); ?></h3>
	<form method="post" class="hmr-form">
		<?php wp_nonce_field( 'save-new-hmr', 'hmr-wpnonce', false, true ); ?>	
		<input name="hmr_step" value="3" type="hidden" />
				<?php if( isset( $hmr ) ): foreach( $hmr as $key => $val ):
			if( is_array( $val ) ):
			foreach( $val as $v ):
			?>
			<input type="hidden" name="hmr[<?php echo $key;?>][]" value="<?php echo $v; ?>" />			
			<?php
			endforeach;
			else:
			?>
			<input type="hidden" name="hmr[<?php echo $key;?>]" value="<?php echo $val; ?>" />
			<?php
			endif;
		endforeach; endif; ?>
		
		
		<fieldset>
			<legend><?php _e( 'Search Result Columns' ,'hmr' ); ?></legend>
		<div class="hmr-4columns">
			<label>
				<img src="<?php echo HMR_URL; ?>/res/admin/layout-li-column1.png" alt="1 Column" />
				<br />
				<input name="hmr[columns]" value="1" type="radio"/>
				<?php _e( '1 Column', 'hmr' ); ?>
			</label>
		</div>
		<div class="hmr-4columns">
			<label>
				<img src="<?php echo HMR_URL; ?>/res/admin/layout-li-column2.png" alt="2 Columns" />
				<br />
				<input name="hmr[columns]" value="2" type="radio"/>
				<?php _e( '2 Columns', 'hmr' ); ?>
			</label>
		</div>
		<div class="hmr-4columns">
			<label>
				<img src="<?php echo HMR_URL; ?>/res/admin/layout-li-column3.png" alt="3 Columns" />
				<br />
				<input name="hmr[columns]" value="3" type="radio"/>
				<?php _e( '3 Columns', 'hmr' ); ?>
			</label>
		</div>
		<div class="hmr-4columns">
			<label>
				<img src="<?php echo HMR_URL; ?>/res/admin/layout-li-column4.png" alt="4 Columns" />
				<br />
				<input name="hmr[columns]" value="4" type="radio"/>
				<?php _e( '4 Columns', 'hmr' ); ?>
			</label>
		</div>
		</fieldset>		
		<fieldset>
			<legend><?php _e( 'Border & Background', 'hmr' ); ?></legend>
			<label for="hmrborder"><?php _e( 'Border Color' ); ?>: </label><input id="hmrborder" type="text" value="#cacaca" name="hmr[border]" class="hmr-colorfield" />
			<label for="hmrbackground"><?php _e( 'Background Color' ); ?>: </label><input id="hmrbackground" type="text" value="#f0f0f0" name="hmr[background]" class="hmr-colorfield" />
		</fieldset>
		<fieldset class="big">
		<legend><?php _e( 'Single Result Element', 'hmr' ); ?></legend>
		<?php 
			$resdir = HMR_DIR . 'templates/res/';
			$dir = HMR_DIR . 'templates/';
			$files = array();
			$dh = opendir( $dir );
			if (!is_writable( $dir ) ):
				?>
				<div class="error"><? _e( 'The directory ' . $dir . ' is not writeable.' ); ?></div>
				<?php
			endif;
			
			$files = scandir( $resdir );
			unset( $files[0] );
			unset( $files[1] );
		if( count( $files ) > 2 ): ?>
		<p><label for="template"><?php _e( 'Choose a template', 'hmr' ); ?>:</label></p>
		<select id="template">
			<?php foreach( $files as $file ): 
				if( preg_match( '^template\-(.*)\.php^', $file ) && !preg_match( '^\-noresult\.php^', $file ) ):
					preg_match_all( '^template\-(.*)\.php^', $file, $match )
					?>
					<option <?php if( $match[1][0] == 'standard' ) echo 'selected="selected"'; ?> value="<?php echo $file; ?>"><?php echo ucfirst( $match[1][0] ); ?></option>
			<?php 
			endif;
			endforeach; ?>
		</select>
		<?php endif; ?>
		<?php
			if( !is_file( HMR_DIR . 'templates/template-' . $_POST['hmr']['name'] . '.php' ) )
				$file = HMR_DIR . 'templates/res/template-standard.php';
			else
				$file = HMR_DIR . 'templates/template-' . $_POST['hmr']['name'] . '.php';
			if( !is_file( HMR_DIR . 'templates/template-' . $_POST['hmr']['name'] . '-noresult.php' ) )
				$file_no_result = HMR_DIR . 'templates/res/template-standard-noresult.php';
			else
				$file_no_result = HMR_DIR . 'templates/template-' . $_POST['hmr']['name'] . '-noresult.php';
				
		?>
		<div class="hmr-2columns">			
			<?php _e( 'Adjust template for Result Elemet', 'hmr' ); ?>
			<textarea class="hmr" name="template[result]"><?php echo file_get_contents( $file ); ?></textarea>
			<?php _e( 'Adjust template for No Result Elemet', 'hmr' ); ?>
			<textarea class="hmr" name="template[noresult]"><?php echo file_get_contents( $file_no_result ); ?></textarea>		
		</div>
		<div class="hmr-2columns">
			<strong><?php _e( 'Template Tags', 'hmr' ); ?></strong><br />
			<?php _e( 'You can enrich their taxonomy template Post metatsennostey and more. Here you see a list of tags templates that you can use:', 'hmr' ); ?>
			<table>
				<thead>
					<tr><th><?php _e( 'Name', 'hmr' ); ?></th><th><?php _e( 'Displays', 'hmr' ); ?></th></tr>
				</thead>
				<tbody>
					<tr><td><code>#the_title#</code></td><td><?php _e( 'Displays the title of the post', 'hmr' ); ?></td></tr>
					<tr><td><code>#the_content#</code></td><td><?php _e( 'Displays the content of the post', 'hmr' ); ?></td></tr>
					<tr><td><code>#the_excerpt#</code></td><td><?php _e( 'Displays the excerpt of the post', 'hmr' ); ?></td></tr>
					<tr><td><code>#the_author#</code></td><td><?php _e( 'Displays the authors name', 'hmr' ); ?></td></tr>
					<tr><td><code>#count_comments#</code></td><td><?php _e( 'Displays the number of comments on this post', 'hmr' ); ?></td></tr>
					<tr><td><code>#the_permalink#</code></td><td><?php _e( 'Displays the link to the post', 'hmr' ); ?></td></tr>
					<tr><td><code>#thumbnail#</code></td><td><?php _e( 'Displays the thumbnail of the post', 'hmr' ); ?></td></tr>
					<?php
					if( isset( $hmr['tax'] ) && is_array( $hmr['tax'] ) ):
						foreach( $hmr['tax'] as $tax ):
						?>
						<tr><td><code>#tax_<?php echo $tax; ?>#</code></td><td><?php printf( __( 'Displays the used terms of the taxonomy "%s"', 'hmr' ), $tax ); ?></td></tr>					
						<?php
						endforeach;
					?><?php
					endif;
					if( isset( $hmr['meta'] ) && is_array( $hmr['meta'] ) ):
						foreach( $hmr['meta'] as $tax ):
						?>
						<tr><td><code>#meta_<?php echo $tax; ?>#</code></td><td><?php printf( __( 'Displays the value of the Postmeta "%s"', 'hmr' ), $tax ); ?></td></tr>					
						<?php
						endforeach;
					endif;
					?>
				</tbody>
				<tfoot>
					<tr><th><?php _e( 'Name', 'hmr' ); ?></th><th><?php _e( 'Displays', 'hmr' ); ?></th></tr>
				</tfoot>
			</table>
		</div>
		</fieldset>
		<div class="hmr-clear"></div>
		<hr />
		<input class="button" type="submit" value="<?php _e( 'Next Step', 'hmr' ); ?> &#10148;" />
	</form>	
	<?php elseif( $_POST['hmr_step'] == 4 ): 
	if( ! wp_verify_nonce( $_POST['hmr-wpnonce'], 'save-new-hmr' ) )
			wp_die( 'Wrong parameter.' );
	?>
	<h3><?php _e( 'Saved' ,'hmr' ); ?></h3>
		<form class="hmr-form">
			<fieldset>
				<legend><?php _e( 'Yeeeeha!', 'hmr' ); ?></legend>
				<div class="update-nag">
					<p><?php _e( 'Your detail search is now updated. You can insert this form by using the following shortcode:', 'hmr' ); ?></p>
					<input onclick="this.select();" onfocus="this.select();" value='[search-form id="<?php echo $hmr['name']; ?>"]' />
				</div>
			</fieldset>
			<fieldset class="big">
				<legend><?php _e( 'What\'s next?', 'hmr' ); ?></legend>
				<p><?php printf( __( 'You have created your search from. This form needs to be inserted into a Page. We recommend to <a target="_blank" href="%s" target="_blank">create a new Page</a> and insert this shortcode [search-form id="%s"] into the Editor.', 'hmr' ), 'post-new.php?post_type=page', $hmr['name'] ); ?><br />
				<?php printf( __( 'If you are not sure, what Shortcodes are, have a look into the <a href="%s" target="_blank">WordPress Documentation</a>.', 'hmr' ), 'http://en.support.wordpress.com/shortcodes/' ); ?><br />
				<?php _e( 'Once, you have done this and published the new Page, your visitors can access this page and search your WordPress Database very detailed.', 'hmr' ); ?><br />		
				<?php printf( __( 'Thanks a lot for using <a href="%s" target="_blank">Profi Search Form</a>', 'hmr' ), 'http://profisearchform.com/' ); ?></p>
			</fieldset>
		</form>
		<?php  
		$fields = get_option( 'hmr-fields' );
		$fields[ $hmr['name'] ] = $hmr;
		update_option( 'hmr-fields', $fields ); ?>
	</form>
	<?php endif; ?>
	
</div>