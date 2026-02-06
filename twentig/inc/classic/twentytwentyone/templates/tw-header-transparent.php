<?php
/**
 * Template Name: Twentig - Transparent header
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

get_header();

/* Start the Loop */
while ( have_posts() ) :
	the_post(); ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<div class="entry-content">
			<?php
			the_content();

			wp_link_pages(
				array(
					'before'   => '<nav class="page-links" aria-label="' . esc_attr__( 'Page', 'twentytwentyone' ) . '">', // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
					'after'    => '</nav>',
					/* translators: %: page number. */
					'pagelink' => esc_html__( 'Page %', 'twentytwentyone' ), // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch
				)
			);
			?>
		</div><!-- .entry-content -->

	</article><!-- #post-<?php the_ID(); ?> -->

	<?php

	// If comments are open or there is at least one comment, load up the comment template.
	if ( comments_open() || get_comments_number() ) {
		comments_template();
	}

endwhile; // End of the loop.

get_footer();
