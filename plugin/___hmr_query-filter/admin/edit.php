<?php
	global $wpdb;
	$update = false;
	
	
	$fields = get_option( 'hmr-fields' );
	
	/**
		Import
	*/
	if( isset( $_POST['import'] ) ):
		if( ! wp_verify_nonce( $_POST['hmr-wpnonce'], 'update-hmr' ) )
			wp_die( 'The parameter is incorrect.' );
		$_POST['import'] = stripslashes( $_POST['import'] );
		$field = unserialize( $_POST['import'] );
		echo $field['name'];
		echo '<pre>';print_r( $field );echo '</pre>';
		$fields[ $field['name'] ] = $field;
		update_option( 'hmr-fields', $fields ); 
	endif;
	
	
	foreach( $fields as $field )
		if( $field['name'] == $_GET["ID"] )
			break;
	
	if( isset( $_POST['hmr'] ) ):
		if( ! wp_verify_nonce( $_POST['hmr-wpnonce'], 'update-hmr' ) )
			wp_die( 'The parameter is incorrect.' );
		$hmr = $_POST['hmr'];
		
		if( isset( $_POST['hmr_step'] ) && $_POST['hmr_step'] == 1 ):
			foreach( $hmr as $key => $val ):
				$field[ $key ] = $val;
			endforeach;
		endif;
		if( isset( $_POST['hmr_step'] ) && $_POST['hmr_step'] == 2 ):
			$field['tax'] = $hmr['tax'];
			$field['meta'] = $hmr['meta'];
		endif;
		if( isset( $_POST['hmr_step'] ) && $_POST['hmr_step'] == 3 ):
			
			foreach( $hmr as $key => $val ):
				$field[ $key ] = $val;
			endforeach;
			
			if( !function_exists('is_multisite') || !is_multisite() )
				$file = HMR_DIR . 'templates/template-' . $field['name'] . '.php';
			else
				$file = HMR_DIR . 'templates/template-' . $wpdb->blogid . '-' . $field['name'] . '.php';
			$fp = fopen( $file, 'w' );
			fwrite( $fp, stripslashes( $_POST['template']['result'] ) );
			fclose( $fp );
		
			if( !function_exists('is_multisite') || !is_multisite() )
				$file = HMR_DIR . 'templates/template-' . $field['name'] . '-noresult.php';
			else
				$file = HMR_DIR . 'templates/template-' . $wpdb->blogid . '-' . $field['name'] . '-noresult.php';
			$fp = fopen( $file, 'w' );
			fwrite( $fp, stripslashes( $_POST['template']['noresult'] ) );
			fclose( $fp );			
		endif;
		
		
		if( isset( $_POST['hmr_step'] ) && $_POST['hmr_step'] == 4 ):
			
			foreach( $hmr as $key => $val ):
				$field[ $key ] = $val;
			endforeach;
		endif;
		
		$fields[ $field['name'] ] = $field;
		update_option( 'hmr-fields', $fields ); 
		$update = true;
	endif;
