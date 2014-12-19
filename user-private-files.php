<?php
/*
Plugin Name: User Private Files 1.1
Description: Plugin to manage private files for users. You can upload files for your users to access, files are only viewable/downloadable for the designated users.
Author: Hai Bui - FLDtrace team
Author URI: http://www.fldtrace.com
License: GPL
Version: 1.1

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

//*********** install/uninstall actions ********************//
register_activation_hook(__FILE__,'upf_install');
register_deactivation_hook(__FILE__, 'upf_uninstall');
function upf_install(){
    upf_uninstall();//force to uninstall option
	add_option('upf_email_subject','New File Upload');
	add_option('upf_email_message',"Hello %user_login%,\nYou have a new file upload. The file name is %filename%, you can download it here %download_url%");
}

function upf_uninstall(){
	delete_option('upf_email_subject');
	delete_option('upf_email_message');
}
//*********** end of install/uninstall actions ********************//

function upf_init() {
     load_plugin_textdomain('user-private-files');
}
add_action('init', 'upf_init');

add_action('admin_menu', 'upf_menu');

function upf_menu() {
    add_submenu_page( 'edit.php?post_type=userfile', 'User Private Files', 'Settings', 'manage_options', 'upf_options', 'upf_options');
}

function upf_options() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.', 'user-private-files') );
	}

	if (!empty($_POST['update'])) {

		if($_POST['upf_email_subject'] ) { 
			update_option('upf_email_subject',$_POST['upf_email_subject'] );
		}
		
		if($_POST['upf_email_message'] ) { 
			update_option('upf_email_message',esc_attr($_POST['upf_email_message']) );
		}
		?>
		<div class="updated settings-error" id="setting-error-settings_updated"><p><strong><?php _e('Settings Saved', 'user-private-files');?>.</strong></p></div>
		<?php
	}

	$upf_email_subject = get_option('upf_email_subject');
	$upf_email_message = get_option('upf_email_message');
	?>
	<div class="wrap">
		<h2><?php _e('User Private Files Settings', 'user-private-files');?></h2>
		<form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
			<h3><?php _e('Notification', 'user-private-files');?></h3>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="upf_email_subject"><?php _e('Email Subject:', 'user-private-files');?></label></th>
						<td><input type="text" class="regular-text" name="upf_email_subject" id="upf_email_subject" value="<?php echo $upf_email_subject; ?>"></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="upf_email_subject"><?php _e('Email Message:', 'user-private-files');?></label></th>
						<td>
							<textarea name="upf_email_message" class="regular-text" rows="5" cols="50"><?php echo $upf_email_message; ?></textarea>
							<p class="description"><?php _e('Available Variables: ', 'user-private-files');?> <br/><strong>%blogname%, %siteurl%, %user_login%, %filename%, %download_url%, %category%</strong></p>
						</td>
					</tr>
				</tbody>
			</table>
			<input type="hidden" name="update" value="update">
			<p class="submit"><input type="submit" value="<?php _e('Save Changes', 'user-private-files');?>" class="button-primary" id="submit" name="submit"></p>
		</form>
	</div>
	<?php
}

add_action( 'init', 'upf_register_cpt_userfile' );

