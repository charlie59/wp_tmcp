<?php
/**
 * Default Events Template
 * This file is the basic wrapper template for all the views if 'Default Events Template'
 * is selected in Events -> Settings -> Template -> Events Template.
 *
 * Override this template in your own theme by creating a file at [your-theme]/tribe-events/default-template.php
 *
 * @package TribeEventsCalendar
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

get_header(); ?>
<div class="span6">
	<div id="tribe-events-pg-template">
		<div id="tribe-events-add-your-event">
			<div class="block">
          		<article class="card clearfix">
            		<footer class="meta">
						<h6 class="pull-right">
							<a href="/submitcalendarevent/">Add your event</a>
						</h6>
					</footer>
          		</article>
        	</div>  <!-- /.block -->
    	</div>
		<?php tribe_events_before_html(); ?>
		<?php tribe_get_view(); ?>
		<?php tribe_events_after_html(); ?>
	</div> <!-- #tribe-events-pg-template -->
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>