?>
<script>var hmr_tab_index = <?php if( isset( $_POST['hmr_step'] ) ): echo ( $_POST['hmr_step'] - 1 ); else: echo '0'; endif; ?>;</script>
<div id="wrap" class="hmr-wrap">
	<p><a href="?page=search-filter"><?php _e( 'Search Filter', 'hmr' ); ?></a> &raquo;</p>
	<?php if( isset( $field['title'] ) && trim( $field['title'] ) ) $title = $field['title']; else $title = $field['name']; ?>
	<h2><?php printf( __( 'Edit "%s"', 'hmr' ),  $title ); ?></h2>
	<?php if( $update ): ?>
	<div class="updated below-h2"><p><?php _e( 'Filter updated.', 'hmr' ); ?></p></div>
	<?php endif; ?>
	<div class="hmr-tabs">
		<ul>
			<li><a href="#general-settings"><?php _e( 'General Settings', 'hmr' ); ?></a></li>
			<li><a href="#taxonomies-postmeta"><?php _e( 'Taxonomies & Postmetas' ,'hmr' ); ?></a></li>
			<li><a href="#layout"><?php _e( 'Layout' ,'hmr' ); ?></a></li>
			<li><a href="#form-elements"><?php _e( 'Form Elements' ,'hmr' ); ?></a></li>
			<li><a href="#import-export"><?php _e( 'Import & Export', 'hmr' ); ?></a></li>
		</ul>
		
		<!-- 1. Tab: General Settings -->
		<div id="general-settings">
			<form method="post" class="hmr-form"><?php wp_nonce_field( 'update-hmr', 'hmr-wpnonce', false ); ?>
				<input type="hidden" value="1" name="hmr_step" />				

				<fieldset>
					<legend><?php _e( 'General Settings' ,'hmr' ); ?></legend>
					<section>
						<label for="hmr_id"><?php _e( 'ID', 'hmr' ); ?>:</label>
						<input id="hmr_id" readonly name="hmr[name]" value="<?php echo $field['name']; ?>" />		
					</section>
					<section>
						<label for="hmr_name"><?php _e( 'Name', 'hmr' ); ?>:</label>
						<input id="hmr_name" name="hmr[title]" value="<?php if( isset( $field['title'] ) && trim( $field['title'] ) != '' ) echo $field['title']; else echo $field['name']; ?>" />		
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
								<option <?php if( ( is_array( $field['posttype'] ) && in_array( $key, $field['posttype'] ) ) || ( !is_array( $field['posttype'] ) && $key == $field['posttype'] ) ) echo 'selected="selected"'; ?> value="<?php echo $key; ?>"><?php echo $p->labels->name; ?></option>				
							<?php endforeach; ?>
						</select>
					</section>
					<section>
						<label for="hmr_debug"><?php _e( 'Debug Mode', 'hmr' ); ?>:</label>
						<select id="hmr_debug" name="hmr[debug]">
							<option <?php if( !isset( $field['debug'] ) || 0 == $field['debug'] ) echo 'selected="selected"'; ?> value="0"><?php _e( 'Off', 'hmr' ); ?></option>
							<option <?php if( isset( $field['debug'] ) && 1 == $field['debug'] ) echo 'selected="selected"'; ?> value="1"><?php _e( 'On', 'hmr' ); ?></option>
						</select>
						
						<small><?php _e( 'Turn this mode on, in order to get additional data on the WP_Query like the args or the SQL statement. Please turn it off in live mode', 'hmr' ); ?></small>
					</section>
					<hr />
					<input class="button" type="submit" value="<?php _e( 'Update', 'hmr' ); ?>" />
				</fieldset>
			</form>
		</div>
		
		<!-- 2. Tab: Taxonomies and Postmeta -->
		<div id="taxonomies-postmeta">
			<h3><?php _e( 'Taxonomies & Postmetas' ,'hmr' ); ?></h3>
			<p><?php _e( 'Please drag taxonomies and Post METAS, you want to use in your form from the left into the right-field search.', 'hmr' ); ?></p>
			<?php
				$metas = get_all_postmetas_from_post_type( $field['posttype'] );
			?>
			<ul class="hmr-group1">
				<li><?php _e( 'Taxonomies', 'hmr' ); ?>
					<?php $tax = get_all_post_taxonomies( $field['posttype'] ); ?>
					<ul class="hmr-tax-ul">
						<?php foreach( $tax as $key => $t ): 
						if( !isset( $field['tax'] ) || ( is_array( $field['tax'] ) && !in_array( $key, $field['tax'] ) ) ): 
						?>
						<li class="hmr-drag"><input name="hmr[tax][]" value="<?php echo $key; ?>" type="hidden" /><?php echo $t->labels->name; ?> (<?php echo $key; ?>)</li>
						<?php 
						endif;
						endforeach; ?>
					</ul>
				</li>
			
				<li><?php _e( 'Postmeta', 'hmr' ); ?>
					<ul class="hmr-meta-ul">
					<?php foreach( $metas as $key => $val ): 
					if( !isset( $field['meta'] ) || ( is_array( $field['meta'] ) && !in_array( $key, $field['meta'] ) ) ): ?>
					<li class="hmr-drag"><input name="hmr[meta][]" value="<?php echo $key; ?>" type="hidden" /><?php echo ucfirst( $key ); ?></li>
					<?php 
					endif;
					endforeach; ?>
					</ul>
				</li>
			</ul>
			
			<form method="post" class="hmr-form"><?php wp_nonce_field( 'update-hmr', 'hmr-wpnonce', false ); ?>
				<input type="hidden" value="2" name="hmr_step" />

				<ul class="hmr-group2">
					<?php foreach( $tax as $key => $t ): 
					if( isset( $field['tax'] ) && is_array( $field['tax'] ) && in_array( $key, $field['tax'] ) ): 
					?>
					<li class="hmr-drag"><input name="hmr[tax][]" value="<?php echo $key; ?>" type="hidden" /><?php echo $t->labels->name; ?></li>
					<?php 
					endif;
					endforeach; ?>
					<?php foreach( $metas as $key => $val ): 
					if( isset( $field['meta'] ) && is_array( $field['meta'] ) && in_array( $key, $field['meta'] ) ): ?>
					<li class="hmr-drag"><input name="hmr[meta][]" value="<?php echo $key; ?>" type="hidden" /><?php echo ucfirst( $key ); ?></li>
					<?php 
					endif;
					endforeach; ?>
				</ul>
				<div class="hmr-clear"></div>
				<hr />
				
				<hr />
				<input class="button" type="submit" value="<?php _e( 'Update', 'hmr' ); ?>" />
			</form>
		</div>
		
		<!-- 3. Tab: Layout -->
		<div id="layout">
			<h3><?php _e( 'Layout' ,'hmr' ); ?></h3>
			<form method="post" class="hmr-form"><?php wp_nonce_field( 'update-hmr', 'hmr-wpnonce', false ); ?>
				<input name="hmr_step" value="3" type="hidden" />
				
			<div class="hmr-accordion">
				<h3><?php _e( 'Search Result Columns' ,'hmr' ); ?></h3>
				<div>
					<div class="hmr-4columns">
						<label>
							<img src="<?php echo HMR_URL; ?>/res/admin/layout-li-column1.png" alt="1 Column" />
							<br />
							<input <?php if( !isset( $field['columns'] ) || $field['columns'] == 1 ) echo 'checked="checked" '; ?>name="hmr[columns]" value="1" type="radio"/>
							<?php _e( '1 Column', 'hmr' ); ?>
						</label>
					</div>
					<div class="hmr-4columns">
						<label>
							<img src="<?php echo HMR_URL; ?>/res/admin/layout-li-column2.png" alt="2 Columns" />
							<br />
							<input <?php if( isset( $field['columns'] ) && $field['columns'] == 2 ) echo 'checked="checked" '; ?>name="hmr[columns]" value="2" type="radio"/>
							<?php _e( '2 Columns', 'hmr' ); ?>
						</label>
					</div>
					<div class="hmr-4columns">
						<label>
							<img src="<?php echo HMR_URL; ?>/res/admin/layout-li-column3.png" alt="3 Columns" />
							<br />
							<input <?php if( isset( $field['columns'] ) && $field['columns'] == 3 ) echo 'checked="checked" '; ?>name="hmr[columns]" value="3" type="radio"/>
							<?php _e( '3 Columns', 'hmr' ); ?>
						</label>
					</div>
					<div class="hmr-4columns">
						<label>
							<img src="<?php echo HMR_URL; ?>/res/admin/layout-li-column4.png" alt="4 Columns" />
							<br />
							<input <?php if( isset( $field['columns'] ) && $field['columns'] == 4 ) echo 'checked="checked" '; ?>name="hmr[columns]" value="4" type="radio"/>
							<?php _e( '4 Columns', 'hmr' ); ?>
						</label>
					</div>
					<div class="hmr-clear"></div>
				</div>
				<h3><?php _e( 'Border & Background', 'hmr' ); ?></h3>
				<div>
					<label for="hmrborder"><?php _e( 'Border Color' ); ?>: </label><input id="hmrborder" type="text" value="<?php echo $field['border']; ?>" name="hmr[border]" class="hmr-colorfield" />
					<label for="hmrbackground"><?php _e( 'Background Color' ); ?>: </label><input id="hmrbackground" type="text" value="<?php echo $field['background']; ?>" name="hmr[background]" class="hmr-colorfield" />
				</div>
				
				<h3><?php _e( 'Single Result Element', 'hmr' ); ?></h3>
				<div>
					<?php 
					$resdir = HMR_DIR . 'templates/res/';
					$dir = HMR_DIR . 'templates/';
					$files = array();
					
					if (!is_writable( $dir ) ):
						?>
						<div class="error"><? _e( 'The directory ' . $dir . ' is not writeable.' ); ?></div>
						<?php
					endif;
				
					$template_name = $field['name'];
					if( function_exists('is_multisite') && is_multisite() )
						$template_name = $wpdb->blogid . '-' . $field['name'];
					if( !is_file( HMR_DIR . 'templates/template-' . $template_name . '.php' ) )
						$file = HMR_DIR . 'templates/res/template-standard.php';
					else
						$file = HMR_DIR . 'templates/template-' . $template_name . '.php';
					if( !is_file( HMR_DIR . 'templates/template-' . $template_name . '-noresult.php' ) )
						$file_no_result = HMR_DIR . 'templates/res/template-standard-noresult.php';
					else
						$file_no_result = HMR_DIR . 'templates/template-' . $template_name . '-noresult.php';
					?>
					<div class="hmr-2columns">
						<?php _e( 'Adjust template for Result Elemet', 'hmr' ); ?>
						<textarea class="hmr" name="template[result]"><?php echo file_get_contents( $file ); ?></textarea>
						<?php _e( 'Adjust template for No Result Elemet', 'hmr' ); ?>
						<textarea class="hmr" name="template[noresult]"><?php echo file_get_contents( $file_no_result ); ?></textarea>		
					</div>
					<div class="hmr-2columns">
						<strong><?php _e( 'Template Tags', 'hmr' ); ?></strong><br />
						<?php _e( 'You can enrich your template with Taxonomies, Postmeta-Values and much more. Here, you see the list of Template Tags you can use:', 'hmr' ); ?>
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
								if( isset( $field['tax'] ) && is_array( $field['tax'] ) ):
									foreach( $field['tax'] as $tax ):
								?>
								<tr><td><code>#tax_<?php echo $tax; ?>#</code></td><td><?php printf( __( 'Displays the used terms of the taxonomy "%s"', 'hmr' ), $tax ); ?></td></tr>					
								<?php
									endforeach;
								?><?php
								endif;
						
								if( isset( $field['meta'] ) && is_array( $field['meta'] ) ):
									foreach( $field['meta'] as $tax ):
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
					<div class="hmr-clear"></div>
				</div>
				<h3 class="last"><?php _e( 'Custom CSS', 'hmr' ); ?></h3>
				<div class="last">
					<?php _e( 'You can enter here your custom CSS for the search form.', 'hmr' ); ?>
					<textarea id="newcontent" class="hmr" name="hmr[custom_css]"><?php if( isset( $field['custom_css'] ) ) echo  stripslashes( $field['custom_css'] ); ?></textarea>
				</div>
				
			</div>
			<div class="hmr-clear"></div>
			<hr />
			<input class="button" type="submit" value="<?php _e( 'Update', 'hmr' ); ?>" />
		</form>	
	</div>
	
	
	<!-- 4. Form Elements -->
	<div id="form-elements">
		<h3><?php _e( 'Form Elements' ,'hmr' ); ?></h3>
		<p><?php _e( 'Moving form elements that you want to have in the form on the right in the left pane. You can edit the attributes of the elements by clicking on it in the "Selected Form elements" panel. In this dialog you can set the necessary attributes.', 'hmr' ); ?></p>
		<div style="display:none;">
			<select id="hmr-datasource">
				<optgroup label="<?php _e( 'Taxonomies', 'hmr' ); ?>">
					<?php foreach( $field['tax'] as $meta ): ?>
					<option value="tax[<?php echo $meta; ?>]"><?php echo $meta; ?></option>
					<?php endforeach; ?>
				</optgroup><optgroup label="<?php _e( 'Postmetas', 'hmr' ); ?>">
				<?php foreach( $field['meta'] as $meta ): ?>
					<option value="meta[<?php echo $meta; ?>]"><?php echo $meta; ?></option>
				<?php endforeach; ?></optgroup>
				
			</select>
			
			<div id="hmr-orderbysource">
					<?php 
					$i = 0;
					if( is_array( $field['meta'] ) ):
					foreach( $field['meta'] as $meta ): ?>
					<?php echo $meta; ?> <?php _e( 'ascending', 'hmr' ); ?>:<br /><input class="hmr-array" type="checkbox" checked="checked" name="orderby[<?php echo $i; ?>]" value="meta[<?php echo $meta; ?>|asc]"> <input class="hmr-orderbylabel hmr-array" name="orderbylabel[<?php echo $i; ?>]" value="<?php echo $meta; ?> <?php _e( 'ascending', 'hmr' ); ?>" /><br />
					<?php echo $meta; ?> <?php _e( 'descending', 'hmr' ); ?>:<br /><input class="hmr-array" type="checkbox" checked="checked" name="orderby[<?php echo $i; ?>]" value="meta[<?php echo $meta; ?>|desc]"> <input class="hmr-orderbylabel hmr-array" name="orderbylabel[<?php echo $i; ?>]" value="<?php echo $meta; ?> <?php _e( 'descending', 'hmr' ); ?>" /><br /><br />
					<?php $i++; endforeach; endif;?>
					<?php _e( 'Date ascending', 'hmr' ); ?>:<br /><input class="hmr-array" type="checkbox" checked="checked" name="orderby[<?php echo $i; ?>]" value="post[date|asc]"> <input class="hmr-orderbylabel hmr-array" name="orderbylabel[<?php echo $i++; ?>]" value="<?php _e( 'Date ascending', 'hmr' );  ?>" /><br />
					<?php _e( 'Date descending', 'hmr' ); ?>:<br /><input class="hmr-array" type="checkbox" checked="checked" name="orderby[<?php echo $i; ?>]" value="post[date|desc]"> <input class="hmr-orderbylabel hmr-array" name="orderbylabel[<?php echo $i++; ?>]" value="<?php _e( 'Date descending', 'hmr' ); ?>" /><br />
			</div>
			
			<select id="hmr-allpostmeta">
				<?php foreach( $field['meta'] as $meta ): ?>
					<option value="<?php echo $meta; ?>"><?php echo $meta; ?></option>
				<?php endforeach; ?></optgroup>
			</select>
		</div>	

		<form method="post" class="hmr-form"><?php wp_nonce_field( 'update-hmr', 'hmr-wpnonce', false ); ?>
			<input name="hmr_step" value="4" type="hidden" />
			<div class="field filter">
				<p><strong><?php _e( 'Chosen Form Elements', 'hmr' ); ?></strong></p>
				<?php 
				$img_array = array(
								'fulltext'	=>	'input-fulltext.png',
								'select'	=>	'select.png',
								'input'		=>	'input.png',
								'checkbox'	=>	'checkbox.png',
								'radiobox'	=>	'radiobox.png',
								'range'		=>	'range.png',
								'map'		=>	'maps.png',
								'orderby'	=>	'order-by.png',
								'hidden'	=>	'hidden.png',
								'btnsearch'	=>	'btn-search.png',
								'btnreset'	=>	'btn-reset.png',
								'date'		=>	'date.png'
				);
				$i = 0;
				foreach( $field['fields'] as $key => $f ): 
				$i++;
				?>
				<div data-attr='<?php echo json_encode( $f ); ?>' style="" data-id="<?php echo $i; ?>">