function upf_register_cpt_userfile() {

    $labels = array( 
        'name' => _x( 'User Files', 'userfile' ),
        'singular_name' => _x( 'User File', 'userfile' ),
        'add_new' => _x( 'Add New', 'userfile' ),
        'add_new_item' => _x( 'Add New User File', 'userfile' ),
        'edit_item' => _x( 'Edit User File', 'userfile' ),
        'new_item' => _x( 'New User File', 'userfile' ),
        'view_item' => _x( 'View User File', 'userfile' ),
        'search_items' => _x( 'Search User Files', 'userfile' ),
        'not_found' => _x( 'No user files found', 'userfile' ),
        'not_found_in_trash' => _x( 'No user files found in Trash', 'userfile' ),
        'parent_item_colon' => _x( 'Parent User File:', 'userfile' ),
        'menu_name' => _x( 'User Files', 'userfile' ),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,
        'supports' => array( 'title', 'author', 'editor' ),
        'taxonomies' => array( 'file_categories' ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => false,
        'publicly_queryable' => true,
        'exclude_from_search' => true,
        'has_archive' => false,
        'query_var' => true,
        'can_export' => false,
        'rewrite' => false,
        'capabilities' => array(
            'edit_post' => 'update_core',
            'edit_posts' => 'update_core',
            'edit_others_posts' => 'update_core',
            'publish_posts' => 'update_core',
            'read_post' => 'update_core',
            'read_private_posts' => 'update_core',
            'delete_post' => 'update_core'
        )
    );

    register_post_type( 'userfile', $args );
}

add_action( 'init', 'upf_register_taxonomy_file_categories' );

function upf_register_taxonomy_file_categories() {

    $labels = array( 
        'name' => _x( 'Categories', 'file_categories' ),
        'singular_name' => _x( 'Category', 'file_categories' ),
        'search_items' => _x( 'Search Categories', 'file_categories' ),
        'popular_items' => _x( 'Popular Categories', 'file_categories' ),
        'all_items' => _x( 'All Categories', 'file_categories' ),
        'parent_item' => _x( 'Parent Category', 'file_categories' ),
        'parent_item_colon' => _x( 'Parent Category:', 'file_categories' ),
        'edit_item' => _x( 'Edit Category', 'file_categories' ),
        'update_item' => _x( 'Update Category', 'file_categories' ),
        'add_new_item' => _x( 'Add New Category', 'file_categories' ),
        'new_item_name' => _x( 'New Category', 'file_categories' ),
        'separate_items_with_commas' => _x( 'Separate categories with commas', 'file_categories' ),
        'add_or_remove_items' => _x( 'Add or remove categories', 'file_categories' ),
        'choose_from_most_used' => _x( 'Choose from the most used categories', 'file_categories' ),
        'menu_name' => _x( 'Categories', 'file_categories' ),
    );

    $args = array( 
        'labels' => $labels,
        'public' => true,
        'show_in_nav_menus' => false,
        'show_ui' => true,
        'show_tagcloud' => false,
        'hierarchical' => true,
        'rewrite' => false,
        'query_var' => true
    );

    register_taxonomy( 'file_categories', array('userfile'), $args );
}


// Register the column
function upf_user_column_register( $columns ) {
	$columns['user'] = __( 'User', 'user-private-files' );
	return $columns;
}
add_filter( 'manage_edit-userfile_columns', 'upf_user_column_register' );

// Display the column content
function upf_user_column_display( $column_name, $post_id ) {
	if ( 'user' != $column_name )
		return;
 
	$username = get_post_meta($post_id, 'upf_user', true);
	echo $username;
}
add_action( 'manage_userfile_posts_custom_column', 'upf_user_column_display', 10, 2 );

// Register the column as sortable
function upf_user_column_register_sortable( $columns ) {
	$columns['user'] = 'user';
 
	return $columns;
}
add_filter( 'manage_edit-userfile_sortable_columns', 'upf_user_column_register_sortable' );

function upf_user_column_orderby( $vars ) {
	if ( isset( $vars['orderby'] ) && 'user' == $vars['orderby'] ) {
		$vars = array_merge( $vars, array(
			'meta_key' => 'upf_user',
			'orderby' => 'meta_value'
		) );
	}
 
	return $vars;
}
add_filter( 'request', 'upf_user_column_orderby' );




add_filter('get_sample_permalink_html', 'upf_hide_sample_permalink', '',4);

function upf_hide_sample_permalink($return, $id, $new_title, $new_slug){
	global $post;
	if ($post->post_type == 'userfile') {
		$return = '';
	}
	return $return;
}

function upf_get_user_dir($user_id) { 
	if (empty($user_id)) return false;

	$dir = get_user_meta($user_id, 'upf_dir', true);
	if (empty($dir)) {
		$dir = uniqid($user_id.'_');
		add_user_meta( $user_id, 'upf_dir', $dir );
	}
	return $dir;
}


add_action( 'post_edit_form_tag' , 'upf_post_edit_form_tag' );
function upf_post_edit_form_tag() {
	global $post;

    // if invalid $post object or post type is not 'userfile', return
    if(!$post || get_post_type($post->ID) != 'userfile') return;
       	
	echo ' enctype="multipart/form-data" autocomplete="off"';
}

add_action('admin_menu', 'upf_meta_box');
function upf_meta_box() {
	add_meta_box('userfile', __('User File', 'user-private-files'), 'upf_meta_fields', 'userfile', 'normal', 'high');
}

	

function upf_meta_fields() { 
	global $post;
    wp_nonce_field(plugin_basename(__FILE__), 'wp_upf_nonce');

	$upf_file = get_post_meta($post->ID, 'upf_file', true);
	if (!empty($upf_file)) { ?>
		<p><?php _e('Current file:', 'user-private-files');?> <a href="<?php echo $upf_file['url'];?>" target="_blank"><?php echo basename($upf_file['file']);?></a></p>
		<?php
	}
	?>
	<p class="label"><label for="upf_file"><?php _e('Upload a PDF file here', 'user-private-files');?></label></p>	
	<p><input type="file" name="upf_file" id="upf_file" /></p>
	<p class="label"><label for="upf_user"><?php _e('Select a user', 'user-private-files');?></label></p>	
	<select name="upf_user" id="upf_user">
		<?php
		$users = get_users();
		$upf_user = get_post_meta($post->ID, 'upf_user', true);
		foreach ($users as $user) { ?>
			<option value="<?php echo $user->ID;?>" <?php if ($upf_user == $user->user_login) echo 'selected="selected"';?>><?php echo $user->user_login;?></option>
			<?php
		}
		?>
	</select>
	<p class="label"><input type="checkbox" name="upf_notify" value="1"> <label for="upf_notify"><?php _e('Notify User', 'user-private-files');?></label></p>	
	<?php 
}

add_action('save_post', 'upf_save_post');
function upf_save_post($post_id, $post = null) {
	global $post;

	/* --- security verification --- */  
    if(!wp_verify_nonce($_POST['wp_upf_nonce'], plugin_basename(__FILE__)))
		return $post_id;  

	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		return $post_id;  

    // if invalid $post object or post type is not 'userfile', return
    if(!$post || get_post_type($post->ID) != 'userfile') return;

	$user_info = get_userdata($_POST['upf_user']);
	add_post_meta($post_id, 'upf_user', $user_info->user_login);
	update_post_meta($post_id, 'upf_user', $user_info->user_login);

	// Make sure the file array isn't empty
	if(!empty($_FILES['upf_file']['name'])) {
		// Setup the array of supported file types. In this case, it's just PDF.
		$supported_types = array('application/pdf');

		// Get the file type of the upload
		$arr_file_type = wp_check_filetype(basename($_FILES['upf_file']['name']));

		$uploaded_type = $arr_file_type['type'];
		// Check if the type is supported. If not, throw an error.
		if(in_array($uploaded_type, $supported_types)) {
			$upf_file = get_post_meta($post_id, 'upf_file', true);
			if ($upf_file) {
				$upf_file_path = WP_CONTENT_DIR.'/userfiles/'.$upf_file['file'];
				if (file_exists($upf_file_path)) unlink($upf_file_path);
			}

			// Use the WordPress API to upload the file
			$upload = wp_handle_upload( $_FILES['upf_file'], array( 'test_form' => false ) );

			if(isset($upload['error']) && $upload['error'] != 0) {
				wp_die(__('There was an error uploading your file. The error is: ' . $upload['error'], 'user-private-files'));
			} else {
				// Update custom field
				$upload['file'] = substr($upload['file'],stripos($upload['file'],'wp-content/userfiles/')+21);
				add_post_meta($post_id, 'upf_file', $upload);
				update_post_meta($post_id, 'upf_file', $upload);
			} // end if/else
		} else {
			wp_die(__("The file type that you've uploaded is not a PDF.", 'user-private-files'));
		} // end if/else
	} // end if


	if ($_POST['upf_notify'] == '1') {
		$upf_file = get_post_meta($post_id, 'upf_file', true);

		$email_subject = get_option('upf_email_subject');
		$email_msg = get_option('upf_email_message');

		$email_msg = str_replace('%blogname%', get_bloginfo('name'), $email_msg);
		$email_msg = str_replace('%siteurl%', get_bloginfo('url'), $email_msg);
		$email_msg = str_replace('%user_login%', $user_info->user_login, $email_msg);
		$email_msg = str_replace('%filename%', basename($upf_file['file']), $email_msg);
		$email_msg = str_replace('%download_url%', get_bloginfo('url').'/?upf=dl&id='.$post_id, $email_msg);

		$cats = wp_get_post_terms($post_id, 'file_categories', array("fields" => "names"));
		$email_msg = str_replace('%category%', implode(", ", $cats), $email_msg); 

		$headers[] ='From: "'.htmlspecialchars_decode(get_bloginfo('name'), ENT_QUOTES).'" <'.get_option('admin_email').'>';
			
		wp_mail($user_info->user_email, $email_subject, $email_msg, $headers);
	}
}

add_filter( 'upload_dir', 'upf_custom_upload_dir' );
function upf_custom_upload_dir( $default_dir ) {
	if ( ! isset( $_POST['post_ID'] ) || $_POST['post_ID'] < 0 )
		return $default_dir;

	if ( ! isset( $_POST['upf_user'] ) )
		return $default_dir;

	if ( $_POST['post_type'] != 'userfile' )
		return $default_dir;

	$dir = WP_CONTENT_DIR . '/userfiles';
	$url = WP_CONTENT_URL . '/userfiles';

	$bdir = $dir;
	$burl = $url;

	$subdir = '/'.upf_get_user_dir($_POST['upf_user']);
	
	$dir .= $subdir;
	$url .= $subdir;

	$custom_dir = array( 
		'path'    => $dir,
		'url'     => $url, 
		'subdir'  => $subdir, 
		'basedir' => $bdir, 
		'baseurl' => $burl,
		'error'   => false, 
	);

	return $custom_dir;
}

add_action('init', 'upf_get_download');


function upf_get_download() {
	if (isset($_GET['upf']) && isset($_GET['id'])) {
		if (is_user_logged_in()) {
			global $current_user;
			get_currentuserinfo();

			// if the file was not assigned to the current user, return 
			if (get_post_meta($_GET['id'], 'upf_user', true) != $current_user->user_login) return;

			$upf_file = get_post_meta($_GET['id'], 'upf_file', true);
			$upf_file_path = WP_CONTENT_DIR.'/userfiles/'.$upf_file['file'];
			$upf_file_name = substr($upf_file['file'], stripos($upf_file['file'], '/')+1);
			set_time_limit(0);

			$action = $_GET['upf']=='vw'?'view':'download';
			output_file($upf_file_path, $upf_file_name, $upf_file['type'], $action);
		}
		else {
			wp_redirect(wp_login_url($_SERVER['REQUEST_URI']));
			exit;
		}
	}
}

/*DOWNLOAD FUNCTION */
function output_file($file, $name, $mime_type='', $action = 'download') {
	if(!is_readable($file)) {
		//die('File not found or inaccessible!<br />'.$file.'<br /> '.$name);
		return;
	}
	$size = filesize($file);
	$name = rawurldecode($name);

	$known_mime_types=array(
		"pdf" => "application/pdf",
		"txt" => "text/plain",
		"html" => "text/html",
		"htm" => "text/html",
		"exe" => "application/octet-stream",
		"zip" => "application/zip",
		"doc" => "application/msword",
		"xls" => "application/vnd.ms-excel",
		"ppt" => "application/vnd.ms-powerpoint",
		"gif" => "image/gif",
		"png" => "image/png",
		"jpeg"=> "image/jpg",
		"jpg" =>  "image/jpg",
		"php" => "text/plain"
	);

	if($mime_type==''){
		$file_extension = strtolower(substr(strrchr($file,"."),1));
		if(array_key_exists($file_extension, $known_mime_types)){
			$mime_type=$known_mime_types[$file_extension];
		} else {
			$mime_type="application/force-download";
		};
	};

	@ob_end_clean(); //turn off output buffering to decrease cpu usage

	// required for IE, otherwise Content-Disposition may be ignored
	if(ini_get('zlib.output_compression'))
		ini_set('zlib.output_compression', 'Off');

	header('Content-Type: ' . $mime_type);
	if ($action == 'download') header('Content-Disposition: attachment; filename="'.$name.'"');
	else header('Content-Disposition: inline; filename="'.$name.'"');
	header("Content-Transfer-Encoding: binary");
	header('Accept-Ranges: bytes');

	/* The three lines below basically make the	download non-cacheable */
	header("Cache-control: private");
	header('Pragma: private');
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

	// multipart-download and download resuming support
	if(isset($_SERVER['HTTP_RANGE']))
	{
		list($a, $range) = explode("=",$_SERVER['HTTP_RANGE'],2);
		list($range) = explode(",",$range,2);
		list($range, $range_end) = explode("-", $range);
		$range=intval($range);

		if(!$range_end) {
			$range_end=$size-1;
		} else {
			$range_end=intval($range_end);
		}

		$new_length = $range_end-$range+1;
		header("HTTP/1.1 206 Partial Content");
		header("Content-Length: $new_length");
		header("Content-Range: bytes $range-$range_end/$size");
	} else {
		$new_length=$size;
		header("Content-Length: ".$size);
	}

	/* output the file itself */
	$chunksize = 1*(1024*1024); //you may want to change this
	$bytes_send = 0;
	if ($file = fopen($file, 'r'))
	{
		if(isset($_SERVER['HTTP_RANGE']))
			fseek($file, $range);

		while(!feof($file) && (!connection_aborted()) && ($bytes_send<$new_length)) {
			$buffer = fread($file, $chunksize);
			print($buffer); //echo($buffer); // is also possible
			flush();
			$bytes_send += strlen($buffer);
		}
		fclose($file);
	} 
	else die('Error - can not open file.');

	die();
}   

function upf_list_user_files() {
	if (!is_user_logged_in()) return;

	global $current_user;
    get_currentuserinfo();

	
	$current_url = get_permalink();
	if (strpos($current_url,'?') !== false) $current_url .= '&';
	else $current_url .= '?';

	ob_start();
	?>
	<div class="filter clearfix">
		<form action="<?php the_permalink();?>" method="post" autocomplete="off">
					
		<select name="upf_year">
						
			<option value=""><?php _e('Show all years', 'user-private-files');?></option>
			<?php
			global $wpdb;
			$years = $wpdb->get_col("SELECT DISTINCT YEAR(post_date) 
					FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta
					WHERE wposts.ID = wpostmeta.post_id 
					AND wposts.post_type = 'userfile' 
					AND wpostmeta.meta_key = 'upf_user' 
					AND wpostmeta.meta_value = '$current_user->user_login'
					ORDER BY post_date DESC");
			foreach($years as $year) { ?>
				<option <?php if (isset($_POST['upf_year']) && $_POST['upf_year']==$year) echo 'selected="selected"';?>><?php echo $year; ?></option>
				<?php 
			}
			?>
		</select>
		<select name="upf_cat">
			<option value=""><?php _e('Show all categories', 'user-private-files');?></option>
			<?php
			$cats = get_terms('file_categories');
			foreach($cats as $cat) { ?>
				<option value="<?php echo $cat->slug;?>" <?php if (isset($_POST['upf_cat']) && $_POST['upf_cat']==$cat->slug) echo 'selected="selected"';?>><?php echo $cat->name;?></option>
				<?php 
			}
			?>
		</select>
	
		<input type="submit" value="<?php _e('Filter', 'user-private-files');?>" />
		</form>
	</div>
	<div class="upf_filelist">
	<?php
	$args = array(
		'post_type' => 'userfile',
		'meta_key' => 'upf_user', 
		'meta_value' => $current_user->user_login,
		'orderby' => 'date',
		'order' => DESC
	);

	if (!empty($_POST['upf_year'])) $args['year'] = $_POST['upf_year'];
	if (!empty($_POST['upf_cat'])) $args['file_categories'] = $_POST['upf_cat'];
	
	$the_query = new WP_Query( $args );


	$html = '';

	$current_year = '';

	// The Loop
	if ($the_query->have_posts()) : 
		while ( $the_query->have_posts() ) : $the_query->the_post(); 
			$year = get_the_date('Y');
			if ($year != $current_year) {
				echo '<h2>'.$year.'</h2>';
				$current_year = $year;
			}
			?>
			<div class="report-wrap clearfix">
				<span class="report-name"><a href="<?php the_permalink();?>"><?php the_title();?></a></span>
				<div class="right">
					<a href="?upf=vw&id=<?php echo get_the_ID();?>" class="view-print" target="_blank"><?php _e('View and Print', 'user-private-files');?></a> |
					<a href="?upf=dl&id=<?php echo get_the_ID();?>" class="download" target="_blank"><?php _e('Download', 'user-private-files');?></a>
				</div>
			</div>
			<?php
		endwhile; 
	endif;

	// Reset Post Data
	wp_reset_postdata();

	$html .= ob_get_clean();
	$html .= '</div>';
	return $html;
}

add_shortcode('userfiles', 'upf_list_user_files');


add_action('wp_head', 'upf_userfile_cpt_noindex');
function upf_userfile_cpt_noindex() {
	if ( get_post_type() == 'userfile' ) { ?>
		<meta name="robots" content="noindex,nofollow" />
		<?php 
	}
}


add_action( 'template_redirect', 'upf_userfile_cpt_template' );

function upf_userfile_cpt_template() {
    global $wp, $wp_query;

    if ( isset( $wp->query_vars['post_type'] ) && $wp->query_vars['post_type'] == 'userfile' ) {
        if ( have_posts() ) {
            add_filter( 'the_content', 'upf_userfile_cpt_template_filter' );
        }
        else {
            $wp_query->is_404 = true;
        }
    }
}

function upf_userfile_cpt_template_filter( $content ) {

    global $wp_query;
    $post_id = $wp_query->post->ID;

	$output = "You are not authorized to access this page.";
	if (is_user_logged_in()) {
		global $current_user;
		get_currentuserinfo();

		// if the file was not assigned to the current user, return 
		if (get_post_meta($post_id, 'upf_user', true) == $current_user->user_login) {
    		$output = $content;
			$output .= '<p><a href="?upf=vw&id='.$post_id.'" class="view-print" target="_blank">' . __('View and Print', 'user-private-files') . '</a><br/>
						<a href="?upf=dl&id='.$post_id.'" class="download" target="_blank">' . __('Download', 'user-private-files') . '</a></p>';
		}
	}


    return $output;
}
