<?php 
/*
Plugin Name: Post List Widget
Plugin URI: http://www.dennis99.com
Version: 1.0
Author: Miller Ren
Author URI: http://blog.d99.me
Description: 可以实现:最新文章,随机文章,热评文章,作者文章排行榜等等.自定义显示类别,标题,摘要,缩略图
*/

class WP_Widget_Posts_List extends WP_Widget {
    
	function WP_Widget_Posts_List () {
		$widget_ops = array('classname' => 'widget_posts', 'description' => __( "Posts").__( "List" ) );
		$this->WP_Widget('posts_list', __( "Posts" ).__( "List" ), $widget_ops);
		$this->alt_option_name = 'widget_posts_list';
        
	}

	function widget($args, $instance) {
		extract($args);

		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
		if ( !$number = (int) $instance['number'] )$number = 10;
		else if ( $number < 1 ) $number = 1;
		else if ( $number > 50 )$number = 50;
    
        if ( !$excerpt_number = (int) $instance['excerpt_number'] )	$excerpt_number = 300;
		else if ( $excerpt_number < 1 )	$excerpt_number = 300;
		else if ( $excerpt_number > 800 ) $excerpt_number = 800;
			
		if ( !$thumb_size = (int) $instance['thumb_size'] )	$thumb_size = 50;
		else if ( $thumb_size < 1 )	$thumb_size = 50;
		else if ( $thumb_size > 500 ) $thumb_size = 500;
        
        $category = isset( $instance['category'] )  ? intval($instance['category'])  : 0 ;
        $show_thumb =  $instance['show_thumb']   ? 1  : 0 ;
        $show_excerpt =  $instance['show_excerpt']   ? 1 : 0 ;
        $show_title =  $instance['show_title']   ? 1 : 0 ;
        $sticky =  $instance['sticky']   ? 1 : 0 ;
        $orderby = isset($instance['orderby']) ? $instance['orderby'] : 'date';
        global $widget_id;
        $widget_id = $instance['widget_id'];

		$r = new WP_Query(
        array(
         'showposts' => $number, 
         'nopaging' => 0,
         'post_status' => 'publish', 
         'caller_get_posts' => 1,
         'cat'=>$category,
         'orderby' => $orderby
         ));
         
		if ($r->have_posts()  ) :
?>
        <div id="<?php echo $widget_id;?>" class="widget widget_posts">
        <?php if ( $title ) echo $before_title . $title . $after_title;?>
        <ul>
        <?php while ($r->have_posts()) : $r->the_post(); ?>            
        <li><?php if( $show_title ) echo '<a href="'.get_permalink().'">'.get_the_title().'</a>' ?>
        <?php if( $show_thumb ){ if ( function_exists( 'the_post_thumbnail' ) )the_post_thumbnail( array( $thumb_size,$thumb_size ) );} ?>
        <?php if ( $show_excerpt ){ echo '<p class="excerpt">'.mb_substr( get_the_excerpt(),0,$excerpt_number,'utf-8' ).'<a href="'.get_permalink().'">'.__('[more]').'</a></p>';} ?>
        <?php if( is_home() && is_sticky() && $sticky )the_content() ?>
        
        </li>
        <?php endwhile; ?>
        </ul>
		</div>
  
<?php
        wp_reset_query();
        endif;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number'] = (int) $new_instance['number'];
        $instance['excerpt_number'] = (int) $new_instance['excerpt_number'];
        $instance['category'] = (int) $new_instance['category'];
        $instance['show_thumb'] = isset( $new_instance['show_thumb'] )  ? 1 : 0 ;
        $instance['thumb_size'] = (int) $new_instance['thumb_size'];
        $instance['show_excerpt'] = isset( $new_instance['show_excerpt'] )  ? 1 : 0 ;
        $instance['show_title'] = isset( $new_instance['show_title'] )  ? 1 : 0 ;
        $instance['sticky'] = isset( $new_instance['sticky'] )  ? 1 : 0 ;
        $instance['orderby'] = isset($new_instance['orderby']) ? ( string )$new_instance['orderby'] : 'date';
        $instance['widget_id'] = isset( $new_instance['widget_id'] )  ? $new_instance['widget_id'] : '' ;
        return $instance;
	}

	
	function form( $instance ) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : __( 'Posts' );
		if ( !isset($instance['number']) || !$number = (int) $instance['number'] )$number = 15;
        if ( !isset($instance['excerpt_number']) || !$excerpt_number = (int) $instance['excerpt_number'] )$excerpt_number = 300;
        if ( !isset($instance['thumb_size']) || !$thumb_size = (int) $instance['thumb_size'] )$thumb_size = 50;
        
