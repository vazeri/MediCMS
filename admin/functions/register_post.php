<?php
/* Question */
if ((bool)get_option("FlushRewriteRules")) {
	flush_rewrite_rules(true);
	delete_option("FlushRewriteRules");
}
function question_post_types_init() {
	$questions_slug          = vpanel_options('questions_slug');
	$category_questions_slug = vpanel_options('category_questions_slug');
	$tag_questions_slug      = vpanel_options('tag_questions_slug');
	$questions_slug          = (isset($questions_slug) && $questions_slug != ""?$questions_slug:"questions");
	$category_questions_slug = (isset($category_questions_slug) && $category_questions_slug != ""?$category_questions_slug:"question-category");
	$tag_questions_slug      = (isset($tag_questions_slug) && $tag_questions_slug != ""?$tag_questions_slug:"question-tag");
    register_post_type( 'question',
        array(
        	'label' => __('Questions','vbegy'),
            'labels' => array(
				'name'               => __('Questions','vbegy'),
				'singular_name'      => __('Questions','vbegy'),
				'menu_name'          => __('Questions','vbegy'),
				'name_admin_bar'     => __('Questions','vbegy'),
				'add_new'            => __('Add New','vbegy'),
				'add_new_item'       => __('Add New question','vbegy'),
				'new_item'           => __('New Question','vbegy'),
				'edit_item'          => __('Edit Question','vbegy'),
				'view_item'          => __('View Question','vbegy'),
				'view_items'         => __('View Questions','vbegy'),
				'all_items'          => __('All Questions','vbegy'),
				'search_items'       => __('Search Questions','vbegy'),
				'parent_item_colon'  => __('Parent Question:','vbegy'),
				'not_found'          => __('No Questions Found.','vbegy'),
				'not_found_in_trash' => __('No Questions Found in Trash.','vbegy'),
            ),
            'description'         => '',
            'public'              => true,
            'show_ui'             => true,
            'capability_type'     => 'post',
            'publicly_queryable'  => true,
            'exclude_from_search' => false,
            'hierarchical'        => false,
            'rewrite'             => array( 'slug' => $questions_slug, 'with_front' => false ),
            'query_var'           => true,
            'show_in_rest'        => true,
            'has_archive'         => true,
			'menu_position'       => 5,
            'supports'            => array('title','editor','comments'),
        )
    );
    
	$labels = array(
		'name'              => __('Categories','vbegy'),
		'singular_name'     => __('Categories','vbegy'),
		'search_items'      =>  __('Search in categories','vbegy'),
		'all_items'         => __('All categories','vbegy'),
		'parent_item'       => __('Categories','vbegy'),
		'parent_item_colon' => __('Categories','vbegy'),
		'edit_item'         => __('Edit','vbegy'),
		'update_item'       => __('Edit','vbegy'),
		'add_new_item'      => __('Add a new category','vbegy'),
		'new_item_name'     => __('Add a new category','vbegy')
	);
	
	register_taxonomy('question-category','question',array(
		'hierarchical' => true,
		'labels'       => $labels,
		'show_ui'      => true,
		'query_var'    => true,
		'show_in_rest' => true,
		'rewrite'      => array( 'slug' => $category_questions_slug, 'with_front' => false ),
	));
	
	register_taxonomy( 'question_tags',
		array('question'),
		array(
			'hierarchical' => false,
			'labels' => array(
				'name'              => __('Tags','vbegy'),
				'singular_name'     => __('Tags','vbegy'),
				'search_items'      =>  __('Search in tags','vbegy'),
				'all_items'         => __('All tags','vbegy'),
				'parent_item'       => __('Tags','vbegy'),
				'parent_item_colon' => __('Tags','vbegy'),
				'edit_item'         => __('Edit','vbegy'),
				'update_item'       => __('Edit','vbegy'),
				'add_new_item'      => __('Add new tag','vbegy'),
				'new_item_name'     => __('Add new tag','vbegy')
			),
			'show_ui'      => true,
			'query_var'    => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => $tag_questions_slug ),
		)
	);
}  
add_action( 'init', 'question_post_types_init', 0 );
/* vpanel_remove_meta_boxes */
function vpanel_remove_meta_boxes() {
	remove_meta_box( 'question-categorydiv', 'question', 'side' );
}
add_action( 'admin_menu' , 'vpanel_remove_meta_boxes' );
/* Admin columns for post types */
function askme_question_columns($old_columns){
	$columns = array();
	$columns["cb"]       = "<input type=\"checkbox\">";
	$columns["title"]    = __("Title","vbegy");
	$columns["type"]     = __("Type","vbegy");
	$columns["author"]   = __("Author","vbegy");
	$columns["category"] = __("Category","vbegy");
	$columns["tag"]      = __("Tags","vbegy");
	$columns["comments"] = "<span class='vers comment-grey-bubble' title='".__("Answers","vbegy")."'><span class='screen-reader-text'>".__("Answers","vbegy")."</span></span>";
	$columns["date"]     = __("Date","vbegy");
	return $columns;
}
add_filter('manage_edit-question_columns', 'askme_question_columns');

function askme_question_custom_columns($column) {
	global $post;
	$question_details = question_get_question_details( $post->ID );
	switch ( $column ) {
		case 'type' :
			$question_poll = get_post_meta($post->ID,'question_poll',true);
			if ($question_poll == 1) {
				echo '<a href="'.admin_url('edit.php?post_type=question&types=poll').'">'.__("Poll","vbegy").'</a>';
			}else {
				echo '<a href="'.admin_url('edit.php?post_type=question&types=question').'">'.__("Question","vbegy").'</a>';
			}
		break;
		case 'category' :
			$question_category = wp_get_post_terms($post->ID,'question-category',array("fields" => "all"));
			if (isset($question_category[0])) {?>
				<a href="<?php echo admin_url('edit.php?question-category='.$question_category[0]->slug.'&post_type=question');?>"><?php echo $question_category[0]->name?></a>
			<?php }else {
				echo '<span aria-hidden="true">—</span><span class="screen-reader-text">'.__("No category","vbegy").'</span>';
			}
		break;
		case 'tag' :
			$terms = wp_get_object_terms($post->ID,'question_tags');
			if ($terms) :
				$terms_array = array();
				foreach ($terms as $term) :
					$terms_array[] = '<a href="'.admin_url('edit.php?question_tags='.$term->slug.'&post_type=question').'">'.$term->name.'</a>';
				endforeach;
				echo implode(', ',$terms_array);
			else:
				echo '<span aria-hidden="true">—</span><span class="screen-reader-text">'.__("No tags","vbegy").'</span>';
			endif;
		break;
	}
}
add_action('manage_question_posts_custom_column', 'askme_question_custom_columns', 2);

if (!function_exists('question_get_question_details')) {
	function question_get_question_details( $post_id ) { 
		$status = current(wp_get_object_terms( $post_id, 'site_status' ));
		return $post_id;
	}
}
function question_updated_messages($messages) {
  global $post,$post_ID;
  $messages['question'] = array(
    0 => '',
    1 => sprintf( __('Updated. <a href="%s">View question</a>','vbegy'),esc_url(get_permalink($post_ID))),
  );
  return $messages;
}
add_filter('post_updated_messages','question_updated_messages');

