<?php
/**
 * Block styles registration.
 *
 * @package twentig
 */

defined( 'ABSPATH' ) || exit;

function twentig_register_block_styles() {

	/* Media & Text */
	register_block_style(
		'core/media-text',
		array(
			'name'  => 'tw-shadow',
			'label' => esc_html__( 'Shadow', 'twentig' ),
		)
	);

	register_block_style(
		'core/media-text',
		array(
			'name'  => 'tw-overlap',
			'label' => esc_html_x( 'Overlap', 'noun', 'twentig' ),
		)
	);

	register_block_style(
		'core/media-text',
		array(
			'name'  => 'tw-hard-shadow',
			'label' => esc_html__( 'Hard shadow', 'twentig' ),
		)
	);

	/* List */

	register_block_style(
		'core/list',
		array(
			'name'  => 'tw-square',
			'label' => esc_html__( 'Square', 'twentig' ),
		)
	);

	register_block_style(
		'core/list',
		array(
			'name'  => 'tw-dash',
			'label' => esc_html__( 'Dash', 'twentig' ),
		)
	);

	register_block_style(
		'core/list',
		array(
			'name'  => 'tw-checkmark',
			'label' => esc_html__( 'Checkmark', 'twentig' ),
		)
	);

	register_block_style(
		'core/list',
		array(
			'name'  => 'tw-arrow',
			'label' => esc_html__( 'Arrow', 'twentig' ),
		)
	);

	register_block_style(
		'core/list',
		array(
			'name'  => 'tw-border',
			'label' => esc_html__( 'Border', 'twentig' ),
		)
	);

	register_block_style(
		'core/list',
		array(
			'name'  => 'tw-border-inner',
			'label' => esc_html__( 'Inner border', 'twentig' ),
		)
	);

	register_block_style(
		'core/list',
		array(
			'name'  => 'tw-table',
			'label' => esc_html__( 'Table', 'twentig' ),
		)
	);

	register_block_style(
		'core/list',
		array(
			'name'  => 'tw-no-bullet',
			'label' => esc_html_x( 'No bullet', 'list style', 'twentig' ),
		)
	);

	register_block_style(
		'core/list',
		array(
			'name'  => 'tw-inline',
			'label' => esc_html_x( 'Inline', 'list style', 'twentig' ),
		)
	);

	/* Table */
	register_block_style(
		'core/table',
		array(
			'name'  => 'tw-border-h',
			'label' => esc_html__( 'Horizontal border', 'twentig' ),
		)
	);

	register_block_style(
		'core/table',
		array(
			'name'  => 'tw-border-h-inner',
			'label' => esc_html__( 'Horizontal inner border', 'twentig' ),
		)
	);

	/* Quote */
	register_block_style(
		'core/quote',
		array(
			'name'  => 'tw-icon',
			'label' => esc_html__( 'Icon', 'twentig' ),
		)
	);

	/* Pullquote */

	register_block_style(
		'core/pullquote',
		array(
			'name'  => 'tw-icon',
			'label' => esc_html_x( 'Icon', 'block style', 'twentig' ),
		)
	);

	/* Social Links */
	register_block_style(
		'core/social-links',
		array(
			'name'  => 'tw-square',
			'label' => esc_html__( 'Square', 'twentig' ),
		)
	);

	register_block_style(
		'core/social-links',
		array(
			'name'  => 'tw-rounded',
			'label' => esc_html__( 'Rounded', 'twentig' ),
		)
	);

	/* Separator */

	register_block_style(
		'core/separator',
		array(
			'name'  => 'tw-asterisks',
			'label' => esc_html__( 'Asterisks', 'twentig' ),
		)
	);

	register_block_style(
		'core/separator',
		array(
			'name'  => 'tw-dotted',
			'label' => esc_html__( 'Dotted', 'twentig' ),
		)
	);

	register_block_style(
		'core/separator',
		array(
			'name'  => 'tw-dashed',
			'label' => esc_html__( 'Dashed', 'twentig' ),
		)
	);

	/* Post Terms */

	register_block_style(
		'core/tag-cloud',
		array(
			'name'  => 'tw-outline-rounded',
			'label' => esc_html__( 'Rounded', 'twentig' ),
		)
	);

	register_block_style(
		'core/tag-cloud',
		array(
			'name'  => 'tw-outline-pill',
			'label' => esc_html__( 'Pill', 'twentig' ),
		)
	);

	register_block_style(
		'core/tag-cloud',
		array(
			'name'  => 'tw-plain',
			'label' => esc_html__( 'Plain', 'twentig' ),
		)
	);

	register_block_style(
		'core/tag-cloud',
		array(
			'name'  => 'tw-list',
			'label' => esc_html__( 'List', 'twentig' ),
		)
	);

	/* Post Terms */

	register_block_style(
		'core/post-terms',
		array(
			'name'  => 'tw-outline',
			'label' => esc_html__( 'Outline', 'twentig' ),
		)
	);

	register_block_style(
		'core/post-terms',
		array(
			'name'  => 'tw-outline-rounded',
			'label' => esc_html__( 'Rounded', 'twentig' ),
		)
	);

	register_block_style(
		'core/post-terms',
		array(
			'name'  => 'tw-outline-pill',
			'label' => esc_html__( 'Pill', 'twentig' ),
		)
	);

	register_block_style(
		'core/post-terms',
		array(
			'name'  => 'tw-hashtag',
			'label' => esc_html__( 'Hashtag', 'twentig' ),
		)
	);

	register_block_style(
		'core/post-terms',
		array(
			'name'  => 'tw-plain',
			'label' => esc_html__( 'Plain', 'twentig' ),
		)
	);

	register_block_style(
		'core/post-terms',
		array(
			'name'  => 'tw-list',
			'label' => esc_html__( 'List', 'twentig' ),
		)
	);

	/* Search */
	register_block_style(
		'core/search',
		array(
			'name'  => 'tw-underline',
			'label' => esc_html__( 'Underline', 'twentig' ),
		)
	);

	/* Post Navigation Link */
	register_block_style(
		'core/post-navigation-link',
		array(
			'name'  => 'tw-nav-stack',
			'label' => esc_html__( 'Stack', 'twentig' ),
		)
	);


	/* Pagination */

	register_block_style(
		'core/query-pagination-numbers',
		array(
			'name'  => 'tw-square',
			'label' => esc_html__( 'Square', 'twentig' ),
		)
	);

	register_block_style(
		'core/query-pagination-numbers',
		array(
			'name'  => 'tw-rounded',
			'label' => esc_html__( 'Rounded', 'twentig' ),
		)
	);

	register_block_style(
		'core/query-pagination-numbers',
		array(
			'name'  => 'tw-circle',
			'label' => esc_html__( 'Circle', 'twentig' ),
		)
	);

	register_block_style(
		'core/query-pagination-numbers',
		array(
			'name'  => 'tw-plain',
			'label' => esc_html__( 'Plain', 'twentig' ),
		)
	);

	register_block_style(
		'core/query-pagination-previous',
		array(
			'name'  => 'tw-btn-square',
			'label' => esc_html__( 'Square', 'twentig' ),
		)
	);

	register_block_style(
		'core/query-pagination-previous',
		array(
			'name'  => 'tw-btn-rounded',
			'label' => esc_html__( 'Rounded', 'twentig' ),
		)
	);

	register_block_style(
		'core/query-pagination-previous',
		array(
			'name'  => 'tw-btn-pill',
			'label' => esc_html__( 'Pill', 'twentig' ),
		)
	);

	register_block_style(
		'core/query-pagination-next',
		array(
			'name'  => 'tw-btn-square',
			'label' => esc_html__( 'Square', 'twentig' ),
		)
	);

	register_block_style(
		'core/query-pagination-next',
		array(
			'name'  => 'tw-btn-rounded',
			'label' => esc_html__( 'Rounded', 'twentig' ),
		)
	);

	register_block_style(
		'core/query-pagination-next',
		array(
			'name'  => 'tw-btn-pill',
			'label' => esc_html__( 'Pill', 'twentig' ),
		)
	);

	register_block_style(
		'core/navigation-link',
		array(
			'name'  => 'tw-external-link',
			'label' => esc_html__( 'External', 'twentig' ),
		)
	);

}
add_action( 'init', 'twentig_register_block_styles' );
