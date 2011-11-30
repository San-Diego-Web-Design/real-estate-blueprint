<?php
/**
 * Sets up the core framework's widgets. 
 *
 * @package PlacesterSpine
 * @subpackage Functions
 */

/* Register Hybrid widgets. */
add_action( 'widgets_init', 'pls_register_widgets' );

/**
 * Registers the core frameworks widgets.  These widgets typically overwrite the equivalent default WordPress
 * widget by extending the available options of the widget.
 *
 * @since 0.0.1
 * @uses register_widget() Registers individual widgets with WordPress
 * @link http://codex.wordpress.org/Function_Reference/register_widget
 */
function pls_register_widgets() {

	/** Load the Placester Agent widget. */
	require_once( trailingslashit( PLS_FUNCTIONS_DIR ) . 'widgets/agent.php' );

	/** Load the Placester Recent Posts widget. */
	require_once( trailingslashit( PLS_FUNCTIONS_DIR ) . 'widgets/recent_posts.php' );

	/** Load the Placester Listings widget. */
	require_once( trailingslashit( PLS_FUNCTIONS_DIR ) . 'widgets/listings.php' );

	/* Register each of the widgets. */
	register_widget( 'PLS_Widget_Agent' );

	/* Register each of the widgets. */
	register_widget( 'PLS_Widget_Recent_Posts' );

	/* Register each of the widgets. */
	register_widget( 'PLS_Widget_Listings' );

}