if (!function_exists('get_question_details')) {
	function get_question_details( $post_id ) { 
		
		$category = current(wp_get_object_terms($post_id,'question-category'));
		$video_type = get_post_meta($post_id,'video_type',true);
		$video_id = get_post_meta($post_id,'video_id',true);
		
		if (!isset($category->name)) $category = '';
		
		$question_details = array(
			'category'   => $category,
			'video_type' => $video_type,
			'video_id'   => $video_id,
		);
		return $question_details;
	}
}
/* askme_questions_status */
add_filter( "views_edit-question", "askme_questions_status" );
function askme_questions_status($status) {
	global $wpdb;
	$prepare = $wpdb->prepare("SELECT $wpdb->posts.* FROM $wpdb->posts INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id ) WHERE 1=1 AND ( ( $wpdb->postmeta.meta_key = 'question_poll' AND $wpdb->postmeta.meta_value = %s ) ) AND $wpdb->posts.post_type = 'question' AND (($wpdb->posts.post_status = 'publish')) GROUP BY $wpdb->posts.ID ORDER BY $wpdb->posts.post_date DESC",1);
	$result = $wpdb->get_results($prepare);
	$query_poll_count = count($result);
	$query_question_count = wp_count_posts("question")->publish-$query_poll_count;
	if (isset($_GET['types'])) {
		$get_status = esc_attr($_GET['types']);
	}
	return array_merge( $status, array(
		'question' => '<a href="'.admin_url('edit.php?post_type=question&types=question').'"'.(isset($get_status) && $get_status == "question"?' class="current"':'').'>'.__('Question','vbegy').' ('.$query_question_count.')</a>',
		'poll' => '<a href="'.admin_url('edit.php?post_type=question&types=poll').'"'.(isset($get_status) && $get_status == "poll"?' class="current"':'').'>'.__('Poll','vbegy').' ('.$query_poll_count.')</a>',
	));
}
add_action('current_screen','askme_questions_exclude',10,2);
function askme_questions_exclude($screen) {
	if ($screen->id != 'edit-question')
		return;
	if (isset($_GET['types'])) {
		$get_status = esc_attr($_GET['types']);
	}
	if (isset($get_status) && ($get_status == "question" || $get_status == "poll")) {
		add_action('pre_get_posts','askme_list_questions',10,1);
	}
}
function askme_list_questions($clauses) {
	if (isset($_GET['types'])) {
		$get_status = esc_attr($_GET['types']);
	}
	if (isset($get_status) && $get_status == "poll") {
		$clauses->query_vars['meta_key'] = "question_poll";
		$clauses->query_vars['meta_value'] = 1;
		$clauses->query_vars['post_type'] = "question";
	}else {
		$clauses->query_vars['meta_key'] = "question_poll";
		$clauses->query_vars['meta_value'] = 2;
		$clauses->query_vars['post_type'] = "question";
	}
}
/* Add Meta Boxes */
add_action( 'add_meta_boxes', 'question_meta_boxes' );
function question_meta_boxes() {
	add_meta_box( 'question_info', __('Questions','vbegy'), 'vpanel_question_meta', 'question', 'normal', 'high' );
}
/* Question Meta Box */
function vpanel_question_meta() {
	global $post;
	wp_nonce_field( 'vpanel_save_question_meta', 'vpanel_save_question_meta_nonce' );
	$question_id = $post->ID;
	$question_details = get_question_details( $question_id );
	?>
	<style type="text/css">
	.rwmb-field {
		margin: 10px 0;
	}
	.rwmb-label,.rwmb-input {
		display: inline-block;
		vertical-align: top;
	}
	.rwmb-label {
		width: 24%;
	}
	p.description {
		margin: 2px 0 5px;
	}
	p.description, span.description {
		font-family: sans-serif;
		font-size: 12px;
		font-style: italic;
	}
	</style>
	
	<?php if ($post->post_author == 0) {
		$question_username = get_post_meta($post->ID, 'question_username',true);
		$question_email = get_post_meta($post->ID, 'question_email',true);?>
		<ul>
			<li><div class='clear'></div><br><span class="dashicons dashicons-admin-users"></span> : <?php echo $question_username?></li>
			<li><div class='clear'></div><br><span class="dashicons dashicons-email-alt"></span> : <?php echo $question_email?></li>
		</ul>
	<?php }
	
	$added_file = get_post_meta($post->ID, 'added_file', true);
	if ($added_file != "") {
		echo "<ul><li><div class='clear'></div><br><a href='".wp_get_attachment_url($added_file)."'>".__("Attachment","vbegy")."</a> - <a class='delete-this-attachment single-attachment' href='".$added_file."'>".__("Delete","vbegy")."</a></li></ul>";
	}
	$attachment_m = get_post_meta($post->ID, 'attachment_m');
	if (isset($attachment_m) && is_array($attachment_m) && !empty($attachment_m)) {
		$attachment_m = $attachment_m[0];
		if (isset($attachment_m) && is_array($attachment_m)) {
			echo "<ul>";
				foreach ($attachment_m as $key => $value) {
					echo "<li><div class='clear'></div><br><a href='".wp_get_attachment_url($value["added_file"])."'>".__("Attachment","vbegy")."</a> - <a class='delete-this-attachment' href='".$value["added_file"]."'>".__("Delete","vbegy")."</a></li>";
				}
			echo "</ul>";
		}
	}
	
    $custom = get_post_custom($post->ID);
	if (!empty($custom["ask"])) {
		$asks = unserialize($custom["ask"][0]);
	}?>
	<div class="rwmb-field">

		<div class="rwmb-label">
			<label for="vpanel_question_poll"><?php _e("This question is a poll?","vbegy")?></label>
		</div>
		<div class="rwmb-input">
        	<?php $question_poll = get_post_meta($post->ID, 'question_poll', true)?>
			<input type="checkbox" id="vpanel_question_poll" name="question_poll" value="1" <?php if ($question_poll != "" && $question_poll == 1){echo 'checked="checked"';} ?>>
		</div><div class="clear"></div>

        <div class="vpanel_poll_options" <?php if ($question_poll == "") {echo "style='display:none'";}?>>
			<input id="upload_add_ask" type="button" class="question_poll" value="<?php _e("Add a new option to poll","vbegy")?>">
			<div class="clear"></div>
			<div class="rwmb-label">
				<label><?php _e("Poll Options","vbegy")?></label><br>
			</div>
			<ul id="question_poll_item">
				<?php $i=0;
				if(isset($asks)){
					foreach( $asks as $ask ):
						$i++;?>
						<li id="listItem_<?php echo $i?>"  class="ui-state-default">
							<div class="widget-content option-item">
								<div class="rwmb-input">
									<input id="ask[<?php echo $i?>][title]" name="ask[<?php echo $i?>][title]" value="<?php echo stripslashes( $ask['title'] ) ?>" type="text">
									<input id="ask[<?php echo $i?>][value]" name="ask[<?php echo $i?>][value]" value="<?php echo stripslashes( $ask['value'] ) ?>" type="hidden">
									<input id="ask[<?php echo $i?>][id]" name="ask[<?php echo $i?>][id]" value="<?php echo stripslashes( $ask['id'] ) ?>" type="hidden">
									<a class="del-cat">x</a>
								</div>
							</div>
						</li>
					<?php
					endforeach;
				}?>
			</ul>
			<script> var nextCell = <?php echo $i+1?> ;</script>
        </div><div class="clear"></div><br>
        
        <?php if ($question_poll != "" && $question_poll == 1) {
        	if (isset($asks)) {
        		echo '<div class="rwmb-label"><label>Stats of User</label></div><div class="clear"></div><br>';
        		foreach( $asks as $ask ):$i++;
        			echo stripslashes( $ask['title'] ).' --- '.(isset($ask['value']) && $ask['value'] != 0?stripslashes( $ask['value'] ):0)." Votes <br>";
	        		if (isset($ask['user_ids']) && is_array($ask['user_ids'])) {
	        			foreach ($ask['user_ids'] as $key => $value) {
	        				if ($value != 0) {
	        					$get_user_by = get_user_by("id",$value);
	        					if (isset($get_user_by) && is_array($get_user_by)) {
	        						echo "<div class='vpanel_checkbox_input'><p class='description'>".$get_user_by->display_name." Has vote for ".stripslashes($ask['title'])."</p></div>";
	        					}
	        				}else {
	        					echo "<div class='vpanel_checkbox_input'><p class='description'>Unregistered user Has vote for ".stripslashes( $ask['title'] )."</p></div>";
	        				}
	        			}
	        			echo "<br>";
	        		}
	        	endforeach;?>
	        	<div class="clear"></div><br>
        	<?php }
        }?>
        
		<div class="rwmb-label">
			<label for="vpanel_question-category"><?php _e("Category","vbegy")?></label>
		</div>
		<div class="rwmb-input">
			<div class="styled-select">
				<?php wp_dropdown_categories(array(
				    'orderby'      => 'name',
				    'hide_empty'   => 0,
				    'hierarchical' => 1,
				    'taxonomy'     => 'question-category',
				    'class'        => 'rwmb-select',
				    'name'         => 'question-category',
				    'id'           => 'vpanel_question-category',
				    'selected'     => (isset($question_details['category']->term_id) && $question_details['category']->term_id != ""?$question_details['category']->term_id:"")
				))?>
			</div>
			<p class="description"><?php _e("Choose from here the question category.","vbegy")?></p>
		</div><div class="clear"></div>
		
		<div class="rwmb-label">
			<label for="vpanel_video_description"><?php _e("Video description","vbegy")?></label>
		</div>
		<div class="rwmb-input">
			<?php
			$video_description = get_post_meta($post->ID, 'video_description', true);
			?>
			<input type="checkbox" id="vpanel_video_description" name="video_description" value="1" <?php if ($video_description != "" && $video_description == 1){echo 'checked="checked"';} ?>>
			<p class="description"><?php _e("Do you need a video to description the problem better ?","vbegy")?></p>
		</div>
		
		<div class="video_description">
			<div class="rwmb-label">
				<label for="vpanel_video_type"><?php _e("Video type","vbegy")?></label>
			</div>
			<div class="rwmb-input">
				<div class="styled-select">
					<select class="rwmb-select" id="vpanel_video_type" name="video_type">
						<option value="youtube" <?php echo (isset($question_details['video_type']) && $question_details['video_type'] == "youtube"?' selected="selected"':'')?>>Youtube</option>
						<option value="vimeo" <?php echo (isset($question_details['video_type']) && $question_details['video_type'] == "vimeo"?' selected="selected"':'')?>>Vimeo</option>
						<option value="daily" <?php echo (isset($question_details['video_type']) && $question_details['video_type'] == "daily"?' selected="selected"':'')?>>Dialymotion</option>
					</select>
				</div>
				<p class="description"><?php _e("Choose from here the video type.","vbegy")?></p>
			</div><div class="clear"></div>
		
			<div class="rwmb-label">
				<label for="vpanel_video_id"><?php _e("Video ID","vbegy")?></label>
			</div>
			<div class="rwmb-input">
				<input type="text" class="rwmb-select" id="vpanel_video_id" name="video_id" <?php echo (isset($question_details['video_id'])?' value="'.$question_details['video_id'].'"':'')?>>
				<p class="description"><?php _e("Put here the video id : http://www.youtube.com/watch?v=sdUUx5FdySs EX : 'sdUUx5FdySs'.","vbegy")?></p>
			</div><div class="clear"></div>
		</div>
		
		<div class="rwmb-label">
			<label for="vpanel_remember_answer"><?php _e("Notified by e-mail","vbegy")?></label>
		</div>
		<div class="rwmb-input">
			<?php
			$remember_answer = get_post_meta($post->ID, 'remember_answer', true);
			?>
			<input type="checkbox" id="vpanel_remember_answer" name="remember_answer" value="1" <?php if ($remember_answer != "" && $remember_answer == 1){echo 'checked="checked"';} ?>>
			<p class="description"><?php _e("Notified by e-mail at incoming answers","vbegy")?></p>
		</div>
        
	</div>
	<?php
}	
/* Process question Meta Box */
add_action( 'save_post', 'vpanel_question_meta_save', 1, 2 );
function vpanel_question_meta_save( $post_id, $post ) {
	global $wpdb,$post;
	if ( !$_POST ) return $post_id;
	if ( isset($post) && $post->post_type != 'question' ) return $post_id;
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;
	if ( !isset($_POST['vpanel_save_question_meta_nonce']) || !wp_verify_nonce( $_POST['vpanel_save_question_meta_nonce'], 'vpanel_save_question_meta' )) return $post_id;
	if ( !current_user_can( 'edit_post', $post_id )) return $post_id;
	
	$data = array();
	
	// Get Post Data
	$data['question-category'] = (isset($_POST['question-category'])?stripslashes( $_POST['question-category'] ):"");
	
	// category
	if (isset($_POST['question-category'])) {
		$new_term_slug = get_term_by( 'id', $data['question-category'], 'question-category')->slug;
		wp_set_object_terms( $post_id, $new_term_slug, 'question-category' );
	}

  	if( isset($_POST['ask']) && $_POST['ask'] != "" ){
		update_post_meta($post_id, 'ask' , $_POST['ask']);
	}
	
  	if( isset($_POST['question_poll']) && $_POST['question_poll'] != "" ){
		update_post_meta($post_id, 'question_poll' , $_POST['question_poll']);
	}else {
		update_post_meta($post_id, 'question_poll' , 2);
	}
	
  	if( isset($_POST['best_answer']) && $_POST['best_answer'] != "" ){
		update_post_meta($post_id, 'best_answer' , $_POST['best_answer']);		
	}
	
	if ( isset($_POST['video_type']) && $_POST['video_type'] != "" ) {
		update_post_meta($post_id, 'video_type', $_POST['video_type']);
	}
		
	if ( isset($_POST['video_id']) && $_POST['video_id'] != "" ) {
		update_post_meta($post_id, 'video_id', $_POST['video_id']);
	}
	
  	if( isset($_POST['remember_answer']) && $_POST['remember_answer'] != "" ){
		update_post_meta($post_id, 'remember_answer' , $_POST['remember_answer']);
	}else {
		delete_post_meta($post_id, 'remember_answer');
	}
	
	if( isset($_POST['video_description']) && $_POST['video_description'] != "" ){
		update_post_meta($post_id, 'video_description' , $_POST['video_description']);
	}else {
		delete_post_meta($post_id, 'video_description');
	}
	
	$user_id = get_current_user_id();
	
	$add_questions = get_user_meta($user_id,"add_questions_all",true);
	$add_questions_m = get_user_meta($user_id,"add_questions_m_".date_i18n('m_Y',current_time('timestamp')),true);
	$add_questions_d = get_user_meta($user_id,"add_questions_d_".date_i18n('d_m_Y',current_time('timestamp')),true);
	if ($add_questions_d == "" or $add_questions_d == 0) {
		add_user_meta($user_id,"add_questions_d_".date_i18n('d_m_Y',current_time('timestamp')),1);
	}else {
		update_user_meta($user_id,"add_questions_d_".date_i18n('d_m_Y',current_time('timestamp')),$add_questions_d+1);
	}
	
	if ($add_questions_m == "" or $add_questions_m == 0) {
		add_user_meta($user_id,"add_questions_m_".date_i18n('m_Y',current_time('timestamp')),1);
	}else {
		update_user_meta($user_id,"add_questions_m_".date_i18n('m_Y',current_time('timestamp')),$add_questions_m+1);
	}
	
	if ($add_questions == "" or $add_questions == 0) {
		add_user_meta($user_id,"add_questions_all",1);
	}else {
		update_user_meta($user_id,"add_questions_all",$add_questions+1);
	}
}
/* set_post_stats */
function set_post_stats() {
    $post_id = get_the_ID();
    if (is_single($post_id) || is_page($post_id)) {
        $current_stats = get_post_meta($post_id, 'post_stats', true);
        if (!isset($current_stats)) {
            add_post_meta($post_id, 'post_stats', 1, true);
        } else {
            update_post_meta($post_id, 'post_stats', $current_stats + 1);
        }
    }
}
add_action('wp_head', 'set_post_stats', 1000);
/* extra_category_fields */
function extra_category_fields_edit ($tag) {
	if (isset($tag->term_id)) {
		$t_id = $tag->term_id;
		$questions_category = get_option("questions_category_$t_id");
	}?>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="private">Private category?</label></th>
		<td>
			<input id="private" class="checkbox of-input vpanel_checkbox" type="checkbox" name="questions_category[private]" <?php echo isset($questions_category['private']) && $questions_category['private'] == "on"?'checked="checked"':'';?>>
			<p class="description">Select 'On' to enable private category. (In private categories questions can only be seen by the author of the question and the admin).</p><br><br>
			<div class="clear"></div>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="special">Special category?</label></th>
		<td>
			<input id="special" class="checkbox of-input vpanel_checkbox" type="checkbox" name="questions_category[special]" <?php echo isset($questions_category['special']) && $questions_category['special'] == "on"?'checked="checked"':'';?>>
			<p class="description">Select 'On' to enable special category. (In a special category, the admin must answer the question before anyone else).</p><br><br>
			<input type="hidden" name="questions_category[special_private]" value="special_private">
			<div class="clear"></div>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="new">New category?</label></th>
		<td>
			<input id="new" class="checkbox of-input vpanel_checkbox" type="checkbox" name="questions_category[new]" <?php echo isset($questions_category['new']) && $questions_category['new'] == "on"?'checked="checked"':'';?>>
			<p class="description">Select 'On' to enable new category. (In a new category, the admin must answer the question before anyone else and the user has add question and admin only can answer).</p><br><br>
			<div class="clear"></div>
		</td>
	</tr>
<?php
}
function extra_category_fields_edit_style ($tag) {
	if (isset($tag->term_id)) {
		$t_id = $tag->term_id;
		$categories = get_option("categories_$t_id");
	}?>
	<tr class="form-field">
		<th scope="row" valign="top"><label>Page layout</label></th>
		<td>
			<div class="rwmb-input">
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_layout]" value="default" <?php echo isset($categories['cat_layout']) && $categories['cat_layout'] == "default"?'checked="checked"':''.empty($categories['cat_layout'])?'checked="checked"':'';?>></label>
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_layout]" value="full" <?php echo isset($categories['cat_layout']) && $categories['cat_layout'] == "full"?'checked="checked"':'';?>></label>
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_layout]" value="fixed" <?php echo isset($categories['cat_layout']) && $categories['cat_layout'] == "fixed"?'checked="checked"':'';?>></label>
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_layout]" value="fixed_2" <?php echo isset($categories['cat_layout']) && $categories['cat_layout'] == "fixed_2"?'checked="checked"':'';?>></label>
			</div>
			<div class="clear"></div>
		</td>
	</tr>
	
	<tr class="form-field">
		<th scope="row" valign="top"><label>Choose template</label></th>
		<td>
			<div class="rwmb-input">
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_template]" value="default" <?php echo isset($categories['cat_template']) && $categories['cat_template'] == "default"?'checked="checked"':''.empty($categories['cat_template'])?'checked="checked"':'';?>></label>
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_template]" value="grid_1200" <?php echo isset($categories['cat_template']) && $categories['cat_template'] == "grid_1200"?'checked="checked"':'';?>></label>
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_template]" value="grid_970" <?php echo isset($categories['cat_template']) && $categories['cat_template'] == "grid_970"?'checked="checked"':'';?>></label>
			</div>
			<div class="clear"></div>
		</td>
	</tr>
	
	<tr class="form-field">
		<th scope="row" valign="top"><label>Sidebar layout</label></th>
		<td>
			<div class="rwmb-input">
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_sidebar_layout]" value="default" <?php echo isset($categories['cat_sidebar_layout']) && $categories['cat_sidebar_layout'] == "default"?'checked="checked"':''.empty($categories['cat_sidebar_layout'])?'checked="checked"':'';?>></label>
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_sidebar_layout]" value="right" <?php echo isset($categories['cat_sidebar_layout']) && $categories['cat_sidebar_layout'] == "right"?'checked="checked"':'';?>></label>
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_sidebar_layout]" value="full" <?php echo isset($categories['cat_sidebar_layout']) && $categories['cat_sidebar_layout'] == "full"?'checked="checked"':'';?>></label>
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_sidebar_layout]" value="left" <?php echo isset($categories['cat_sidebar_layout']) && $categories['cat_sidebar_layout'] == "left"?'checked="checked"':'';?>></label>
			</div>
			<div class="clear"></div>
		</td>
	</tr>
	
	<tr class="form-field">
		<th scope="row" valign="top"><label for="cat_sidebar">Sidebar</label></th>
		<td>
			<div class="styled-select">
				<select name="categories[cat_sidebar]" id="cat_sidebar">
					<?php $sidebars = get_option('sidebars');
					echo "<option ".(isset($categories['cat_sidebar']) && $categories['cat_sidebar'] == "default"?'selected="selected"':'')." value='default'>Default</option>";
					foreach ($GLOBALS['wp_registered_sidebars'] as $sidebar) {
						echo "<option ".(isset($categories['cat_sidebar']) && $categories['cat_sidebar'] == $sidebar['id']?'selected="selected"':'')." value='".$sidebar['id']."'>".$sidebar['name']."</option>";
					}?>
				</select>
			</div>
			<div class="clear"></div>
		</td>
	</tr>
	
	<tr class="form-field">
		<th scope="row" valign="top"><label>Site skin</label></th>
		<td>
			<div class="rwmb-input">
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin_l]" value="default" <?php echo isset($categories['cat_skin_l']) && $categories['cat_skin_l'] == "default"?'checked="checked"':''.empty($categories['cat_skin_l'])?'checked="checked"':'';?>></label>
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin_l]" value="site_light" <?php echo isset($categories['cat_skin_l']) && $categories['cat_skin_l'] == "site_light"?'checked="checked"':'';?>></label>
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin_l]" value="site_dark" <?php echo isset($categories['cat_skin_l']) && $categories['cat_skin_l'] == "site_dark"?'checked="checked"':'';?>></label>
			</div>
			<div class="clear"></div>
		</td>
	</tr>
	
	<tr class="form-field">
		<th scope="row" valign="top"><label>Choose Your Skin</label></th>
		<td>
			<div class="rwmb-input">
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="default" <?php echo isset($categories['cat_skin']) && $categories['cat_skin'] == "default"?'checked="checked"':''.empty($categories['cat_skin'])?'checked="checked"':'';?>></label>
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="skin" <?php echo isset($categories['cat_skin']) && $categories['cat_skin'] == "skin"?'checked="checked"':'';?>></label>
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="blue" <?php echo isset($categories['cat_skin']) && $categories['cat_skin'] == "blue"?'checked="checked"':'';?>></label>
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="gray" <?php echo isset($categories['cat_skin']) && $categories['cat_skin'] == "gray"?'checked="checked"':'';?>></label>
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="green" <?php echo isset($categories['cat_skin']) && $categories['cat_skin'] == "green"?'checked="checked"':'';?>></label>
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="moderate_cyan" <?php echo isset($categories['cat_skin']) && $categories['cat_skin'] == "moderate_cyan"?'checked="checked"':'';?>></label>
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="orange" <?php echo isset($categories['cat_skin']) && $categories['cat_skin'] == "orange"?'checked="checked"':'';?>></label>
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="purple" <?php echo isset($categories['cat_skin']) && $categories['cat_skin'] == "purple"?'checked="checked"':'';?>></label>
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="red" <?php echo isset($categories['cat_skin']) && $categories['cat_skin'] == "red"?'checked="checked"':'';?>></label>
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="strong_cyan" <?php echo isset($categories['cat_skin']) && $categories['cat_skin'] == "strong_cyan"?'checked="checked"':'';?>></label>
				<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="yellow" <?php echo isset($categories['cat_skin']) && $categories['cat_skin'] == "yellow"?'checked="checked"':'';?>></label>
			</div>
			<div class="clear"></div>
		</td>
	</tr>
	
	<tr class="form-field">
		<th scope="row" valign="top"><label for="primary_color">Primary color</label></th>
		<td>
			<input id="primary_color" class="wp-color-picker" type="text" name="categories[primary_color]" value="<?php echo isset($categories['primary_color']) && $categories['primary_color'] != ""?$categories['primary_color']:'';?>">
			<div class="clear"></div>
		</td>
	</tr>
	
	<tr class="form-field">
		<th scope="row" valign="top"><label for="secondary_color">Secondary Color ( it's darkness more than primary color )</label></th>
		<td>
			<input id="secondary_color" class="wp-color-picker" type="text" name="categories[secondary_color]" value="<?php echo isset($categories['secondary_color']) && $categories['secondary_color'] != ""?$categories['secondary_color']:'';?>">
			<div class="clear"></div>
		</td>
	</tr>
	
	<tr class="form-field form-field-images">
		<th scope="row" valign="top"><label>Background image</label></th>
		<td>
			<input type="text" size="36" class="upload upload_meta regular-text" id="background_img" name="categories[background_img]" value="<?php echo (isset($categories['background_img']) && $categories['background_img'] != ""?$categories['background_img']:'');?>">
			<input id="background_img_button" class="upload_image_button button upload-button-2" type="button" value="Upload Image">
			<div class="clear"></div>
		</td>
	</tr>
	
	<tr class="form-field">
		<th scope="row" valign="top"><label for="background_color">Background color</label></th>
		<td>
			<input id="background_color" class="wp-color-picker" type="text" name="categories[background_color]" value="<?php echo isset($categories['background_color']) && $categories['background_color'] != ""?$categories['background_color']:'';?>">
			<div class="clear"></div>
		</td>
	</tr>
	
	<tr class="form-field">
		<th scope="row" valign="top"><label for="background_repeat">Background repeat</label></th>
		<td>
			<div class="rwmb-input">
				<div class="styled-select">
					<select class="rwmb-select" name="categories[background_repeat]" id="background_repeat" size="0">
						<option value="repeat" <?php echo (isset($categories['background_repeat']) && $categories['background_repeat'] == 'repeat'?'selected="selected"':'')?>>repeat</option>
						<option value="no-repeat" <?php echo (isset($categories['background_repeat']) && $categories['background_repeat'] == 'no-repeat'?'selected="selected"':'')?>>no-repeat</option>
						<option value="repeat-x" <?php echo (isset($categories['background_repeat']) && $categories['background_repeat'] == 'repeat-x'?'selected="selected"':'')?>>repeat-x</option>
						<option value="repeat-y" <?php echo (isset($categories['background_repeat']) && $categories['background_repeat'] == 'repeat-y'?'selected="selected"':'')?>>repeat-y</option>
					</select>
				</div>
			</div>
			<div class="clear"></div>
		</td>
	</tr>
	
	<tr class="form-field">
		<th scope="row" valign="top"><label for="background_fixed">Background fixed</label></th>
		<td>
			<div class="rwmb-input">
				<div class="styled-select">
					<select class="rwmb-select" name="categories[background_fixed]" id="background_fixed" size="0">
						<option value="fixed" <?php echo (isset($categories['background_fixed']) && $categories['background_fixed'] == 'fixed'?'selected="selected"':'')?>>fixed</option>
						<option value="scroll" <?php echo (isset($categories['background_fixed']) && $categories['background_fixed'] == 'scroll'?'selected="selected"':'')?>>scroll</option>
					</select>
				</div>
			</div>
			<div class="clear"></div>
		</td>
	</tr>
	
	<tr class="form-field">
		<th scope="row" valign="top"><label for="background_position_x">Background position x</label></th>
		<td>
			<div class="rwmb-input">
				<div class="styled-select">
					<select class="rwmb-select" name="categories[background_position_x]" id="background_position_x" size="0">
						<option value="left" <?php echo (isset($categories['background_position_x']) && $categories['background_position_x'] == 'left'?'selected="selected"':'')?>>left</option>
						<option value="center" <?php echo (isset($categories['background_position_x']) && $categories['background_position_x'] == 'center'?'selected="selected"':'')?>>center</option>
						<option value="right" <?php echo (isset($categories['background_position_x']) && $categories['background_position_x'] == 'right'?'selected="selected"':'')?>>right</option>
					</select>
				</div>
			</div>
			<div class="clear"></div>
		</td>
	</tr>
	
	<tr class="form-field">
		<th scope="row" valign="top"><label for="background_position_y">Background position y</label></th>
		<td>
			<div class="rwmb-input">
				<div class="styled-select">
					<select class="rwmb-select" name="categories[background_position_y]" id="background_position_y" size="0">
						<option value="top" <?php echo (isset($categories['background_position_y']) && $categories['background_position_y'] == 'top'?'selected="selected"':'')?>>top</option>
						<option value="center" <?php echo (isset($categories['background_position_y']) && $categories['background_position_y'] == 'center'?'selected="selected"':'')?>>center</option>
						<option value="bottom" <?php echo (isset($categories['background_position_y']) && $categories['background_position_y'] == 'bottom'?'selected="selected"':'')?>>bottom</option>
					</select>
				</div>
			</div>
			<div class="clear"></div>
		</td>
	</tr>
	
	<tr class="form-field">
		<th scope="row" valign="top"><label for="">Full Screen Background</label></th>
		<td>
			<input id="background_full" class="checkbox of-input vpanel_checkbox" type="checkbox" name="categories[background_full]" <?php echo isset($categories['background_full']) && $categories['background_full'] == "on"?'checked="checked"':'';?>>
			<div class="clear"></div>
		</td>
	</tr>
<?php
}
function extra_category_fields ($tag) {?>
	<div class="form-field">
		<label for="private">Private category?</label>
		<input id="private" class="checkbox of-input vpanel_checkbox" type="checkbox" name="questions_category[private]">
		<p>Select 'On' to enable private category. (In private categories questions can only be seen by the author of the question and the admin).</p>
	</div>
	<div class="form-field">
		<label for="special">Special category?</label>
		<input id="special" class="checkbox of-input vpanel_checkbox" type="checkbox" name="questions_category[special]">
		<p>Select 'On' to enable special category. (In a special category, the admin must answer the question before anyone else).</p>
	</div>
	<div class="form-field">
		<label for="new">New category?</label>
		<input id="new" class="checkbox of-input vpanel_checkbox" type="checkbox" name="questions_category[new]">
		<p>Select 'On' to enable new category. (In a new category, the admin must answer the question before anyone else and the user has add question and admin only can answer).</p>
	</div>
<?php
}
function extra_category_fields_style ($tag) {?>
	<div class="form-field">
		<label>Page layout</label>
		<div class="rwmb-input">
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_layout]" value="default" checked="checked"></label>
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_layout]" value="full"></label>
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_layout]" value="fixed"></label>
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_layout]" value="fixed_2"></label>
		</div>
	</div>
	
	<div class="form-field">
		<label>Choose template</label>
		<div class="rwmb-input">
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_template]" value="default" checked="checked"></label>
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_template]" value="grid_1200"></label>
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_template]" value="grid_970"></label>
		</div>
	</div>
	
	<div class="form-field">
		<label>Sidebar layout</label>
		<div class="rwmb-input">
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_sidebar_layout]" value="default" checked="checked"></label>
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_sidebar_layout]" value="right"></label>
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_sidebar_layout]" value="full"></label>
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_sidebar_layout]" value="left"></label>
		</div>
	</div>
	
	<div class="form-field">
		<label for="cat_sidebar">Sidebar</label>
		<div class="styled-select">
			<select name="categories[cat_sidebar]" id="cat_sidebar">
				<?php $sidebars = get_option('sidebars');
				echo "<option value='default'>Default</option>";
				foreach ($GLOBALS['wp_registered_sidebars'] as $sidebar) {
					echo "<option value='".$sidebar['id']."'>".$sidebar['name']."</option>";
				}?>
			</select>
		</div>
	</div>
	
	<div class="form-field">
		<label>Site skin</label>
		<div class="rwmb-input">
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin_l]" value="default" checked="checked"></label>
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin_l]" value="site_light"></label>
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin_l]" value="site_dark"></label>
		</div>
	</div>
	
	<div class="form-field">
		<label>Choose Your Skin</label>
		<div class="rwmb-input">
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="default" checked="checked"></label>
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="skin"></label>
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="blue"></label>
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="gray"></label>
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="green"></label>
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="moderate_cyan"></label>
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="orange"></label>
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="purple"></label>
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="red"></label>
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="strong_cyan"></label>
			<label class="radio_no_margin"><input type="radio" class="rwmb-radio" name="categories[cat_skin]" value="yellow"></label>
		</div>
	</div>
	
	<div class="form-field">
		<label for="primary_color">Primary color</label>
		<input id="primary_color" class="wp-color-picker" type="text" name="categories[primary_color]">
	</div>
	
	<div class="form-field">
		<label for="secondary_color">Secondary Color ( it's darkness more than primary color )</label>
		<input id="secondary_color" class="wp-color-picker" type="text" name="categories[secondary_color]">
	</div>
	
	<div class="form-field form-field-images">
		<label for="small_image">Background image</label>
		<input type="text" size="36" class="upload upload_meta regular-text" id="small_image" name="categories[background_img]">
		<input id="small_image_button" class="upload_image_button button upload-button-2" type="button" value="Upload Image">
	</div>
	
	<div class="form-field">
		<label for="background_color">Background color</label>
		<input id="background_color" class="wp-color-picker" type="text" name="categories[background_color]">
	</div>
	
	<div class="form-field">
		<label for="background_repeat">Background repeat</label>
		<div class="rwmb-input">
			<div class="styled-select">
				<select class="rwmb-select" name="categories[background_repeat]" id="background_repeat" size="0">
					<option value="repeat" selected="selected">repeat</option>
					<option value="no-repeat">no-repeat</option>
					<option value="repeat-x">repeat-x</option>
					<option value="repeat-y">repeat-y</option>
				</select>
			</div>
		</div>
	</div>
	
	<div class="form-field">
		<label for="background_fixed">Background fixed</label>
		<div class="rwmb-input">
			<div class="styled-select">
				<select class="rwmb-select" name="categories[background_fixed]" id="background_fixed" size="0">
					<option value="fixed" selected="selected">fixed</option>
					<option value="scroll">scroll</option>
				</select>
			</div>
		</div>
	</div>
	
	<div class="form-field">
		<label for="background_position_x">Background position x</label>
		<div class="rwmb-input">
			<div class="styled-select">
				<select class="rwmb-select" name="categories[background_position_x]" id="background_position_x" size="0">
					<option value="left" selected="selected">left</option>
					<option value="center">center</option>
					<option value="right">right</option>
				</select>
			</div>
		</div>
	</div>
	
	<div class="form-field">
		<label for="background_position_y">Background position y</label>
		<div class="rwmb-input">
			<div class="styled-select">
				<select class="rwmb-select" name="categories[background_position_y]" id="background_position_y" size="0">
					<option value="top" selected="selected">top</option>
					<option value="center">center</option>
					<option value="bottom">bottom</option>
				</select>
			</div>
		</div>
	</div>
	
	<div class="form-field">
		<label for="background_full">Full Screen Background</label>
		<input id="background_full" class="checkbox of-input vpanel_checkbox" type="checkbox" name="categories[background_full]">
	</div>
<?php
}
add_action('question-category_edit_form_fields','extra_category_fields_edit',10,2);
add_action ('question-category_add_form_fields','extra_category_fields',10,2);
/* style */
add_action('question-category_edit_form_fields','extra_category_fields_edit_style',10,2);
add_action ('question-category_add_form_fields','extra_category_fields_style',10,2);
add_action('category_edit_form_fields','extra_category_fields_edit_style',10,2);
add_action ('category_add_form_fields','extra_category_fields_style',10,2);
/* save_extra_category_fileds */
add_action('edited_question-category','save_extra_category_fileds',10,2);
add_action('create_question-category','save_extra_category_fileds',10,2);
/* save style */
add_action('edited_category','save_extra_category_fileds_style',10,2);
add_action('create_category','save_extra_category_fileds_style',10,2);
add_action('edited_question-category','save_extra_category_fileds_style',10,2);
add_action('create_question-category','save_extra_category_fileds_style',10,2);

