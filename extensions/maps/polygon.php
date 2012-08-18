<?php 

class PLS_Map_Polygon extends PLS_Map {

	function polygon($listings = array(), $map_args = array(), $marker_args = array()) {
		$map_args = self::process_defaults($map_args);
		self::make_markers($listings, $marker_args, $map_args);
		extract($map_args, EXTR_SKIP);
		wp_enqueue_script('google-maps', 'http://maps.googleapis.com/maps/api/js?sensor=false');
		$polygon_html = '';
		if(WP_DEBUG !== true) {
			$cache = new PL_Cache('Map Polygon');
			// Doesn't seem to always be an array
			if(!is_array($listings)) {
				$listings_arr = array($listings);
			}
			else {	
				$listings_arr = $listings;
			}

			if($polygon_html_cached = $cache->get(array_merge($listings_arr, $map_args, $marker_args))) {
				$polygon_html = $polygon_html_cached;
			}
		}
		if($polygon_html === '') {
			ob_start();
		?>

		  <script src="<?php echo trailingslashit( PLS_JS_URL ) . 'libs/google-maps/text-overlay.js' ?>"></script>
			<?php echo self::get_lifestyle_controls($map_args); ?>
		<?php
			$polygon_html = ob_get_clean();
			if(WP_DEBUG !== true) {
				$cache->save($polygon_html);
			}
		}
		return $polygon_html;
	}

}