        $cat_ids=get_terms("category","hide_empty=0");
        $allowed_keys = array('author'=>'Author', 'date'=>'Date', 'title'=>'Title', 'modified'=>'Last Modified', 'menu_order'=>'Menu order', 'parent'=>'Parent', 'ID'=>'ID', 'rand'=>'Random', 'comment_count'=>'Comment');
        $show_thumb = isset( $instance['show_thumb'] )  ? (bool) $instance['show_thumb'] : false ;
        $show_excerpt = isset( $instance['show_excerpt'] )  ? (bool) $instance['show_excerpt'] : false ;
        $show_title = isset( $instance['show_title'] )  ? (bool) $instance['show_title'] : true ;
        $sticky = isset( $instance['sticky'] )  ? (bool) $instance['sticky'] : false ;
        if(!isset($instance['orderby']))$instance['orderby']='date';
        $widget_id = isset( $instance['widget_id'] ) ? $instance['widget_id'] : '' ;
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
    
        <p><label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category:'); ?></label>
        <select  class="widefat" id="cat" name="<?php echo $this->get_field_name('category');?>" onchange="jQuery('#<?php echo $this->get_field_id('title'); ?>').val(jQuery(this).children(':selected').text())" >   
        <option value="" selected="selected"><?php _e('All')?></option>
        <?php foreach($cat_ids as $id){echo '<option value="'.$id->term_id.'"'.( $id->term_id == $instance['category'] ? ' selected="selected"' : '' ).'>'.$id->name.'</option>';}?> 
        </select></p> 
        
        <p><label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e('Order:'); ?></label>
        <select  class="widefat" id="order" name="<?php echo $this->get_field_name('orderby');?>">   
        <?php foreach($allowed_keys as $k=>$v){echo '<option value="'.$k.'"'.( $k == $instance['orderby'] ? ' selected="selected"' : '' ).'>'.__($v).'</option>';}?> 
        </select></p>
           
        <p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_title'); ?>" name="<?php echo $this->get_field_name('show_title'); ?>"<?php checked( $show_title ); ?> />
		<label for="<?php echo $this->get_field_id('show_title'); ?>"><?php _e( 'Title' ) ?></label>
        <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_excerpt'); ?>" name="<?php echo $this->get_field_name('show_excerpt'); ?>"<?php checked( $show_excerpt ); ?> />
		<label for="<?php echo $this->get_field_id('show_excerpt'); ?>"><?php  _e( 'Excerpt' ) ?></label>
        <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('show_thumb'); ?>" name="<?php echo $this->get_field_name('show_thumb'); ?>"<?php checked( $show_thumb ); ?> />
		<label for="<?php echo $this->get_field_id('show_thumb'); ?>"><?php  _e( 'Thumbnail' ) ?></label>
        <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('sticky'); ?>" name="<?php echo $this->get_field_name('sticky'); ?>"<?php checked( $sticky ); ?> />
		<label for="<?php echo $this->get_field_id('sticky'); ?>"><?php  _e( 'Sticky' ) ?></label></p>

        <p><input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" />
        <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:'); ?></label><br />
        <input id="<?php echo $this->get_field_id('excerpt_number'); ?>" name="<?php echo $this->get_field_name('excerpt_number'); ?>" type="text" value="<?php echo $excerpt_number; ?>" size="3" />
        <label for="<?php echo $this->get_field_id('excerpt_number'); ?>"><?php _e('Excerpt');printf(__('Word count: %d'),$excerpt_number); ?></label><br />
        <input id="<?php echo $this->get_field_id('thumb_size'); ?>" name="<?php echo $this->get_field_name('thumb_size'); ?>" type="text" value="<?php echo $thumb_size; ?>" size="3" />
        <label for="<?php echo $this->get_field_id('thumb_size'); ?>"><?php _e('Thumbnail');_e('Size'); ?></label><br />
        <input id="<?php echo $this->get_field_id('widget_id'); ?>" name="<?php echo $this->get_field_name('widget_id'); ?>" type="text" value="<?php echo $widget_id; ?>" size="3" />
        <label for="<?php echo $this->get_field_id('widget_id'); ?>"><?php _e('ID'); ?></label><br /></p>
        <center><a href="http://dennis99.taobao.com" id="donate"><?php _e('Help')?></a></center>
          
<?php
	}
}

if(function_exists('add_theme_support'))add_theme_support('post-thumbnails');

add_action('widgets_init', create_function('','return register_widget("WP_Widget_Posts_List");' ));

?>