add_action('edited_product_cat','save_extra_category_fileds_style',10,2);
add_action('create_product_cat','save_extra_category_fileds_style',10,2);
add_action('product_cat_edit_form_fields','extra_category_fields_edit_style',10,2);
add_action ('product_cat_add_form_fields','extra_category_fields_style',10,2);
function save_extra_category_fileds ($term_id) {
	if (isset($_POST['questions_category'])) {
		$t_id = $term_id;
		$questions_category = get_option("questions_category_$t_id");
		$questions_category = array_keys($_POST['questions_category']);
		foreach ($questions_category as $key){
			if (isset($_POST['questions_category'][$key])){
				$questions_category[$key] = $_POST['questions_category'][$key];
			}
		}
		update_option("questions_category_$t_id",$questions_category);
	}
}
function save_extra_category_fileds_style ($term_id) {
	if (isset($_POST['categories'])) {
		$t_id = $term_id;
		$categories = get_option("categories_$t_id");
		$categories = array_keys($_POST['categories']);
		foreach ($categories as $key){
			if (isset($_POST['categories'][$key])){
				$categories[$key] = $_POST['categories'][$key];
			}
		}
		update_option("categories_$t_id",$categories);
	}
}
if (is_admin()) {
	/* Count new reports */
	$ask_option_array = get_option("ask_option_array");
	if (is_array($ask_option_array)) {
		foreach ($ask_option_array as $key => $value) {
			$ask_one_option = get_option("ask_option_".$value);
			$post_no_empty = get_post($ask_one_option["post_id"]);
			if (!isset($post_no_empty)) {
				unset($ask_one_option);
			}
			if (isset($ask_one_option) && $ask_one_option["report_new"] == 1) {
				$count_report_new[] = $ask_one_option["report_new"];
			}
		}
	}
	/* Count new reports answers */
	$ask_option_answer_array = get_option("ask_option_answer_array");
	if (is_array($ask_option_answer_array)) {
		foreach ($ask_option_answer_array as $key => $value) {
			$ask_one_option = get_option("ask_option_answer_".$value);
			$comment_no_empty = get_comment($ask_one_option["comment_id"]);
			if (!isset($comment_no_empty)) {
				unset($ask_one_option);
			}
			if (isset($ask_one_option) && $ask_one_option["report_new"] == 1) {
				$count_report_answer_new[] = $ask_one_option["report_new"];
			}
		}
	}
	/* reports_delete */
	function reports_delete() {
		$reports_delete_id = (int)esc_html($_POST["reports_delete_id"]);
		/* delete option */
		delete_option("ask_option_".$reports_delete_id);
		$ask_option_array = get_option("ask_option_array");
		$ask_option = get_option("ask_option");
		$ask_option--;
		update_option("ask_option",$ask_option);
		$arr = array_diff($ask_option_array, array($reports_delete_id));
		update_option("ask_option_array",$arr);
		die();
	}
	add_action("wp_ajax_nopriv_reports_delete","reports_delete");
	add_action("wp_ajax_reports_delete","reports_delete");
	/* reports_view */
	function reports_view() {
		$reports_view_id = (int)esc_html($_POST["reports_view_id"]);
		/* option */
		$ask_one_option = get_option("ask_option_".$reports_view_id);
		$item_id_option = $ask_one_option["item_id_option"];
		foreach ($ask_one_option as $key => $value) {
			if ($key == "report_new") {
				$ask_one_option["report_new"] = 0;
			}
		}
		update_option("ask_option_".$reports_view_id,$ask_one_option);
		die();
	}
	add_action("wp_ajax_nopriv_reports_view","reports_view");
	add_action("wp_ajax_reports_view","reports_view");
	/* reports_answers_delete */
	function reports_answers_delete() {
		$reports_delete_id = (int)esc_html($_POST["reports_delete_id"]);
		/* delete option */
		delete_option("ask_option_answer_".$reports_delete_id);
		$ask_option_answer_array = get_option("ask_option_answer_array");
		$ask_option_answer = get_option("ask_option_answer");
		$ask_option_answer--;
		update_option("ask_option_answer",$ask_option_answer);
		$arr = array_diff($ask_option_answer_array, array($reports_delete_id));
		update_option("ask_option_answer_array",$arr);
		die();
	}
	add_action("wp_ajax_nopriv_reports_answers_delete","reports_answers_delete");
	add_action("wp_ajax_reports_answers_delete","reports_answers_delete");
	/* reports_answers_view */
	function reports_answers_view() {
		$reports_view_id = (int)esc_html($_POST["reports_view_id"]);
		echo $reports_view_id;
		/* option */
		$ask_one_option = get_option("ask_option_answer_".$reports_view_id);
		$item_id_option = $ask_one_option["item_id_option"];
		foreach ($ask_one_option as $key => $value) {
			if ($key == "report_new") {
				$ask_one_option["report_new"] = 0;
			}
		}
		update_option("ask_option_answer_".$reports_view_id,$ask_one_option);
		die();
	}
	add_action("wp_ajax_nopriv_reports_answers_view","reports_answers_view");
	add_action("wp_ajax_reports_answers_view","reports_answers_view");
	/* publishing_action_post */
	function publishing_action_post() {
		$post_ID = (int)$_POST["post_ID"];
	    $question_username = get_post_meta($post_ID, 'question_username', true);
	    $question_email = get_post_meta($post_ID, 'question_email', true);
	    $post_username = get_post_meta($post_ID, 'post_username', true);
	    $post_email = get_post_meta($post_ID, 'post_email', true);
	    if ((isset($question_username) && $question_username != "" && isset($question_email) && $question_email != "") || (isset($post_username) && $post_username != "" && isset($post_email) && $post_email != "")) {
	    	$get_post = get_post($post_ID);
	    	$publish_date = $get_post->post_date;
	        $data = array(
	        	'ID' => $post_ID,
	        	'post_author' => "No_user",
	        );
	    	wp_update_post($data);
	    }
	}
	add_action('wp_ajax_publishing_action_post','publishing_action_post');
	add_action('wp_ajax_nopriv_publishing_action_post','publishing_action_post');
	/* confirm_delete_attachment */
	function confirm_delete_attachment() {
		$attachment_id     = (int)$_POST["attachment_id"];
		$post_id           = (int)$_POST["post_id"];
		$single_attachment = esc_attr($_POST["single_attachment"]);
		if ($single_attachment == "Yes") {
			wp_delete_attachment($attachment_id);
			delete_post_meta($post_id, 'added_file');
		}else {
			$attachment_m = get_post_meta($post_id, 'attachment_m');
			if (isset($attachment_m) && is_array($attachment_m) && !empty($attachment_m)) {
				$attachment_m = $attachment_m[0];
				if (isset($attachment_m) && is_array($attachment_m)) {
					foreach ($attachment_m as $key => $value) {
						if ($value["added_file"] == $attachment_id) unset($attachment_m[$key]);
						wp_delete_attachment($value["added_file"]);
					}
				}
			}
			update_post_meta($post_id, 'attachment_m', $attachment_m);
		}
		die();
	}
	add_action('wp_ajax_confirm_delete_attachment','confirm_delete_attachment');
	add_action('wp_ajax_nopriv_confirm_delete_attachment','confirm_delete_attachment');
}
/* ask_add_admin_page_reports */
function ask_add_admin_page_reports() {
	$active_reports = vpanel_options("active_reports");
	if ($active_reports == 1) {
		global $count_report_new,$count_report_answer_new;
		$count_report_new = count($count_report_new);
		$count_report_answer_new = count($count_report_answer_new);
		$count_lasts = $count_report_new+$count_report_answer_new;
		$vpanel_page = add_menu_page('Reports', 'Reports <span class="count_report_new awaiting-mod count-'.$count_lasts.'"><span class="count_lasts">'.$count_lasts.'</span></span>' ,'manage_options', 'r_questions' , 'r_questions','dashicons-email-alt');
		add_submenu_page( 'r_questions', 'Questions', 'Questions <span class="count_report_new awaiting-mod count-'.$count_report_new.'"><span class="count_report_question_new">'.$count_report_new.'</span></span>', 'manage_options', 'r_questions', 'r_questions' );
		add_submenu_page( 'r_questions', 'Answers', 'Answers <span class="count_report_new awaiting-mod count-'.$count_report_answer_new.'"><span class="count_report_answer_new">'.$count_report_answer_new.'</span></span>', 'manage_options', 'r_answers', 'r_answers' );
	}
	$pay_ask = vpanel_options("pay_ask");
	if ($pay_ask == 1) {
		$new_payments = get_option("new_payments");
		add_menu_page('Payments', 'Payments <span class="count_report_new awaiting-mod count-'.$new_payments.'"><span>'.$new_payments.'</span></span>' ,'manage_options', 'ask_payments' , 'ask_payments','dashicons-cart');
	}
}
add_action('admin_menu', 'ask_add_admin_page_reports');
/* ask_payments */
function ask_payments () {?>
	<div class="reports-warp">
		<div class="reports-head"><i class="dashicons dashicons-cart"></i>Payments</div>
		<div class="reports-padding">
			<?php $the_currency = get_option("the_currency");
			if (isset($the_currency) && is_array($the_currency)) {
				echo "All my money<br>";
				foreach ($the_currency as $key => $currency) {
					if (isset($currency) && $currency != "") {
						$all_money = get_option("all_money_".$currency);
						echo "<br>".(isset($all_money) && $all_money != ""?$all_money:0)." ".$currency."<br>";
						//$_all_my_payment = get_user_meta(get_current_user_id(),get_current_user_id()."_all_my_payment_".$currency,true);
						//echo " all my payment ".(isset($_all_my_payment) && $_all_my_payment != ""?$_all_my_payment:0)." ".$currency."<br>";
					}
				}
				echo "<br>";
			}?>
			<div class="reports-table">
				<div class="reports-table-head">
					<div class="div-payment">Price - (coupon)</div>
					<div class="div-payment">Author</div>
					<div class="div-payment">Date</div>
					<div class="div-payment">Transaction</div>
					<div class="div-payment div-payment-last">Payer email - (sandbox)</div>
				</div><!-- End reports-table-head -->
				<?php
				$_payments = get_option("payments_option");
				$rows_per_page = get_option("posts_per_page");
				for ($payments = 1; $payments <= $_payments; $payments++) {
					$payment_one[] = get_option("payments_".$payments);
				}
				
				if (isset($payment_one) && is_array($payment_one) && !empty($payment_one)) {?>
					<div class="reports-table-items">
					<?php
					$new_payments = update_option("new_payments",0);
					$payment = array_reverse($payment_one);
					$paged = (isset($_GET["paged"])?(int)$_GET["paged"]:1);
					$current = max(1,$paged);
					$pagination_args = array(
						'base' => @esc_url(add_query_arg('paged','%#%')),
						'total' => ceil(sizeof($payment)/$rows_per_page),
						'current' => $current,
						'show_all' => false,
						'prev_text' => '&laquo; Previous',
						'next_text' => 'Next &raquo;',
					);
					
					if( !empty($wp_query->query_vars['s']) )
						$pagination_args['add_args'] = array('s'=>get_query_var('s'));
						
					$start = ($current - 1) * $rows_per_page;
					$end = $start + $rows_per_page;
					$end = (sizeof($payment) < $end) ? sizeof($payment) : $end;
					for ($i=$start;$i < $end ;++$i ) {
						$payment_result = $payment[$i];
						$date_years = (isset($payment_result["date_years"]) && $payment_result["date_years"] != ""?$payment_result["date_years"]:(isset($payment_result[0]) && $payment_result[0] != ""?$payment_result[0]:""));
						$date_hours = (isset($payment_result["date_hours"]) && $payment_result["date_hours"] != ""?$payment_result["date_hours"]:(isset($payment_result[1]) && $payment_result[1] != ""?$payment_result[1]:""));
						$item_number = (isset($payment_result["item_number"]) && $payment_result["item_number"] != ""?$payment_result["item_number"]:(isset($payment_result[2]) && $payment_result[2] != ""?$payment_result[2]:""));
						$item_price = (isset($payment_result["item_price"]) && $payment_result["item_price"] != ""?$payment_result["item_price"]:(isset($payment_result[3]) && $payment_result[3] != ""?$payment_result[3]:""));
						$item_currency = (isset($payment_result["item_currency"]) && $payment_result["item_currency"] != ""?$payment_result["item_currency"]:(isset($payment_result[4]) && $payment_result[4] != ""?$payment_result[4]:""));
						$item_transaction = (isset($payment_result["item_transaction"]) && $payment_result["item_transaction"] != ""?$payment_result["item_transaction"]:(isset($payment_result[5]) && $payment_result[5] != ""?$payment_result[5]:""));
						$payer_email = (isset($payment_result["payer_email"]) && $payment_result["payer_email"] != ""?$payment_result["payer_email"]:(isset($payment_result[6]) && $payment_result[6] != ""?$payment_result[6]:""));
						$first_name = (isset($payment_result["first_name"]) && $payment_result["first_name"] != ""?$payment_result["first_name"]:(isset($payment_result[7]) && $payment_result[7] != ""?$payment_result[7]:""));
						$last_name = (isset($payment_result["last_name"]) && $payment_result["last_name"] != ""?$payment_result["last_name"]:(isset($payment_result[8]) && $payment_result[8] != ""?$payment_result[8]:""));
						$user_id = (isset($payment_result["user_id"]) && $payment_result["user_id"] != ""?$payment_result["user_id"]:(isset($payment_result[9]) && $payment_result[9] != ""?$payment_result[9]:""));
						$sandbox = (isset($payment_result["sandbox"]) && $payment_result["sandbox"] != ""?$payment_result["sandbox"]:(isset($payment_result[10]) && $payment_result[10] != ""?$payment_result[10]:""));
						$time = (isset($payment_result["time"]) && $payment_result["time"] != ""?human_time_diff($payment_result["time"],current_time('timestamp'))." ago":(isset($payment_result[11]) && $payment_result[11] != ""?human_time_diff($payment_result[11],current_time('timestamp'))." ago":$date_years." ".$date_hours));
						$coupon = (isset($payment_result["coupon"]) && $payment_result["coupon"] != ""?$payment_result["coupon"]:(isset($payment_result[12]) && $payment_result[12] != ""?$payment_result[12]:""));
						$coupon_value = (isset($payment_result["coupon_value"]) && $payment_result["coupon_value"] != ""?$payment_result["coupon_value"]:(isset($payment_result[13]) && $payment_result[13] != ""?$payment_result[13]:""));?>
						<div class="reports-table-item">
							<div class="div-payment"><?php echo $item_price." ".$item_currency.(isset($coupon) && $coupon != ""?" - (".$coupon.")":"")?></a></div>
							<div class="div-payment">
								<a href="<?php echo vpanel_get_user_url((int)$user_id);?>"><?php echo get_the_author_meta("display_name",(int)$user_id)?></a>
							</div>
							<div class="div-payment">
								<?php echo $time?>
							</div>
							<div class="div-payment"><?php echo $item_transaction?></a></div>
							<div class="div-payment div-payment-last"><?php echo $payer_email.(isset($sandbox) && $sandbox != ""?" - (".$sandbox.")":"")?></a></div>
						</div>
					<?php } ?>
					</div><!-- End reports-table-items -->
				<?php }else {
					echo "<p>There are no payments yet</p>";
				}
				?>
			</div><!-- End reports-table -->
			<?php if (isset($payment_one) &&is_array($payment_one) && $pagination_args["total"] > 1) {?>
				<div class='reports-paged'>
					<?php echo (paginate_links($pagination_args) != ""?paginate_links($pagination_args):"")?>
				</div><!-- End reports-paged -->
				<div class="clear"></div>
			<?php }?>
		</div><!-- End reports-padding -->
	</div><!-- End reports-warp -->
	<?php
}
/* r_questions */
function r_questions () {?>
	<div class="reports-warp">
		<div class="reports-head"><i class="dashicons dashicons-flag"></i>Questions Reports</div>
		<div class="reports-padding">
			<div class="reports-table">
				<div class="reports-table-head">
					<div class="report-link">Link</div>
					<div class="report-author">Author</div>
					<div class="report-date">Date</div>
					<div class="reports-options">Options</div>
				</div><!-- End reports-table-head -->
				<?php
				$rows_per_page = get_option("posts_per_page");
				$ask_option = get_option("ask_option");
				$ask_option_array = get_option("ask_option_array");
				if (is_array($ask_option_array)) {
					foreach ($ask_option_array as $key => $value) {
						$ask_one_option[$value] = get_option("ask_option_".$value);
						$post_no_empty = get_post($ask_one_option[$value]["post_id"]);
						if (!isset($post_no_empty)) {
							unset($ask_one_option[$value]);
						}
					}
				}
				if (isset($ask_one_option) && is_array($ask_one_option) && !empty($ask_one_option)) {?>
					<div class="reports-table-items">
					<?php
					$ask_reports_option = array_reverse($ask_one_option);
					$paged = (isset($_GET["paged"])?(int)$_GET["paged"]:1);
					$current = max(1,$paged);
					$pagination_args = array(
						'base' => @esc_url(add_query_arg('paged','%#%')),
						'total' => ceil(sizeof($ask_reports_option)/$rows_per_page),
						'current' => $current,
						'show_all' => false,
						'prev_text' => '&laquo; Previous',
						'next_text' => 'Next &raquo;',
					);
					if( !empty($wp_query->query_vars['s']) )
						$pagination_args['add_args'] = array('s' => get_query_var('s'));
						
					$start = ($current - 1) * $rows_per_page;
					$end = $start + $rows_per_page;
					$end = (sizeof($ask_reports_option) < $end) ? sizeof($ask_reports_option) : $end;
					for ($i=$start;$i < $end ;++$i ) {
						$ask_reports_option_result = $ask_reports_option[$i];?>
						<div class="reports-table-item">
							<div class="report-link"><a href="<?php echo get_the_permalink($ask_reports_option_result["post_id"]);?>"><?php echo get_the_permalink($ask_reports_option_result["post_id"]);?></a></div>
							<div class="report-author">
								<?php
								if ($ask_reports_option_result["the_author"] != "") {
									if ($ask_reports_option_result["the_author"] == 1) {
										echo "Not user";
									}else {
										echo $ask_reports_option_result["the_author"];
									}
								}else {
									?><a href="<?php echo vpanel_get_user_url((int)$ask_reports_option_result["user_id"]);?>"><?php echo get_the_author_meta("display_name",(int)$ask_reports_option_result["user_id"])?></a><?php
								}
								?>
							</div>
							<div class="report-date"><?php echo human_time_diff($ask_reports_option_result["the_date"],current_time('timestamp'))." ago"?></div>
							<div class="reports-options">
								<a href="#" class="reports-view dashicons dashicons-search" attr="<?php echo $ask_reports_option_result["item_id_option"]?>"></a>
								<a href="#" attr="<?php echo $ask_reports_option_result["item_id_option"]?>" class="reports-delete dashicons dashicons-no"></a>
								<?php if ($ask_reports_option_result["report_new"] == 1) {?>
									<div title="New reports" class="reports-new dashicons dashicons-email-alt"></div>
								<?php }?>
							</div>
							<div id="reports-<?php echo $ask_reports_option_result["item_id_option"]?>" class="reports-pop">
								<div class="reports-pop-no-scroll">
									<div class="reports-pop-inner">
										<a href="#" class="reports-close dashicons dashicons-no"></a>
										<div class="clear"></div>
										<div class="reports-pop-warp">
											<div>
												<div>Message</div>
												<div><?php echo nl2br($ask_reports_option_result["value"])?></div>
											</div>
										</div>
										<div class="clear"></div>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
					</div><!-- End reports-table-items -->
				<?php }else {
					echo "<p>There are no reports yet</p>";
				}
				?>
			</div><!-- End reports-table -->
			<?php if (isset($pagination_args["total"]) && $pagination_args["total"] > 1) {?>
				<div class='reports-paged'>
					<?php echo (paginate_links($pagination_args) != ""?paginate_links($pagination_args):"")?>
				</div><!-- End reports-paged -->
				<div class="clear"></div>
			<?php }?>
		</div><!-- End reports-padding -->
	</div><!-- End reports-warp -->
	<?php
}
/* r_answers */
function r_answers () {
	?>
	<div class="reports-warp">
		<div class="reports-head"><i class="dashicons dashicons-flag"></i>Answers Reports</div>
		<div class="reports-padding">
			<div class="reports-table">
				<div class="reports-table-head">
					<div class="report-link">Link</div>
					<div class="report-author">Author</div>
					<div class="report-date">Date</div>
					<div class="reports-options">Options</div>
				</div><!-- End reports-table-head -->
				<?php
				$rows_per_page = get_option("posts_per_page");
				$ask_option_answer = get_option("ask_option_answer");
				$ask_option_answer_array = get_option("ask_option_answer_array");
				if (is_array($ask_option_answer_array)) {
					foreach ($ask_option_answer_array as $key => $value) {
						$ask_one_option[$value] = get_option("ask_option_answer_".$value);
						$comment_no_empty = get_comment($ask_one_option[$value]["comment_id"]);
						if (!isset($comment_no_empty)) {
							unset($ask_one_option[$value]);
						}
					}
				}
				if (isset($ask_one_option) && is_array($ask_one_option) && !empty($ask_one_option)) {?>
					<div class="reports-table-items">
					<?php
					$ask_reports_option = array_reverse($ask_one_option);
					$paged = (isset($_GET["paged"])?(int)$_GET["paged"]:1);
					$current = max(1,$paged);
					$pagination_args = array(
						'base' => @esc_url(add_query_arg('paged','%#%')),
						'total' => ceil(sizeof($ask_reports_option)/$rows_per_page),
						'current' => $current,
						'show_all' => false,
						'prev_text' => '&laquo; Previous',
						'next_text' => 'Next &raquo;',
					);
					if( !empty($wp_query->query_vars['s']) )
						$pagination_args['add_args'] = array('s' => get_query_var('s'));
						
					$start = ($current - 1) * $rows_per_page;
					$end = $start + $rows_per_page;
					$end = (sizeof($ask_reports_option) < $end) ? sizeof($ask_reports_option) : $end;
					for ($i=$start;$i < $end ;++$i ) {
						$ask_reports_option_result = $ask_reports_option[$i];?>
						<div class="reports-table-item">
							<div class="report-link"><a href="<?php echo get_the_permalink($ask_reports_option_result["post_id"]);?>#comment-<?php echo $ask_reports_option_result["comment_id"]?>"><?php echo get_the_permalink($ask_reports_option_result["post_id"]);?>#comment-<?php echo $ask_reports_option_result["comment_id"]?></a></div>
							<div class="report-author">
								<?php
								if ($ask_reports_option_result["the_author"] != "") {
									if ($ask_reports_option_result["the_author"] == 1) {
										echo "Not user";
									}else {
										echo $ask_reports_option_result["the_author"];
									}
								}else {
									?><a href="<?php echo vpanel_get_user_url((int)$ask_reports_option_result["user_id"]);?>"><?php echo get_the_author_meta("display_name",(int)$ask_reports_option_result["user_id"])?></a><?php
								}
								?>
							</div>
							<div class="report-date"><?php echo human_time_diff($ask_reports_option_result["the_date"],current_time('timestamp'))." ago"?></div>
							<div class="reports-options">
								<a href="#" class="reports-view reports-answers dashicons dashicons-search" attr="<?php echo $ask_reports_option_result["item_id_option"]?>"></a>
								<a href="#" attr="<?php echo $ask_reports_option_result["item_id_option"]?>" class="reports-delete reports-answers dashicons dashicons-no"></a>
								<?php if ($ask_reports_option_result["report_new"] == 1) {?>
									<div title="New reports" class="reports-new dashicons dashicons-email-alt"></div>
								<?php }?>
							</div>
							<div id="reports-<?php echo $ask_reports_option_result["item_id_option"]?>" class="reports-pop">
								<div class="reports-pop-no-scroll">
									<div class="reports-pop-inner">
										<a href="#" class="reports-close dashicons dashicons-no"></a>
										<div class="clear"></div>
										<div class="reports-pop-warp">
											<div>
												<div>Message</div>
												<div><?php echo nl2br($ask_reports_option_result["value"])?></div>
											</div>
										</div>
										<div class="clear"></div>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
					</div><!-- End reports-table-items -->
				<?php }else {
					echo "<p>There are no reports yet</p>";
				}
				?>
			</div><!-- End reports-table -->
			<?php if (isset($pagination_args["total"]) && $pagination_args["total"] > 1) {?>
				<div class='reports-paged'>
					<?php echo (paginate_links($pagination_args) != ""?paginate_links($pagination_args):"")?>
				</div><!-- End reports-paged -->
				<div class="clear"></div>
			<?php }?>
		</div><!-- End reports-padding -->
	</div><!-- End reports-warp -->
	<?php
}
/* vpanel_user_table */
function vpanel_user_table( $column ) {
	$user_meta_admin = vpanel_options("user_meta_admin");
	if (isset ($user_meta_admin) && is_array($user_meta_admin)) {
		$column['question']   = __('Questions','vbegy');
		if (isset($user_meta_admin["phone"]) && $user_meta_admin["phone"] == 1) {
			$column['phone']   = __('Phone','vbegy');
		}
		if (isset($user_meta_admin["country"]) && $user_meta_admin["country"] == 1) {
			$column['country'] = __('Country','vbegy');
		}
		if (isset($user_meta_admin["age"]) && $user_meta_admin["age"] == 1) {
			$column['age']     = __('Age','vbegy');
		}
	}
	return $column;
}
add_filter( 'manage_users_columns', 'vpanel_user_table' );
function vpanel_user_table_row( $val, $column_name, $user_id ) {
	$user = get_userdata( $user_id );
	switch ($column_name) {
		case 'question' :
			$count_user_questions = count_user_posts_by_type($user_id,"question");
			return ($count_user_questions > 0?'<a href="'.admin_url('edit.php?post_type=question&author='.$user_id).'">':'').$count_user_questions.($count_user_questions > 0?'</a>':'');
			break;
		case 'phone' :
			return get_the_author_meta( 'phone', $user_id );
			break;
		case 'country' :
			$get_countries = vpanel_get_countries();
			$country = get_the_author_meta( 'country', $user_id );
			if ($country && isset($get_countries[$country])) {
				return $get_countries[$country];
			}else {
				return '';
			}
			break;
		case 'age' :
			return get_the_author_meta( 'age', $user_id );
			break;
		default:
	}
	return $return;
}
add_filter( 'manage_users_custom_column', 'vpanel_user_table_row', 10, 3 );
/* askme_comment_columns */
function askme_comment_columns ($columns) {
	return array_merge( $columns, array(
		'answers' => __('Answer/Comment','vbegy'),
		'best_answer' => __('Best answer?','vbegy')
	));
}
add_filter('manage_edit-comments_columns','askme_comment_columns');
function askme_comment_column ($column,$comment_ID) {
	switch ( $column ) {
		case 'answers':
			$comment_type = get_comment_meta($comment_ID,"comment_type",true);
			if (isset($comment_type) && $comment_type == "question") {
				echo __('Answer','vbegy');
			}else {
				echo __('Comment','vbegy');
			}
		break;
		case 'best_answer':
			$best_answer_comment = get_comment_meta($comment_ID,"best_answer_comment",true);
			if (isset($best_answer_comment) && $best_answer_comment == "best_answer_comment") {
				echo __('Best answer','vbegy');
			}else {
				echo '<span aria-hidden="true">—</span><span class="screen-reader-text">'.__("Not best answer","vbegy").'</span>';
			}
		break;
	}
}
add_filter('manage_comments_custom_column','askme_comment_column',10,2);