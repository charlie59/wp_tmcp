<?php /* To overwrite a function from either functions.php or from library/core.php, overwrite it in this file */ 

/*********************
CHILD STYLESHEET ENQUEUEING
*********************/
if ( ! function_exists( 'cb_script_loaders_child' ) ) {   
    function cb_script_loaders_child() {

        add_action('wp_enqueue_scripts', 'cb_scripts_and_styles_child', 999);
    }
}

add_action('after_setup_theme','cb_script_loaders_child', 16);
    

if ( ! function_exists( 'cb_scripts_and_styles_child' ) ) {
       
    function cb_scripts_and_styles_child() {
                
      if (!is_admin()) {
        // Register child stylesheet for RTL/LTR
        if ( is_rtl() ) {
            wp_register_style( 'cb-child-stylesheet',  get_stylesheet_directory_uri() . '/style-rtl.css', array(), '1.0', 'all' );
        } else {
            wp_register_style( 'cb-child-stylesheet',  get_stylesheet_directory_uri() . '/style.css', array(), '1.0', 'all' );
        }
        wp_enqueue_style('cb-child-stylesheet'); // enqueue it
      }
    }
    
}


/**
 * Register our sidebars and widgetized areas.
 *
 */
function arphabet_widgets_init() {

    register_sidebar( array(
        'name'          => 'Header Ad Widget',
        'id'            => 'header_ad_widget',
        'before_widget' => '<div class="header-ad-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '',
        'after_title'   => '',
    ) );

}
add_action( 'widgets_init', 'arphabet_widgets_init' );



/** 
 * create the shortcode for the listings categories 
 *
 */
// Add Shortcode
function listing_categories( $atts ) {

	// Attributes
	$atts = shortcode_atts(
		array(
		),
		$atts
	);

	echo '<ul class="listing-categories">';

	
	//$customPostTaxonomies = get_object_taxonomies('listing');
	//$customPostTaxonomies = get_object_taxonomies('listing');

	$categories = get_categories('taxonomy=listing_category&type=listing'); 

	//var_dump( $categories );

	/*
	var_dump( $customPostTaxonomies );
	echo '<hr />';
	var_dump( $categories );

	if(count($customPostTaxonomies) > 0)
	{
	     foreach($customPostTaxonomies as $tax)
	     {
		     $args = array(
	         	  'orderby' => 'name',
		          'show_count' => 0,
	        	  'pad_counts' => 0,
		          'hierarchical' => 1,
	        	  //'taxonomy' => $tax,
	        	  'title_li' => ''
	        	);

		     $categories = get_categories( $args );
	     }
	}

	//var_dump( $categories );
	*/
	
	foreach( $categories as $category ) {
		echo '<li><a href="' . get_site_url() . '/listings/' . $category->slug . '">' . $category->name . ' <span>(' . $category->count . ')</span></a></li>';
	}





    echo '</ul>';



}
add_shortcode( 'listing_categories', 'listing_categories' );




?>