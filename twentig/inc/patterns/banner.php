<?php
/**
 * Cover block patterns.
 *
 * @package twentig
 * @phpcs:disable Squiz.Strings.DoubleQuoteUsage.NotRequired
 */

twentig_register_block_pattern(
	'twentig/wide-cover',
	array(
		'title'      => __( 'Wide cover', 'twentig' ),
		'categories' => array( 'banner' ),
		'content'    => '<!-- wp:group {"metadata":{"name":"' . esc_html__( 'Wide cover', 'twentig' ) . '"},"align":"full","layout":{"type":"constrained"}} --><div class="wp-block-group alignfull"><!-- wp:cover {"url":"' . twentig_get_pattern_asset( 'wide.jpg' ) . '","dimRatio":50,"isUserOverlayColor":true,"minHeight":500,"align":"wide","layout":{"type":"constrained"}} --><div class="wp-block-cover alignwide" style="min-height:500px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="' . twentig_get_pattern_asset( 'wide.jpg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center"} --><h2 class="wp-block-heading has-text-align-center">' . esc_html_x( 'Write a heading', 'Block pattern content', 'twentig' ) . '</h2><!-- /wp:heading --><!-- wp:paragraph {"align":"center","fontSize":"medium"} --><p class="has-text-align-center has-medium-font-size">Lorem ipsum dolor sit amet, commodo erat adipiscing elit. Sed do eiusmod ut tempor incididunt ut labore. Integer enim risus suscipit eu iaculis sed, ullamcorper at metus. Class aptent taciti sociosqu ad litora.</p><!-- /wp:paragraph --></div></div><!-- /wp:cover --></div><!-- /wp:group -->',
	)
);

twentig_register_block_pattern(
	'twentig/full-width-cover',
	array(
		'title'      => __( 'Full width cover', 'twentig' ),
		'categories' => array( 'banner' ),
		'content'    => '<!-- wp:cover {"url":"' . twentig_get_pattern_asset( 'wide.jpg' ) . '","dimRatio":50,"isUserOverlayColor":true,"minHeight":500,"align":"full","layout":{"type":"constrained"}} --><div class="wp-block-cover alignfull" style="min-height:500px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="' . twentig_get_pattern_asset( 'wide.jpg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center"} --><h2 class="wp-block-heading has-text-align-center">' . esc_html_x( 'Write a heading', 'Block pattern content', 'twentig' ) . '</h2><!-- /wp:heading --></div></div><!-- /wp:cover -->',
	)
);

twentig_register_block_pattern(
	'twentig/fullscreen-cover',
	array(
		'title'      => __( 'Fullscreen cover', 'twentig' ),
		'categories' => array( 'banner' ),
		'content'    => '<!-- wp:cover {"url":"' . twentig_get_pattern_asset( 'wide.jpg' ) . '","dimRatio":50,"isUserOverlayColor":true,"minHeight":100,"minHeightUnit":"vh","align":"full","layout":{"type":"constrained"}} --><div class="wp-block-cover alignfull" style="min-height:100vh"><span aria-hidden="true" class="wp-block-cover__background has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="' . twentig_get_pattern_asset( 'wide.jpg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center"} --><h2 class="wp-block-heading has-text-align-center">' . esc_html_x( 'Write a heading that captivates your audience', 'Block pattern content', 'twentig' ) . '</h2><!-- /wp:heading --><!-- wp:paragraph {"align":"center","fontSize":"medium"} --><p class="has-text-align-center has-medium-font-size">Lorem ipsum dolor sit amet, commodo erat adipiscing elit. Sed do eiusmod ut tempor incididunt ut labore. Integer enim risus suscipit eu iaculis sed, ullamcorper at metus. Class aptent taciti sociosqu ad litora.</p><!-- /wp:paragraph --></div></div><!-- /wp:cover -->',
	)
);

twentig_register_block_pattern(
	'twentig/fullscreen-cover-with-card',
	array(
		'title'      => __( 'Fullscreen cover with card', 'twentig' ),
		'categories' => array( 'banner' ),
		'content'    => '<!-- wp:cover {"url":"' . twentig_get_pattern_asset( 'wide.jpg' ) . '","dimRatio":0,"isUserOverlayColor":true,"minHeight":100,"minHeightUnit":"vh","align":"full","layout":{"type":"constrained"}} --><div class="wp-block-cover alignfull" style="min-height:100vh"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="' . twentig_get_pattern_asset( 'wide.jpg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|15","padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|35","right":"var:preset|spacing|35"}}},"backgroundColor":"base","textColor":"contrast"} --><div class="wp-block-group has-contrast-color has-base-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--35);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--35)"><!-- wp:heading --><h2 class="wp-block-heading">' . esc_html_x( 'Write a heading', 'Block pattern content', 'twentig' ) . '</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Lorem ipsum dolor sit amet, commodo erat adipiscing elit. Sed do eiusmod ut tempor incididunt ut labore et dolore. Integer enim risus suscipit eu iaculis sed, ullamcorper at metus. Class aptent taciti sociosqu ad litora.</p><!-- /wp:paragraph --></div><!-- /wp:group --></div></div><!-- /wp:cover -->',
	)
);

twentig_register_block_pattern(
	'twentig/2-columns-with-cover',
	array(
		'title'      => __( '2 columns with cover', 'twentig' ),
		'categories' => array( 'banner' ),
		'content'    => '<!-- wp:group {"metadata":{"name":"' . esc_html__( '2 columns with cover', 'twentig' ) . '"},"align":"full","layout":{"type":"constrained"}} --><div class="wp-block-group alignfull"><!-- wp:heading {"textAlign":"center","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|60"}}}} --><h2 class="wp-block-heading has-text-align-center" style="margin-bottom:var(--wp--preset--spacing--60)">' . esc_html_x( 'Write a heading that captivates your audience', 'Block pattern content', 'twentig' ) . '</h2><!-- /wp:heading --><!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|20","left":"var:preset|spacing|20"}}},"twStack":"md"} --><div class="wp-block-columns alignwide tw-cols-stack-md"><!-- wp:column --><div class="wp-block-column"><!-- wp:cover {"url":"' . twentig_get_pattern_asset( 'square1.jpg' ) . '","dimRatio":50,"isUserOverlayColor":true,"minHeight":500,"twStretchedLink":true} --><div class="wp-block-cover tw-stretched-link" style="min-height:500px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="' . twentig_get_pattern_asset( 'square1.jpg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center","level":3} --><h3 class="has-text-align-center">' . esc_html_x( 'First item', 'Block pattern content', 'twentig' ) . '</h3><!-- /wp:heading --><!-- wp:paragraph {"align":"center","style":{"typography":{"lineHeight":"1.4"}},"fontSize":"medium"} --><p class="has-text-align-center has-medium-font-size" style="line-height:1.4">Lorem ipsum dolor sit amet.</p><!-- /wp:paragraph --><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center"><a href="#">' . esc_html_x( 'Learn more', 'Block pattern content', 'twentig' ) . '</a></p><!-- /wp:paragraph --></div></div><!-- /wp:cover --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:cover {"url":"' . twentig_get_pattern_asset( 'square2.jpg' ) . '","dimRatio":50,"isUserOverlayColor":true,"minHeight":500,"twStretchedLink":true} --><div class="wp-block-cover tw-stretched-link" style="min-height:500px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="' . twentig_get_pattern_asset( 'square2.jpg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center","level":3} --><h3 class="has-text-align-center">' . esc_html_x( 'Second item', 'Block pattern content', 'twentig' ) . '</h3><!-- /wp:heading --><!-- wp:paragraph {"align":"center","style":{"typography":{"lineHeight":"1.4"}},"fontSize":"medium"} --><p class="has-text-align-center has-medium-font-size" style="line-height:1.4">Sed do eiusmod ut tempor.</p><!-- /wp:paragraph --><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center"><a href="#">' . esc_html_x( 'Learn more', 'Block pattern content', 'twentig' ) . '</a></p><!-- /wp:paragraph --></div></div><!-- /wp:cover --></div><!-- /wp:column --></div><!-- /wp:columns --></div><!-- /wp:group -->',
	)
);

twentig_register_block_pattern(
	'twentig/2-columns-with-cover-bottom-aligned-text',
	array(
		'title'      => __( '2 columns with cover: bottom aligned text', 'twentig' ),
		'categories' => array( 'banner' ),
		'content'    => '<!-- wp:group {"metadata":{"name":"' . esc_html__( '2 columns with cover', 'twentig' ) . '"},"align":"full","layout":{"type":"constrained"}} --><div class="wp-block-group alignfull"><!-- wp:heading {"textAlign":"center","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|60"}}}} --><h2 class="wp-block-heading has-text-align-center" style="margin-bottom:var(--wp--preset--spacing--60)">' . esc_html_x( 'Write a heading that captivates your audience', 'Block pattern content', 'twentig' ) . '</h2><!-- /wp:heading --><!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|20","left":"var:preset|spacing|20"}}},"twStack":"md"} --><div class="wp-block-columns alignwide tw-cols-stack-md"><!-- wp:column --><div class="wp-block-column"><!-- wp:cover {"url":"' . twentig_get_pattern_asset( 'square1.jpg' ) . '","isUserOverlayColor":true,"minHeight":500,"customGradient":"linear-gradient(0deg,rgba(0,0,0,0.8) 0%,rgba(0,0,0,0) 80%)","contentPosition":"bottom left"} --><div class="wp-block-cover has-custom-content-position is-position-bottom-left" style="min-height:500px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-100 has-background-dim wp-block-cover__gradient-background has-background-gradient" style="background:linear-gradient(0deg,rgba(0,0,0,0.8) 0%,rgba(0,0,0,0) 80%)"></span><img class="wp-block-cover__image-background" alt="" src="' . twentig_get_pattern_asset( 'square1.jpg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html_x( 'First item', 'Block pattern content', 'twentig' ) . '</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Lorem ipsum dolor sit amet, commodo erat adipiscing elit. Sed do eiusmod ut tempor incididunt ut labore et dolore.</p><!-- /wp:paragraph --></div></div><!-- /wp:cover --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:cover {"url":"' . twentig_get_pattern_asset( 'square2.jpg' ) . '","isUserOverlayColor":true,"minHeight":500,"customGradient":"linear-gradient(0deg,rgba(0,0,0,0.8) 0%,rgba(0,0,0,0) 80%)","contentPosition":"bottom left"} --><div class="wp-block-cover has-custom-content-position is-position-bottom-left" style="min-height:500px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-100 has-background-dim wp-block-cover__gradient-background has-background-gradient" style="background:linear-gradient(0deg,rgba(0,0,0,0.8) 0%,rgba(0,0,0,0) 80%)"></span><img class="wp-block-cover__image-background" alt="" src="' . twentig_get_pattern_asset( 'square2.jpg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html_x( 'Second item', 'Block pattern content', 'twentig' ) . '</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Integer enim risus, suscipit eu iaculis sed, ullamcorper at metus. Venenatis nec convallis magna eu congue velit.</p><!-- /wp:paragraph --></div></div><!-- /wp:cover --></div><!-- /wp:column --></div><!-- /wp:columns --></div><!-- /wp:group -->',
	)
);

twentig_register_block_pattern(
	'twentig/2-columns-with-cover-x-2-top-aligned-text',
	array(
		'title'      => __( '2 columns with cover x 2: top aligned text', 'twentig' ),
		'categories' => array( 'banner' ),
		'content'    => '<!-- wp:group {"metadata":{"name":"' . esc_html__( '2 columns with cover', 'twentig' ) . '"},"align":"full","layout":{"type":"constrained"}} --><div class="wp-block-group alignfull"><!-- wp:heading {"textAlign":"center","style":{"spacing":{"margin":{"bottom":"var:preset|spacing|60"}}}} --><h2 class="wp-block-heading has-text-align-center" style="margin-bottom:var(--wp--preset--spacing--60)">' . esc_html_x( 'Write a heading that captivates your audience', 'Block pattern content', 'twentig' ) . '</h2><!-- /wp:heading --><!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"var:preset|spacing|20","left":"var:preset|spacing|20"}}}} --><div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column"><!-- wp:cover {"url":"' . twentig_get_pattern_asset( 'square1.jpg' ) . '","isUserOverlayColor":true,"customGradient":"linear-gradient(180deg,rgba(0,0,0,0.8) 0%,rgba(0,0,0,0) 80%)","contentPosition":"top left","style":{"spacing":{"padding":{"top":"var:preset|spacing|25","right":"var:preset|spacing|25","bottom":"var:preset|spacing|25","left":"var:preset|spacing|25"}}}} --><div class="wp-block-cover has-custom-content-position is-position-top-left" style="padding-top:var(--wp--preset--spacing--25);padding-right:var(--wp--preset--spacing--25);padding-bottom:var(--wp--preset--spacing--25);padding-left:var(--wp--preset--spacing--25)"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-100 has-background-dim wp-block-cover__gradient-background has-background-gradient" style="background:linear-gradient(180deg,rgba(0,0,0,0.8) 0%,rgba(0,0,0,0) 80%)"></span><img class="wp-block-cover__image-background" alt="" src="' . twentig_get_pattern_asset( 'square1.jpg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"600","lineHeight":"1.4"}}} --><p style="font-style:normal;font-weight:600;line-height:1.4">Lorem ipsum dolor sit amet, commodo erat adipiscing elit. Sed do eiusmod ut tempor.</p><!-- /wp:paragraph --></div></div><!-- /wp:cover --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:cover {"url":"' . twentig_get_pattern_asset( 'square2.jpg' ) . '","isUserOverlayColor":true,"customGradient":"linear-gradient(180deg,rgba(0,0,0,0.8) 0%,rgba(0,0,0,0) 80%)","contentPosition":"top left","style":{"spacing":{"padding":{"top":"var:preset|spacing|25","right":"var:preset|spacing|25","bottom":"var:preset|spacing|25","left":"var:preset|spacing|25"}}}} --><div class="wp-block-cover has-custom-content-position is-position-top-left" style="padding-top:var(--wp--preset--spacing--25);padding-right:var(--wp--preset--spacing--25);padding-bottom:var(--wp--preset--spacing--25);padding-left:var(--wp--preset--spacing--25)"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-100 has-background-dim wp-block-cover__gradient-background has-background-gradient" style="background:linear-gradient(180deg,rgba(0,0,0,0.8) 0%,rgba(0,0,0,0) 80%)"></span><img class="wp-block-cover__image-background" alt="" src="' . twentig_get_pattern_asset( 'square2.jpg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"600","lineHeight":"1.4"}}} --><p style="font-style:normal;font-weight:600;line-height:1.4">Integer enim risus suscipit eu iaculis sed ullamcorper at metus. Venenatis nec convallis magna eu congue velit.</p><!-- /wp:paragraph --></div></div><!-- /wp:cover --></div><!-- /wp:column --></div><!-- /wp:columns --><!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"var:preset|spacing|20","left":"var:preset|spacing|20"},"margin":{"top":"var:preset|spacing|20"}}}} --><div class="wp-block-columns" style="margin-top:var(--wp--preset--spacing--20)"><!-- wp:column --><div class="wp-block-column"><!-- wp:cover {"url":"' . twentig_get_pattern_asset( 'square3.jpg' ) . '","isUserOverlayColor":true,"customGradient":"linear-gradient(180deg,rgba(0,0,0,0.8) 0%,rgba(0,0,0,0) 80%)","contentPosition":"top left","style":{"spacing":{"padding":{"top":"var:preset|spacing|25","right":"var:preset|spacing|25","bottom":"var:preset|spacing|25","left":"var:preset|spacing|25"}}}} --><div class="wp-block-cover has-custom-content-position is-position-top-left" style="padding-top:var(--wp--preset--spacing--25);padding-right:var(--wp--preset--spacing--25);padding-bottom:var(--wp--preset--spacing--25);padding-left:var(--wp--preset--spacing--25)"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-100 has-background-dim wp-block-cover__gradient-background has-background-gradient" style="background:linear-gradient(180deg,rgba(0,0,0,0.8) 0%,rgba(0,0,0,0) 80%)"></span><img class="wp-block-cover__image-background" alt="" src="' . twentig_get_pattern_asset( 'square3.jpg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"600","lineHeight":"1.4"}}} --><p style="font-style:normal;font-weight:600;line-height:1.4">Duis enim elit porttitor id feugiat at blandit at erat. Proin varius libero sit amet tortor.</p><!-- /wp:paragraph --></div></div><!-- /wp:cover --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:cover {"url":"' . twentig_get_pattern_asset( 'square4.jpg' ) . '","isUserOverlayColor":true,"customGradient":"linear-gradient(180deg,rgba(0,0,0,0.8) 0%,rgba(0,0,0,0) 80%)","contentPosition":"top left","style":{"spacing":{"padding":{"top":"var:preset|spacing|25","right":"var:preset|spacing|25","bottom":"var:preset|spacing|25","left":"var:preset|spacing|25"}}}} --><div class="wp-block-cover has-custom-content-position is-position-top-left" style="padding-top:var(--wp--preset--spacing--25);padding-right:var(--wp--preset--spacing--25);padding-bottom:var(--wp--preset--spacing--25);padding-left:var(--wp--preset--spacing--25)"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-100 has-background-dim wp-block-cover__gradient-background has-background-gradient" style="background:linear-gradient(180deg,rgba(0,0,0,0.8) 0%,rgba(0,0,0,0) 80%)"></span><img class="wp-block-cover__image-background" alt="" src="' . twentig_get_pattern_asset( 'square4.jpg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"600","lineHeight":"1.4"}}} --><p style="font-style:normal;font-weight:600;line-height:1.4">Fusce sed magna eu ligula commodo hendrerit fringilla ac purus integer sagittis.</p><!-- /wp:paragraph --></div></div><!-- /wp:cover --></div><!-- /wp:column --></div><!-- /wp:columns --></div><!-- /wp:group -->',
	)
);

twentig_register_block_pattern(
	'twentig/2-columns-with-cover-x-2-full-width',
	array(
		'title'      => __( '2 columns with cover x 2: full width', 'twentig' ),
		'categories' => array( 'banner' ),
		'content'    => '<!-- wp:columns {"align":"full","style":{"spacing":{"blockGap":"0px"}},"twStack":"md"} --><div class="wp-block-columns alignfull tw-cols-stack-md"><!-- wp:column --><div class="wp-block-column"><!-- wp:cover {"url":"' . twentig_get_pattern_asset( 'square1.jpg' ) . '","dimRatio":50,"isUserOverlayColor":true,"minHeight":500,"style":{"spacing":{"blockGap":"var:preset|spacing|15","padding":{"left":"var:preset|spacing|site-padding","right":"var:preset|spacing|site-padding"}}},"layout":{"type":"constrained","contentSize":"480px"},"twStretchedLink":true} --><div class="wp-block-cover tw-stretched-link" style="padding-right:var(--wp--preset--spacing--site-padding);padding-left:var(--wp--preset--spacing--site-padding);min-height:500px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="' . twentig_get_pattern_asset( 'square1.jpg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center","fontSize":"xx-large"} --><h2 class="wp-block-heading has-text-align-center has-xx-large-font-size">' . esc_html_x( 'First item', 'Block pattern content', 'twentig' ) . '</h2><!-- /wp:heading --><!-- wp:paragraph {"align":"center","fontSize":"medium"} --><p class="has-text-align-center has-medium-font-size">Lorem ipsum dolor sit amet.</p><!-- /wp:paragraph --><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center"><a href="#">' . esc_html_x( 'Learn more', 'Block pattern content', 'twentig' ) . '</a></p><!-- /wp:paragraph --></div></div><!-- /wp:cover --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:cover {"url":"' . twentig_get_pattern_asset( 'square2.jpg' ) . '","dimRatio":50,"isUserOverlayColor":true,"minHeight":500,"style":{"spacing":{"blockGap":"var:preset|spacing|15","padding":{"left":"var:preset|spacing|site-padding","right":"var:preset|spacing|site-padding"}}},"layout":{"type":"constrained","contentSize":"480px"},"twStretchedLink":true} --><div class="wp-block-cover tw-stretched-link" style="padding-right:var(--wp--preset--spacing--site-padding);padding-left:var(--wp--preset--spacing--site-padding);min-height:500px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="' . twentig_get_pattern_asset( 'square2.jpg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center","fontSize":"xx-large"} --><h2 class="wp-block-heading has-text-align-center has-xx-large-font-size">' . esc_html_x( 'Second item', 'Block pattern content', 'twentig' ) . '</h2><!-- /wp:heading --><!-- wp:paragraph {"align":"center","fontSize":"medium"} --><p class="has-text-align-center has-medium-font-size">Integer enim risus.</p><!-- /wp:paragraph --><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center"><a href="#">' . esc_html_x( 'Learn more', 'Block pattern content', 'twentig' ) . '</a></p><!-- /wp:paragraph --></div></div><!-- /wp:cover --></div><!-- /wp:column --></div><!-- /wp:columns --><!-- wp:columns {"align":"full","style":{"spacing":{"blockGap":"0px","margin":{"top":"0"}}},"twStack":"md"} --><div class="wp-block-columns alignfull tw-cols-stack-md" style="margin-top:0"><!-- wp:column --><div class="wp-block-column"><!-- wp:cover {"url":"' . twentig_get_pattern_asset( 'square3.jpg' ) . '","dimRatio":50,"isUserOverlayColor":true,"minHeight":500,"style":{"spacing":{"blockGap":"var:preset|spacing|15","padding":{"left":"var:preset|spacing|site-padding","right":"var:preset|spacing|site-padding"}}},"layout":{"type":"constrained","contentSize":"480px"},"twStretchedLink":true} --><div class="wp-block-cover tw-stretched-link" style="padding-right:var(--wp--preset--spacing--site-padding);padding-left:var(--wp--preset--spacing--site-padding);min-height:500px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="' . twentig_get_pattern_asset( 'square3.jpg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center","fontSize":"xx-large"} --><h2 class="wp-block-heading has-text-align-center has-xx-large-font-size">' . esc_html_x( 'Third item', 'Block pattern content', 'twentig' ) . '</h2><!-- /wp:heading --><!-- wp:paragraph {"align":"center","fontSize":"medium"} --><p class="has-text-align-center has-medium-font-size">Mauris dui tellus mollis.</p><!-- /wp:paragraph --><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center"><a href="#">' . esc_html_x( 'Learn more', 'Block pattern content', 'twentig' ) . '</a></p><!-- /wp:paragraph --></div></div><!-- /wp:cover --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:cover {"url":"' . twentig_get_pattern_asset( 'square4.jpg' ) . '","dimRatio":50,"isUserOverlayColor":true,"minHeight":500,"style":{"spacing":{"blockGap":"var:preset|spacing|15","padding":{"left":"var:preset|spacing|site-padding","right":"var:preset|spacing|site-padding"}}},"layout":{"type":"constrained","contentSize":"480px"},"twStretchedLink":true} --><div class="wp-block-cover tw-stretched-link" style="padding-right:var(--wp--preset--spacing--site-padding);padding-left:var(--wp--preset--spacing--site-padding);min-height:500px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="' . twentig_get_pattern_asset( 'square4.jpg' ) . '" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center","fontSize":"xx-large"} --><h2 class="wp-block-heading has-text-align-center has-xx-large-font-size">' . esc_html_x( 'Fourth item', 'Block pattern content', 'twentig' ) . '</h2><!-- /wp:heading --><!-- wp:paragraph {"align":"center","fontSize":"medium"} --><p class="has-text-align-center has-medium-font-size">Nunc vehicula at rhoncus ultrices.</p><!-- /wp:paragraph --><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center"><a href="#">' . esc_html_x( 'Learn more', 'Block pattern content', 'twentig' ) . '</a></p><!-- /wp:paragraph --></div></div><!-- /wp:cover --></div><!-- /wp:column --></div><!-- /wp:columns -->',
	)
);
