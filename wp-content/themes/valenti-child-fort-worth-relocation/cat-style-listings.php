<?php /* Category/Blog Style Listings */

$cb_qry = cb_get_qry();

$category = $wp_query->get_queried_object();
$categoryId = $category->term_id;




$cb_qry = new WP_Query( array( 
    'post_type' => 'listing',
    'posts_per_page' => -1,
    'tax_query' => array(
        'relation' => 'AND',
        array(
          'taxonomy' => 'listing_category',
          'field'    => 'id',
          'terms'    => array( $categoryId ),
        ),
        array(
          'taxonomy' => 'listing_tags',
          'field'    => 'slug',
          'terms'    => array( 'featured' ),
          'operator' => 'IN',
        ),
  ), ) );
$count = $cb_qry->post_count;



if ( $cb_qry->have_posts() ) : while ( $cb_qry->have_posts() ) : $cb_qry->the_post();
  global $post;
  $cb_post_id = $post->ID;
  $cb_category_color = cb_get_cat_color( $cb_post_id );
  $businessAddress = get_field( "business_address" );
  $phone = get_field( "phone_number" );
  $url = get_field( "website" );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('cb-blog-style-a cb-blog-style cb-color-hover cb-separated clearfix' ); ?> role="article">

  <div class="cb-mask" style="background-color:<?php echo $cb_category_color; ?>;">

    <?php
        cb_thumbnail('360', '240');
        echo cb_review_ext_box( $cb_post_id, $cb_category_color );
    ?>

  </div>

  <div class="cb-meta listings">

      <h2 class="cb-post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
      <address class="listing-address">
      <?php echo $businessAddress ?>
      </address>
      <span class="phone-number"><?php echo $phone ?></span>
      <span class="url"><a href="<?php echo $url ?>" target="_blank"><?php echo $url ?></a></span>


  </div>

</article>

<?php

  endwhile;
  cb_page_navi( $cb_qry );
  endif;
  wp_reset_postdata();

  $cb_qry = new WP_Query( array( 
    'post_type' => 'listing',
    'posts_per_page' => -1,
    'tax_query' => array(
        'relation' => 'AND',
        array(
          'taxonomy' => 'listing_category',
          'field'    => 'id',
          'terms'    => array( $categoryId ),
        ),
        array(
          'taxonomy' => 'listing_tags',
          'field'    => 'slug',
          'terms'    => array( 'featured' ),
          'operator' => 'NOT IN',
        ),
  ), ) );
$count = $cb_qry->post_count;




if ( $cb_qry->have_posts() ) : while ( $cb_qry->have_posts() ) : $cb_qry->the_post();
  global $post;
  $cb_post_id = $post->ID;
  $cb_category_color = cb_get_cat_color( $cb_post_id );
  $businessAddress = get_field( "business_address" );
  $phone = get_field( "phone_number" );
  $url = get_field( "website" );
?>



<article id="post-<?php the_ID(); ?>" <?php post_class('cb-blog-style-a cb-blog-style cb-color-hover cb-separated clearfix' ); ?> role="article">

  

  <div class="cb-meta listings">

      <h2 class="cb-post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
      <address class="listing-address">
      <?php echo $businessAddress ?>
      </address>
      <span class="phone-number"><?php echo $phone ?></span>
      <span class="url"><a href="<?php echo $url ?>" target="_blank"><?php echo $url ?></a></span>


  </div>

</article>

<?php

  endwhile;
  cb_page_navi( $cb_qry );
  endif;
  wp_reset_postdata();