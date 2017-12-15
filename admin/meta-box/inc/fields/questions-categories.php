<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Questions_Categories_Field' ) )
{
	class RWMB_Questions_Categories_Field extends RWMB_Field
	{
		/**
		 * Show begin HTML markup for fields
		 *
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function begin_html( $meta, $field )
		{
			global $post;
			$option_name = $output = '';
			$output .= '
			<div class="rwmb-label">
				<label for="'.(isset($field['id']) && $field['id'] != ""?$field['id']:"").'">'.(isset($field['desc']) && $field['desc'] != ""?$field['desc']:"").'</label>
			</div>
			<div class="rwmb-input">
				<div class="styled-select">'.
					wp_dropdown_categories(array(
						'taxonomy'        => 'question-category',
					    'orderby'         => 'name',
					    'echo'            => 0,
					    'hide_empty'      => 0,
					    'hierarchical'    => 1,
					    'id'              => (isset($field['id']) && $field['id'] != ""?$field['id']:""),
					    'name'            => (isset($field['id']) && $field['id'] != ""?$field['id']:""),
					    'show_option_all' => __('Show All Categories','vbegy'),
					))
				.'</div>
				<div class="clear"></div>
				<div class="add-item add-item-2 add-item-6" data-id="'.(isset($field['id']) && $field['id'] != ""?$field['id']:"").'_categories" data-name="'.(isset($field['id']) && $field['id'] != ""?$field['id']:"").'">Add category</div>
				<div class="clear"></div>
				<div class="category_tabs">
					<ul class="sort-sections sort-sections-ul ui-sortable">';
						$i = 0;
						$meta = rwmb_meta((isset($field['id']) && $field['id'] != ""?$field['id']:""),'type=questions_categories',$post->ID);
						if(isset($meta) && is_array($meta)) {
							foreach ($meta as $key_a => $value_a) {
								$term_cat = get_term_by('id',$value_a,'question-category');
								$i++;
								$output .= '<li id="'.(isset($field['id']) && $field['id'] != ""?$field['id']:"").'_categories_'.$value_a.'" class="ui-state-default"><div class="widget-head ui-sortable-handle"><span>'.($value_a == 0?__('Show All Categories','vbegy'):$term_cat->name).'</span></div><input name="'.(isset($field['id']) && $field['id'] != ""?$field['id']:"").'['.$value_a.']" value="'.$value_a.'" type="hidden"><a class="del-builder-item"><span class="dashicons dashicons-trash"></span></a></li>';
							}
						}
					$output .= '</ul>
				</div>
				<div class="clear"></div>
				
				<script type="text/javascript" data-js="'.esc_js($i+1).'" class="'.$field['id'].'_j">'.$field['id'].'_j = '.esc_js($i+1).';</script>
			</div>';
			return $output;
		}

		/**
		 * Show end HTML markup for fields
		 *
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function end_html( $meta, $field )
		{
			return '';
		}
	}
}
