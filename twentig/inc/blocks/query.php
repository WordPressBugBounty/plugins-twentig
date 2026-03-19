<?php
/**
 * Server-side customizations for the `core/query` block.
 *
 * @package twentig
 */
 
defined( 'ABSPATH' ) || exit;

/**
 * Filter Query Loop block to handle current post filters.
 *
 * @param array    $query The query arguments.
 * @param WP_Block $block Block instance.
 * @return array Modified query arguments.
 */
function twentig_filter_query_block_current( $query, $block ) {
	if ( empty( $block->context['query']['twSinglePostsFilter'] ) || ! is_singular() ) {
		return $query;
	}

	$filter     = $block->context['query']['twSinglePostsFilter'];
	$current_id = get_the_ID();
	$post_type  = get_post_type();

	// Exclude current post for all filter types
	$query['post__not_in'] = isset( $query['post__not_in'] ) 
		? array_merge( $query['post__not_in'], array( $current_id ) )
		: array( $current_id );

	if ( in_array( $filter, array( 'same-category', 'same-tag' ), true ) ) {
		$taxonomy = false;
		if ( 'post' === $post_type ) {
			$taxonomy = 'same-tag' === $filter ? 'post_tag' : 'category';
		} elseif ( 'portfolio' === $post_type ) {
			$taxonomy = 'same-tag' === $filter ? 'portfolio_tag' : 'portfolio_category';
		}

		if ( $taxonomy && taxonomy_exists( $taxonomy ) ) {
			$terms = get_the_terms( $current_id, $taxonomy );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$term_ids = wp_list_pluck( $terms, 'term_id' );
		
				if ( ! empty( $term_ids ) ) {
					if ( ! isset( $query['tax_query'] ) ) {
						$query['tax_query'] = array();
					}
						
					$query['tax_query'][] = array(
						'taxonomy' => $taxonomy,
						'terms'    => $term_ids,
					);
				}
			}
		}
	}

	return $query;
}
add_filter( 'query_loop_block_query_vars', 'twentig_filter_query_block_current', 10, 2 );