<img alt="" src="<?php echo HMR_URL ?>res/admin/<?php echo $img_array[ $f['type'] ]; ?>">
<span><?php echo $f['fieldname']; ?></span>
	<?php foreach( $f as $k => $v ): ?>
		<?php if( is_array( $v ) ): ?>
			<?php foreach( $v as $single_v ): ?>
			<input type="hidden" value="<?php echo $single_v; ?>" name="hmr[fields][<?php echo $i; ?>][<?php echo $k; ?>][]">
			<?php endforeach; ?>
		<?php else: ?>
			<input type="hidden" value="<?php echo $v; ?>" name="hmr[fields][<?php echo $i; ?>][<?php echo $k; ?>]">
		<?php endif; ?>
	<?php endforeach; ?>
</div>
				<?php endforeach; ?>
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
		<input class="button" type="submit" value="<?php _e( 'Update', 'hmr' ); ?>" />
		</form>
	</div>
	
	<!-- 5. Tab: Import & Export -->
	<div id="import-export">
		<h3><?php _e( 'Import & Export', 'hmr' ); ?></h3>
		<p><?php _e( 'You can import and export the search field. Copy the text below and save it to export the search results box. Insert here the settings to import the exported search field.', 'hmr' ); ?></p>
		<form method="post"><?php wp_nonce_field( 'update-hmr', 'hmr-wpnonce', false ); ?>
			
			<input name="hmr_step" value="5" type="hidden" />
			<textarea style="width:100%;height:250px" name="import"><?php echo serialize( $field ); ?></textarea>
			<input class="button" type="submit" value="<?php _e( 'Import', 'hmr' ); ?>" />
		</form>
	</div>
</div>
	
</div>