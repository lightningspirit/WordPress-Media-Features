<?php

/*
Plugin Name: Media Features
Plugin URI: http://wordpress.org/extend/plugins/media-features
Version: 0.1
Description: Adds a bunch of new features to Media Files like category organization, file type filter/browser, uploaded image resize, crop and JPEG quality controllers.
Author: lightningspirit
Author URI: http://profiles.wordpress.org/lightningspirit
Text Domain: media-features
Domain Path: /languages/
Tags: plugin, media, upload, feature, features, category, media category, media organization, file types, media types, files organization, files category, media resize, upload resize, upload crop, jpeg quality, jpeg
License: GPLv2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


/*
 * @package Media Features
 * @author Vitor Carvalho
 * @copyright lightningspirit 2011-2012
 * This code is released under the GPL licence version 2 or later
 * http://www.gnu.org/licenses/gpl.txt
 */



if ( ! class_exists ( 'WP_Media_Features' ) ) :
/**
 * WP_Media_Features
 * 
 * Central class to media features.
 * 
 * Just use the custom filter hooks provided. If you need a specific hook
 * not listed here, please patch the file and send me a diff file to
 * lightningspirit [at] gmail [dot] com.
 * I will evaluate it and and tag a new release asap.
 * 
 * 
 * Filter hooks:
 * --------------
 * a) media_category_args( array $args ) 
 *    change $args of register_taxonomy
 * 
 * b) media_category_html( string $html, array $form_object, object $post ) 
 *    HTML returned to display category postbox 
 * 
 * Use add_filter() function to hook and change those filters.
 * 
 * 
 * @package WordPress
 * @subpackage Media Feature
 * @since 0.1
 */
