<?php
/**
 * The core functions file. This file should be loaded prior to any other 
 * files because its functions are needed to run the framework.
 *
 * @package PlacesterSpine
 * @subpackage Functions
 */

/**
 * Defines and returns the theme textdomain. 
 * 
 * Defines the 'pls_textdomain' filter.
 * 
 * @global object $placester_spine The global Placester Spine object.
 * @return string The theme textdomain.
 * @since 0.0.1
 */
function pls_get_textdomain() {

    global $placester_spine;

    if ( empty( $placester_spine->textdomain ) )
        $placester_spine->textdomain = sanitize_key( apply_filters( 'pls_textdomain', get_template() ) );

    return $placester_spine->textdomain;
}

/**
 * Defines and returns a prefix.
 * 
 * @access public
 * @return void
 */
function pls_get_prefix() {

    global $placester_spine;

    if ( empty( $placester_spine->prefix ) )
        $placester_spine->prefix = apply_filters( 'pls_prefix', get_template() );

    return $placester_spine->prefix;
}

/**
 * Adds contextual action hooks to the theme. This allows users to easily add context-based content 
 * without having to know how to use WordPress conditional tags. The theme handles the logic. Currently it 
 * generates hooks of the [theme_name]_[$tag] form.
 *
 * Major props to Ptah Dunbar for the do_atomic() function.
 * @link http://ptahdunbar.com/wordpress/smarter-hooks-context-sensitive-hooks
 *
 * @since 0.0.1
 * @param string $tag Usually the location of the hook but defines what the base hook is.
 * @param mixed $arg,... Optional additional arguments which are passed on to the functions hooked to the action.
 */
function pls_do_atomic( $tag = '', $arg = '' ) {

	if ( empty( $tag ) )
		return false;

	/** Get the theme name. */
	$pre = pls_get_prefix();

	/** Get the args passed into the function and remove $tag. */
	$args = func_get_args();
	array_splice( $args, 0, 1 );

    // echo "<div style='border: 1px solid green; margin: 5px;'><code>{$pre}_{$tag}</code>"; /** TODO Developer mode */

	/** Do actions on the basic hook. */
	do_action_ref_array( "{$pre}_{$tag}", $args );

    // echo "</div>"; /** TODO Developer mode */
}

/**
 * Adds contextual filter hooks to the theme. This allows users to easily filter context-based content 
 * without having to know how to use WordPress conditional tags. The theme handles the logic. Currently it 
 * generates filters of the [theme_name]_[$tag] form.
 *
 * @since 0.0.1
 * @param string $tag Usually the location of the hook but defines what the base hook is.
 * @param mixed $value The value on which the filters hooked to $tag are applied on.
 * @param mixed $var,... Additional variables passed to the functions hooked to $tag.
 * @return mixed $value The value after it has been filtered.
 */
function pls_apply_atomic( $tag = '', $value = '' ) {

	if ( empty( $tag ) )
		return false;

	/* Get theme prefix. */
	$pre = pls_get_prefix();

	/* Get the args passed into the function and remove $tag. */
	$args = func_get_args();
	array_splice( $args, 0, 1 );

	/* Apply filters on the basic hook. */
	$value = $args[0] = apply_filters_ref_array( "{$pre}_{$tag}", $args );

    // return pls_h_div(
        // pls_h(
            // 'code',
            // "{$pre}_{$tag}"
        // ) . $value,
        // array( 'style' => 'border: 1px solid blue; margin: 5px;')
    // ); /** TODO Developer mode */

	/* Return the final value once all filters have been applied. */
	return $value;
}

/**
 * Verifies if the there is a problem with retrieving data from the plugin. 
 *
 * Altough this information ca be determined by accessing the $placester_spine 
 * global directly, this method is prefered due to its upgragrade compatibility.
 * 
 * @global object $placester_spine The global Placester Spine object.
 * @returns mixed Returns false if there is no problem, 'no_api_key',
 * 'no_plugin', or 'timeout' if there is.
 * @since 0.0.1
 */
function pls_has_plugin_error() {

    global $placester_spine;

    if ( isset( $placester_spine->has_plugin_error ) ) 
        return $placester_spine->has_plugin_error;

    return false;
}

/**
 * Returns a tailored placeholder message if there is a problem connecting 
 * to the plugin.
 * 
 * @param string $context Optional. Used to display the context of the error. 
 * Usually the __FUNCTION__.
 * @access public
 * @return void
 * @since 0.0.1
 */
function pls_get_no_plugin_placeholder( $context = '' ) {

    $plugin_error = pls_has_plugin_error();

    if ( $plugin_error ) {

        $messages = array(
            'no_api_key' => __( 'You must add a valid API key to the Placester plugin to use this feature.', pls_get_textdomain() ),
            'no_plugin' => __( 'You must activate the Placester plugin to use this feature.', pls_get_textdomain() ),
            'timeout' => __( 'The Placester API connection timed out.', pls_get_textdomain() ) 
        );

        $context = empty( $context ) ? __( 'ERROR', pls_get_textdomain() ) : $context;

        $css_class = str_replace( '_', '-', $plugin_error );

        return pls_h(
            'div',
            array( 'class' => "pls-plugin-error pls-{$css_class}", 'style' => "padding: 5px; border: 1px solid red; margin: 5px 0; clear: both; color: red;" ),
            "<code>{$context}</code>: {$messages[$plugin_error]}"
        );
    }
}

/**
 * Dynamic element to wrap the site title in. If it is the front page, wrap it in an <h1> element. One other 
 * pages, wrap it in a <div> element. 
 *
 * @param bool $echo Default true. Wether to return or echo.
 * @returns string The description html.
 * @since 0.0.1
 */
function pls_site_title( $echo = true ) {

	$tag = ( is_front_page() ) ? 'h1' : 'div';

	if ( $title = get_bloginfo( 'name' ) )
		$title = '<' . $tag . ' id="site-title"><a href="' . home_url() . '" title="' . esc_attr( $title ) . '" rel="home"><span>' . $title . '</span></a></' . $tag . '>';

    $title = pls_apply_atomic( 'site_title', $title );

    if ( $echo )
        echo $title;
    else 
        return $title;
}

/**
 * Dynamic element to wrap the site description in. If it is the front page, wrap it in an <h2> element.  
 * On other pages, wrap it in a <div> element.
 *
 * @param bool $echo Default true. Wether to return or echo.
 * @returns string The description html.
 * @since 0.0.1
 */
function pls_site_description( $echo = true ) {

	$tag = ( is_front_page() ) ? 'h2' : 'div';

	if ( $desc = get_bloginfo( 'description' ) )
		$desc = "\n\t\t\t" . '<' . $tag . ' id="site-description"><span>' . $desc . '</span></' . $tag . '>' . "\n";

    $desc = pls_apply_atomic( 'site_description', $desc );

    if ( $echo )
        echo $desc;
    else 
        return $desc;
}

/**
 * Gets a filterable document title.
 *
 * @param bool $echo Default true. Wether to return or echo.
 * @returns string The description html.
 * @since 0.0.1
 */
function pls_document_title( $echo = true ) {

    $doc_title = wp_title('&laquo;', false, 'right') . get_bloginfo( 'name', 'display' ); 

    $doc_title = pls_apply_atomic( 'document_title', $doc_title );

    if ( $echo )
        echo $doc_title;
    else 
        return $doc_title;
}
