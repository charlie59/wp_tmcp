<?php
/**
 * Template Name: Rides Template
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */

 get_header();


$args = array( 'post_type' => 'post', 'posts_per_page' => 99, "category_name" => 'rides');
$loop = new WP_Query( $args );



?>



        <div id="cb-content" class="wrap clearfix">

            <div class="cb-cat-header" style="border-bottom-color:#eabf00;">
                <h1 id="cb-cat-title"><?php the_title(); ?></h1>
            </div>
            
            <div class="clearfix">
                <div id="main" class="cb-main clearfix cb-module-block cb-light-off cb-blog-style-roll">

                    <?php the_content(); ?>


                    <?php 

                    while ( $loop->have_posts() ) : $loop->the_post(); ?>

                       <article id="post-11753" class="cb-blog-style-a cb-blog-style cb-color-hover cb-separated clearfix post-11753 post type-post status-publish format-standard has-post-thumbnail hentry category-home-page" role="article">

  <div class="cb-mask" style="background-color:#eabf00;">

    <a href="wild-wild-texas/"><img src="/wp-content/uploads/sites/3/2017/06/Marfa-FIlm-Festival_-360x240.jpg" class="attachment-cb-360-240 size-cb-360-240 wp-post-image" alt="Marfa Film Festival" height="240" width="360"></a>
  </div>

  <div class="cb-meta">

      <h2 class="cb-post-title"><a href="/wild-wild-texas/">Wild, Wild Texas</a></h2>
      <div class="cb-byline cb-font-header"> <div class="cb-date cb-byline-element"><i class="fa fa-clock-o"></i> <time datetime="2017-06-23">June 23, 2017</time></div></div>      <div class="cb-excerpt">




With miles of the state’s most rugged landscape, it’s a no- brainer that West Texas would be the setting for movies about wild men and wild times. Fandango (1985) starred Kevin Costner and a pre-Brat Pack <span class="cb-excerpt-dots">...</span> <a href="http://ridetexas.sites.tmcp.com/wild-wild-texas/"><span class="cb-read-more"> Read More...</span></a></div>

  </div>

</article>

                    <?php endwhile;
                    wp_reset_postdata();

                    ?>



                </div> <!-- end #main -->

                <?php get_sidebar(); ?>
            </div>

        </div> <!-- end #cb-content -->

<?php get_footer(); ?>
