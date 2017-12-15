<?php
/* Ask Question */
add_action( 'widgets_init', 'widget_ask_widget' );
function widget_ask_widget() {
	register_widget( 'Widget_Ask' );
}

class Widget_Ask extends WP_Widget {

	function Widget_Ask() {
		$widget_ops = array( 'classname' => 'ask-widget'  );
		$control_ops = array( 'id_base' => 'ask-widget' );
		parent::__construct( 'ask-widget','Ask me - Ask question button', $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {
		extract( $args );?>
		<div class="widget_ask">
			<a href="<?php echo esc_url(get_page_link(vpanel_options('add_question')))?>" class="color button small margin_0"><?php _e("Ask a Question","vbegy")?></a>
		</div>
	<?php }

	function update( $new_instance, $old_instance ) {
		$instance		   = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	function form( $instance ) {
		$defaults = array( 'title' => __('Ask question','vbegy'));
		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title : </label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo (isset($instance['title'])?esc_attr($instance['title']):""); ?>" class="widefat" type="text">
		</p>
	<?php
	}
}
?>