class WP_Media_Features {
	
	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.1
	 * 
	 * @return void
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( 'WP_Media_Features', 'init' ) );
		
	}
	
	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.1
	 * 
	 * @return void
	 */
	public static function init() {
		// adds gettext support
		load_plugin_textdomain( 'media-features', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		
		// if new upgrade
		if ( version_compare( get_option( 'media_features_plugin_version' ), '0.1' ) )
			add_action( 'admin_init', array( 'WP_Media_Features', 'do_upgrade' ) );
		
		// if first install, ask for configuration
		if ( false === get_option( 'media_features_jpeg_quality' ) )
			add_action( 'admin_notices', array( 'WP_Media_Features', 'configure_settings_notice' ) );
			
		
		add_filter( 'plugin_action_links', array( 'WP_Media_Features', 'plugin_action_links' ), 10, 2 );
		
		add_action( 'add_attachment', array( 'WP_Media_Features', 'catch_upload' ), 10, 1 );
		add_filter( 'jpeg_quality', array( 'WP_Media_Features', 'set_jpeg_quality' ), 10, 2 );
		add_filter( 'upload_mimes', array( 'WP_Media_Features', 'upload_mimes' ) );
		add_filter( 'post_mime_types', array( 'WP_Media_Features', 'post_mime_types' ) );
		
		add_filter( 'media_upload_mime_type_links', array( 'WP_Media_Features', 'media_upload_mime_type_links' ), 10, 1 );
		add_action( 'restrict_manage_posts', array( 'WP_Media_Features', 'media_upload_filter_dropdowns' ) );
		
		add_action( 'admin_init', array( 'WP_Media_Features', 'add_attachments_taxonomies' ) );
		
		add_action( 'manage_media_columns', array( 'WP_Media_Features', 'manage_media_columns' ) );
		add_action( 'manage_media_custom_column', array( 'WP_Media_Features', 'manage_media_custom_column' ), 10, 2 );
		
		add_action( 'manage_edit-media_category_columns', array( 'WP_Media_Features', 'manage_media_category_columns' ) );
		add_action( 'manage_media_category_custom_column', array( 'WP_Media_Features', 'manage_media_category_custom_column' ), 10, 3 );
		add_action( 'media_category_row_actions', array( 'WP_Media_Features', 'filter_row_actions' ) );
		
		add_action( 'admin_menu', array( 'WP_Media_Features', 'add_taxonomies_submenus' ) );
		add_filter( 'parent_file', array( 'WP_Media_Features', 'media_taxonomies_parent_file' ) );
		add_filter( 'attachment_fields_to_edit', array( 'WP_Media_Features', 'attachment_fields_to_edit' ), 10, 2 );
		
		add_action( 'load-media.php', array( 'WP_Media_Features', 'load_media_head' ) );
		add_action( 'load-media-new.php', array( 'WP_Media_Features', 'load_media_head' ) );
		add_action( 'admin_init', array( 'WP_Media_Features', 'add_options' ) );
		
	}
	
	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.1
	 * 
	 * @return void
	 */
	public static function do_upgrade() {
		update_option( 'media_features_plugin_version', '0.1' );
		
	}
	
	/** 
	 * Configure settings admin notice
	 * 
	 * @since 0.1
	 * 
	 * @return void
	 */
	public static function configure_settings_notice() {
		echo '<div class="updated"><p>';
		printf( __( 'You have to configure the new settings for media features plugin. <a href="%s">Configure now</a>.', 'media-features' ), 'options-media.php' );
		echo '</p></div>';
		
	}
	
	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.1
	 * 
	 * @param string $links
	 * @param string $file
	 * @return void
	 */
	public static function plugin_action_links( $links, $file ) {
		if ( $file != plugin_basename( __FILE__ ) )
			return $links;

		$settings_link = '<a href="options-media.php">' . __( 'Configure', 'media-features' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;

	}
	
	/** 
	 * Catch the file upload and parse it
	 * 
	 * @since 0.1
	 * 
	 * @param int $post_id
	 * @return string The filepath
	 */
	public static function catch_upload( $post_id ) {
		if ( ! wp_attachment_is_image( $post_id ) )
			return;
		
		// Get default options for Large Size Media and options
		$max_width 	  = (int)  get_option( 'large_size_w', 1024 );
		$max_height	  = (int)  get_option( 'large_size_h', 1024 );
		$crop 		  = (bool) get_option( 'media_features_crop', false );
		$resizes	  = (bool) get_option( 'media_features_resize', false );
		
		// If option is not enabled return
		if ( ! $resizes )
			return;
		
		// Let's see if these sizes are reasonable
		if ( $max_width < 0 || $max_height < 0 )
			return;
		
		// Get file and image GD resource
		$file = get_attached_file( $post_id );
		$image = wp_load_image( $file );
		if ( !is_resource( $image ) )
			return new WP_Error( 'error_loading_image', $image, $file );
		
		// Get real dimensions from file image
		list( $orig_width, $orig_height, $orig_type ) = @getimagesize( $file );
		
		// if the image needs to be cropped, resize it
		if ( $max_width >= $orig_width && $max_height >= $orig_height )
			return;
			
		//var_dump( $file, $orig_width, $orig_height, $orig_type, $max_width, $max_height );
		
		$dims = image_resize_dimensions( $orig_width, $orig_height, $max_width, $max_height, $crop );
		if ( ! $dims )
			return new WP_Error( 'error_getting_dimensions', __( 'Could not calculate resized image dimensions', 'media-features' ) );
		
		list( $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h ) = $dims;
	
		$newimage = wp_imagecreatetruecolor( $dst_w, $dst_h );
		imagecopyresampled( $newimage, $image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h );
		
		
		// convert from full colors to index colors, like original PNG.
		if ( IMAGETYPE_PNG == $orig_type && function_exists( 'imageistruecolor' ) && ! imageistruecolor( $image ) )
			imagetruecolortopalette( $newimage, false, imagecolorstotal( $image ) );
	
		// we don't need the original in memory anymore
		imagedestroy( $image );
		
		extract( pathinfo( $file ) );
		$name = wp_basename( $file, ".$extension" );
		
		//var_dump( $name, $dirname, $extension );
		$dirname = realpath( $dirname );
		$dest_file = "{$dirname}/{$name}.{$extension}";
		
		// If the file is not writable should not proceed
		if ( ! is_writeable( $dest_file ) )
			return;
		
		
		// Save to file based on original type
		$did_save = false;
		
		switch ( $orig_type ) {
			case IMAGETYPE_GIF :
				imagegif( $newimage, $dest_file );
				break;
				
			case IMAGETYPE_PNG :
				imagepng( $newimage, $dest_file );
				break;
				
			case IMAGETYPE_JPEG :
			case IMAGETYPE_JPEG2000 :
				imagejpeg( $newimage, $dest_file, apply_filters( 'jpeg_quality', 90, 'image_resize' ) );
				break;
								
			default :
				$dest_file = "{$dirname}/{$name}.{$extension}";
				
		}

		imagedestroy( $newimage );

		// Set correct file permissions
		$stat = stat( dirname( $dest_file ));
		$perms = $stat['mode'] & 0000666; //same permissions as parent folder, strip off the executable bits
		@chmod( $dest_file, $perms );
	
		return $dest_file;
		
	}
	
	/** 
	 * Sets the JPEG quality for images
	 * 
	 * @since 0.1
	 * 
	 * @param int $jpeg_quality
	 * @param string $action
	 * @return int
	 */
	public static function set_jpeg_quality( $jpeg_quality, $action ) {
		//if ( 'image_resize' != $action )
		//	return $jpeg_quality;
		if ( $custom_jpeg_quality = get_option( 'media_features_jpeg_quality' ) )
			return (int) $custom_jpeg_quality;
		
		else
			return (int) $jpeg_quality;
		
	}
	
	/** 
	 * Return Mime Types
	 * 
	 * @since 0.1
	 * 
	 * @param array $mime_types
	 * @return array
	 */
	public static function upload_mimes( $mime_types = array() ) {
		return array(
			'jpg|jpeg' => 'image/jpg',
			'png' => 'image/png',
			'gif' => 'image/gif',
			'tif|tiff' => 'image/tiff',
			
			/** TODO: providing support as image in a near future (using ImageMagik in 3.5) **/
			'bmp' => 'application/bmp',
			'ico' => 'application/x-icon',
			
			'wbmp' => 'image/wbmp',
			'svg' => 'image/svg',
			'swf' => 'image/x-shockwave-flash',
			
			'pdf' => 'document/pdf',
			'doc|docx|odt|rtf|wri' => 'document/doc',
			'xla|xls|xlt|xlw|xlsx|ods' => 'document/xls',
			'pot|pps|ppt|potx|ppsx|pptx|odp' => 'document/ppt',
			'odg' => 'document/graph',
			'odc' => 'document/chart',
			'odb' => 'document/db',
			'odf' => 'document/calc',
			
			'txt|tsv|csv|tlc|inf|inicrt|ca|key|htaccess|sql|po|pot' => 'text/plain',
			'rtx' => 'text/richtext',
			'css' => 'text/css',
			'xml|xhtml' => 'text/xml',
			'htm|html' => 'text/html',
			
			'asf|asx|wax|wmv|wmx' => 'video/asf',
			'avi' => 'video/avi',
			'mov|qt' => 'video/quicktime',
			'mpeg|mpg|mpe|mp4' => 'video/mpeg',
			
			'mp3|m4a' => 'audio/mpeg',
			'ra|ram' => 'audio/x-realaudio',
			'wav' => 'audio/wav',
			'ogg' => 'audio/ogg',
			'mid|midi' => 'audio/midi',
			'wma' => 'audio/wma',
			
			'7z' => 'package/7z',
			'rar' => 'package/rar',
			'tar' => 'package/x-tar',
			'zip' => 'package/zip',
			'img|iso' => 'package/img',
			'gz|gzip' => 'package/x-gzip',
			'deb|rpm' => 'package/x-app',
			
			'mo' => 'application/gettext',
			'ttf|woff' => 'application/x-font',
			'fla' => 'application/x-shockwave-flash',
		);
		
	}
	
	/** 
	 * Register new post mime types to be handled by the upload.php file
	 * 
	 * @since 0.1
	 * 
	 * @param array $types
	 * @return array $types
	 */
	public static function post_mime_types( $types ) {
		$types['document'] 	= array( __( 'Documents', 'media-features' ), __( 'Manage Documents', 'media-features' ), _n_noop( 'Document <span class="count">(%s)</span>', 'Documents <span class="count">(%s)</span>', 'media-features' ) );
		$types['text'] 		= array( __( 'Text', 'media-features' ), __( 'Manage Text', 'media-features' ), _n_noop( 'Text <span class="count">(%s)</span>', 'Texts <span class="count">(%s)</span>', 'media-features' ) );
		$types['video'] 	= array( __( 'Video', 'media-features' ), __( 'Manage Videos', 'media-features' ), _n_noop( 'Video <span class="count">(%s)</span>', 'Videos <span class="count">(%s)</span>', 'media-features' ) );
		$types['audio'] 	= array( __( 'Audio', 'media-features' ), __( 'Manage Audio Files', 'media-features' ), _n_noop( 'Audio <span class="count">(%s)</span>', 'Audios <span class="count">(%s)</span>', 'media-features' ) );
		$types['package'] 	= array( __( 'Package', 'media-features' ), __( 'Manage Package Files', 'media-features' ), _n_noop( 'Package <span class="count">(%s)</span>', 'Packages <span class="count">(%s)</span>', 'media-features' ) );
		$types['application'] = array( __( 'Applications', 'media-features' ), __( 'Manage Applications', 'media-features' ), _n_noop( 'Application <span class="count">(%s)</span>', 'Applications <span class="count">(%s)</span>', 'media-features' ) );
		return $types;
		
	}
	
	/** 
	 * Add Categories filter
	 * 
	 * @since 0.1
	 * 
	 * @return void
	 */
	public static function media_upload_filter_dropdowns() {
		global $post_mime_types, $avail_post_mime_types;
		
		if ( empty ( $avail_post_mime_types ) )
			return;
		
		add_filter( 'list_cats', array( 'WP_Media_Features', 'list_cats' ), 10, 2 );
		wp_dropdown_categories( array(
			//'show_option_none'	=> __( 'No categories', 'media-features' ),
			'show_option_all'	=> __( 'All categories', 'media-features' ),
			'name'				=> 'media_category',
			'hide_empty'		=> 0,
			'taxonomy' 			=> 'media_category',
			'selected'			=> get_query_var( 'media_category' ) ? get_query_var( 'media_category' ) : '',
			)
		);
		remove_filter( 'list_cats', array( 'WP_Media_Features', 'list_cats' ) );
		
	}
	
	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.1
	 * 
	 * @param array $types
	 * @return array
	 */
	public static function media_upload_mime_type_links( $types ) {
		return $types;
		
	}
	
	/** 
	 * Update the displayed value for categories dropdown
	 * 
	 * @since 0.1
	 * 
	 * @param string $category_name
	 * @param object $category
	 * @return string
	 */
	public static function list_cats( $category_name, $category = array() ) {
		if ( is_object( $category ) )
			$category->term_id = $category->slug;
		 
		return $category_name;
		
	}
	
	/** 
	 * Add new media_category taxonomy
	 * 
	 * @since 0.1
	 * 
	 * @return void
	 */
	public static function add_attachments_taxonomies() {
		$args = array(
			'label' 					=> __( 'Categories', 'media-features' ),
			'labels' 					=> array(
				'name' 						=> _x( 'Categories', 'taxonomy general name', 'media-features' ),
				'singular_name' 			=> _x( 'Category', 'taxonomy singular name', 'media-features' ),
				'search_items' 				=> __( 'Search Categories', 'media-features' ),
				'popular_items'				=> null,
				'all_items' 				=> __( 'All Categories', 'media-features' ),
				'parent_item' 				=> __( 'Parent Category', 'media-features' ),
				'parent_item_colon'			=> null,
				'edit_item' 				=> __( 'Edit Category', 'media-features' ), 
				'update_item' 				=> __( 'Update Category', 'media-features' ),
				'add_new_item' 				=> __( 'Add New Category', 'media-features' ),
				'new_item_name' 			=> __( 'New Category Name', 'media-features' ),
				'separate_items_with_commas'=> __( 'Separate Category names with commas', 'media-features' ),
				'add_or_remove_items'		=> __( 'Add or Remove Categories', 'media-features' ),
				'choose_from_most_used'		=> __( 'Choose from the Most used', 'media-features' ),
				'menu_name' 				=> __( 'Categories', 'media-features' ),
				
			),
			'show_in_nav_menus' 		=> false,
			'show_tagcloud'	 			=> false,
			'hierarchical' 				=> true,
			'update_count_callback' 	=> array( 'WP_Media_Features', 'update_count_callback' ),
		);
		register_taxonomy( 'media_category', 'attachment', apply_filters( 'media_category_args', $args ) );
		
	}

	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.1
	 * 
	 * @param array $columns
	 * @return array
	 */
	public static function manage_media_columns( $old_columns ) {
		$columns['cb'] 			= $old_columns['cb'];
		$columns['icon'] 		= $old_columns['icon'];
		$columns['title'] 		= $old_columns['title'];
		$columns['author'] 		= $old_columns['author'];
		$columns['categories'] 	= __( 'Categories', 'media-features' );
		$columns['parent'] 		= $old_columns['parent'];
		$columns['comments'] 	= $old_columns['comments'];
		$columns['date'] 		= $old_columns['date'];
		
		return $columns;
		
	}
	
	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.1
	 * 
	 * @param array $columns
	 * @return array
	 */
	public static function manage_media_custom_column( $column, $post_id ) {
		if ( 'categories' == $column ) {
			foreach ( wp_get_post_terms ( $post_id, 'media_category' ) as $term )
				$terms[] = sprintf( '<a href="upload.php?media_category=%s">%s</a>', $term->slug, $term->name );
			
			echo @implode( ', ', $terms );
			
		}
		
	}
	
	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.1
	 * 
	 * @param array $columns
	 * @return array
	 */
	public static function manage_media_category_columns( $columns ) {
		unset( $columns['posts'] );
		$columns['slug'] = __( 'ID', 'media-features' );
		$columns['count'] = __( 'Files', 'media-features' );
		return $columns;
		
	}
	
	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.1
	 * 
	 * @param string $display
	 * @param string $column
	 * @param int $term_id
	 * @return void
	 */
	public static function manage_media_category_custom_column( $display, $column, $term_id ) {
		if ( 'count' == $column ) {
			$term = get_term( $term_id, 'media_category' );
			printf( '<a href="upload.php?media_category=%s">%s</a>', $term->slug, $term->count );
			
		}
		
	}
	
	/** 
	 * Remove View link from row actions
	 * 
	 * @since 0.1
	 * 
	 * @param array $actions
	 * @return array
	 */
	public static function filter_row_actions( $actions ) {
		unset( $actions['view'] );
		return $actions;
		
	}
	
	/** 
	 * Updates count media_category terms 
	 * 
	 * @since 0.1
	 * 
	 * @param array $terms
	 * @param string $taxonomy
	 * @return void
	 */
	public static function update_count_callback( $terms, $taxonomy ) {
		global $wpdb;
		
		foreach ( (array) $terms as $term ) {

			$count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term ) );
	
			do_action( 'edit_term_taxonomy', $term, $taxonomy );
			$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
			do_action( 'edited_term_taxonomy', $term, $taxonomy );
		}
	
	}
	
	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.1
	 * 
	 * @return void
	 */
	public static function add_taxonomies_submenus() {
		add_media_page( __( 'Categories', 'media-features' ), __( 'Categories', 'media-features' ), 'manage_categories', 'edit-tags.php?taxonomy=media_category' );
		
	}
	
	/** 
	 * Change parent_slug of Categories to upload.php
	 * 
	 * @since 0.1
	 * 
	 * @param string $parent_file
	 * @return string
	 */
	public static function media_taxonomies_parent_file( $parent_file ) {
		global $current_screen;
		
		if ( 'edit-media_category' == $current_screen->id ) 
			return 'upload.php';
		
		return $parent_file;
		
	}
	
	/** 
	 * Replaces default input box for tags.
	 * Includes metabox for categories
	 * 
	 * @since 0.1
	 * 
	 * @param array $form_fields
	 * @param object $post
	 * @return array
	 */
	public static function attachment_fields_to_edit( $form_fields, $post ) {
		/** TODO: Hack against the form field upload **/
		if ( 'media' != get_current_screen()->id ) {
			unset( $form_fields['media_category'] );
			return $form_fields;
			
		}
		
		
		$box = array(
			'id' => 'categorydiv',
			'title' => 'Cat',
			'callback' => 'post_categories_meta_box',
			'args' => array(
				'taxonomy' => 'media_category',
			),
		); 
		ob_start();
		include( 'includes/meta-boxes.php' );
		post_categories_meta_box( $post, $box );
		$html = ob_get_clean();
		
		$form_fields['media_category']['input'] = 'html';
		$form_fields['media_category']['html'] = apply_filters( 'media_category_html', $html, $form_fields['media_category'], $post );
		
		return $form_fields;
		
	}
	
	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.1
	 * 
	 * @return void
	 */
	public static function load_media_head() {
		wp_enqueue_script( 'post' );
		wp_enqueue_style( 'media-features', plugin_dir_url( __FILE__ ) . 'css/media-features.css' );
		
	}
	
	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.1
	 * 
	 * @return void
	 */
	public static function add_options() {
		// Save categories
		if ( isset( $_GET['attachment_id'] ) && isset( $_POST['tax_input']['media_category'] ) )
			wp_set_post_terms( (int) $_GET['attachment_id'], $_POST['tax_input']['media_category'], 'media_category' );
			
			
		register_setting( 'media', 'media_features_resize', 'intval' );
		register_setting( 'media', 'media_features_crop', 'intval' );
		register_setting( 'media', 'media_features_jpeg_quality', 'intval' );
		
		add_settings_field( 'resize-image', __( 'Resize Uploaded Image', 'media-features' ), array( 'WP_Media_Features', 'input_resize_image' ), 'media', 'default' );
		add_settings_field( 'jpeg-quality', __( 'JPEG Quality', 'media-features' ), array( 'WP_Media_Features', 'input_jpeg_quality' ), 'media', 'default' );
		
	}
	
	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.1
	 * 
	 * @return void
	 */
	public static function input_resize_image( $args ) {
		$resize_image = (int) get_option( 'media_features_resize', 0 );
		$crop = (int) get_option( 'media_features_crop', 0 );
		?>
		<label for="resize-image">
			<input id="resize-image" type="checkbox" name="media_features_resize"' value="1"'<?php checked( $resize_image, 1 ); ?>>&nbsp;
			<span><?php _e( 'Resize the uploaded version to match Large max sizes.', 'media-features' ); ?></span>
		</label>
		<p class="description"><?php printf( __( 'Activate to resize the uploaded image to match the default Large Max Width and Height currently set to %spx width and %spx height.', 'media-features' ), get_option( 'large_size_w' ), get_option( 'large_size_h' ) ); ?></p>
		<label for="crop-large">
			<input id="crop-large" type="checkbox" name="media_features_crop"' value="1"'<?php checked( $crop, 1 ); ?>>&nbsp;
			<span><?php _e( 'Crop uploaded version of images to fit the default Large image size.', 'media-features' ); ?></span>
		</label>
		<?php
	}
	
	/** 
	 * {@internal Missing Short Description}}
	 * 
	 * @since 0.1
	 * 
	 * @return void
	 */
	public static function input_jpeg_quality( $args ) {
		$jpeg_quality = (int) get_option( 'media_features_jpeg_quality', 90 );
		?>
		<input id="jpeg-quality" type="number" min="0" max="100" step="10" name="media_features_jpeg_quality" value="<?php echo absint( $jpeg_quality ); ?>">
		<?php
	}
	
	
}

new WP_Media_Features;

endif;



/**
 * media_features_activation_hook
 * 
 * Register activation hook for plugin
 * 
 * @since 0.1
 */
function media_features_activation_hook() {
	// Wordpress version control. No compatibility with older versions. ( wp_die )
	if ( version_compare( get_bloginfo( 'version' ), '3.4', '<' ) ) {
		wp_die( 'Media Features is not compatible with versions prior to 3.4' );
	
	}
	
}
register_activation_hook( __FILE__, 'media_features_activation_hook' );
