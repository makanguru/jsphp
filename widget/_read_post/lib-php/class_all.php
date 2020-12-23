<?php 

class MNVtest_Widget extends WP_Widget
{
    function __construct() {
    	parent::__construct(
    		'MNVtest_widget', // Base ID
    		'MNVtest Widget', // Name
    		array('description' => __( 'Displays your post for name or path'))
    	   );
    }


    function update($new_instance, $old_instance) {
    		$instance = $old_instance;
    		$instance['title'] = strip_tags($new_instance['title']);
    		$instance['nameOfPost'] = strip_tags($new_instance['nameOfPost']);
    		$instance['pathOfPost'] = strip_tags($new_instance['pathOfPost']);
    		return $instance;
    }


    function form($instance) {
    	if( $instance) {
    		$title = esc_attr($instance['title']);
    		$nameOfPost = esc_attr($instance['nameOfPost']);
    		$pathOfPost = esc_attr($instance['pathOfPost']);
    	} else {
    		$title = '';
    		$nameOfPost = '';
    		$pathOfPost = '';
    	}
    	?>
    		<p>
    		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'MNVtest_widget'); ?></label>
    		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
    		</p>
    		<p>


    		<p>   <!-- Name of post -->
    		<label for="<?php echo $this->get_field_id('nameOfPost'); ?>"><?php _e('Name of Post', 'MNVtest_widget'); ?></label>
    		<input class="widefat" id="<?php echo $this->get_field_id('nameOfPost'); ?>" name="<?php echo $this->get_field_name('nameOfPost'); ?>" type="text" value="<?php echo $nameOfPost; ?>" />
    		</p>
    		<p>


    		<p>   <!-- Path of post -->
    		<label for="<?php echo $this->get_field_id('pathOfPost'); ?>"><?php _e('Path of Post (URL)', 'MNVtest_widget'); ?></label>
    		<input class="widefat" id="<?php echo $this->get_field_id('pathOfPost'); ?>" name="<?php echo $this->get_field_name('pathOfPost'); ?>" type="text" value="<?php echo $pathOfPost; ?>" />
    		</p>
    		

    		
    	<?php
    }


    function widget($args, $instance) {
    	extract( $args );
    	$title = apply_filters('widget_title', $instance['title']);
    	$nameOfPost = $instance['nameOfPost'];
    	$pathOfPost = $instance['pathOfPost'];
    	echo $before_widget;
    	if ( $title ) {
    		echo $before_title . $title . $after_title;
    	}
    	$this->getMNVtestpitem($nameOfPost, $pathOfPost);
    	echo $after_widget;
    }

  
function getMNVtestpitem($nameOfPost, $pathOfPost) { //html

	global $post;
	add_image_size( 'MNVtest_widget_size', 85, 45, false );

  	$mustShow = true;
  	$queryText = "";

	if ($nameOfPost || $pathOfPost) {

		if ($nameOfPost) {
			
			$queryText = 'name=' . $nameOfPost;  // Поиск по имени
			$pitem = new WP_Query( $queryText );

			if ($pitem->have_posts()) {
				$this->showPitem($pitem);
				wp_reset_postdata();
				$mustShow = false;
			}

		}

		if ( $mustShow  &&  $pathOfPost )  {  // Поиск по урлу

			if ($pathOfPost) {

				$url = $pathOfPost;
				$post_ID = url_to_postid($url);

				if ($post_ID) {
					$queryText = 'p=' . $post_ID;
					$pitem = new WP_Query( $queryText );   // Выдача записи-поста

					if ($pitem->have_posts()) {
						$this->showPitem($pitem);
						wp_reset_postdata();
						$mustShow = false;
					} else {
						$queryText = 'page_id=' . $post_ID;
						$pitem = new WP_Query( $queryText );   // Выдача записи-поста

						if ($pitem->have_posts()) {
							$this->showPitem($pitem);
							wp_reset_postdata();
							$mustShow = false;
						}
					}

				} 

			}

		}
		
		if ($mustShow ) {
			echo '<p style="padding:25px;">No post found</p>';
		}
			
		
	} else {
			echo '<p style="padding:25px;">No post found. Enter data for searching</p>';
	}

}




	function showPitem($pitem) {
		echo '<ul class="MNVtest_widget">';
			while ($pitem->have_posts()) {
				$pitem->the_post();
				$image = (has_post_thumbnail($post->ID)) ? get_the_post_thumbnail($post->ID, 'MNVtest_widget_size') : '<div class="noThumb"></div>';
				$listItem = '<li>' . $image;
				$listItem .= '<a href="' . get_permalink() . '">';
				$listItem .= get_the_title() . '</a>';
				$listItem .= '<span>Added ' . get_the_date() . '</span></li>';
				echo $listItem;
			}
		echo '</ul>';

	}




} //end class MNVtest_Widget






