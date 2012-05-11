<?php 

PLS_Taxonomy::init();
class PLS_Taxonomy {

	static $custom_meta = array();

	function init () {
		add_action('init', array(__CLASS__, 'metadata_customizations')); 
	}

	function get ($args = array()) {

		$signature = base64_encode(sha1(implode($args), true));
        $transient_id = 'pl_' . $signature;
        $transient = get_transient($transient_id);
        
        if ($transient) {
        	return $transient;
        } 

		extract(self::process_args($args), EXTR_SKIP);
		$subject = array();
		if ($street) {
			$subject += array('taxonomy' => 'street', 'term' => $street, 'api_field' => 'address');
		} elseif ($neighborhood) {
			$subject += array('taxonomy' => 'neighborhood', 'term' => $neighborhood, 'api_field' => 'neighborhood');
		} elseif ($zip) {
			$subject += array('taxonomy' => 'zip', 'term' => $zip, 'api_field' => 'postal');
		} elseif ($city) {
			$subject += array('taxonomy' => 'city', 'term' => $city, 'api_field' => 'locality');
		} elseif ($state) {
			$subject += array('taxonomy' => 'state', 'term' => $state, 'api_field' => 'region');
		}
		$term = get_term_by('slug', $subject['term'], $subject['taxonomy'], ARRAY_A );
		$custom_data = array();
		foreach (self::$custom_meta as $meta) {
			$custom_data[$meta['id']] = get_tax_meta($term['term_id'],$meta['id']);
		}
		$term = wp_parse_args($term, $custom_data);
		$term['api_field'] = $subject['api_field'];
		$term['listings'] = pls_get_listings( "limit=5&context=home&request_params=location[" . $term['api_field'] . "]=" . $term['name'] );
		
		$term['listing_photos'] = array();
		$listings_raw = PLS_Plugin_API::get_property_list("location[" . $term['api_field'] . "]=" . $term['name']);  
		$term['listings_raw'] = $listings_raw['listings'];
		$count = 0;
		if (isset($listings_raw['listings'])) {
			foreach ($listings_raw['listings'] as $key => $listing) {
				if (!empty($listing['images'])) {
					foreach ($listing['images'] as $image) {
						if ($count > $image_limit) {
							break;
						}
						$term['listing_photos'][] = array('full_address' => $listing['location']['full_address'], 'image_url' => $image['url'], 'listing_url' => $listing['cur_data']['url']);
						$count++;
					}
				}
			}
		}
		$term['polygon'] = PLS_Plugin_API::get_polygon_detail(array('tax' => $term['api_field'], 'slug' => $subject['term']));

	
		set_transient( $transient_id, $term , 3600 * 48 );

		return $term;
	}

	function get_links ($location) {
		$response = array();
		$neighborhoods = array('state' => false, 'city' => false, 'neighborhood' => false, 'zip' => false, 'street' => false);
		$api_translations = array('state' => 'region', 'city' => 'locality', 'neighborhood' => 'neighborhood', 'zip' => 'postal', 'street' => 'address');
		global $query_string;
		$args = wp_parse_args($query_string, $neighborhoods);
		foreach ($neighborhoods as $neighborhood => $value) {
			if ($args[$neighborhood]) {
				$response[ $location[$api_translations[$neighborhood]] ]  = get_term_link( $args[$neighborhood], $neighborhood );
			}
		}
		return $response;
	}

	function add_meta ($type, $id, $label) {
		if (in_array($type, array('text', 'textarea', 'checkbox', 'image', 'file', 'wysiwyg'))) {
			self::$custom_meta[] = array('type' => $type, 'id' => $id, 'label' => $label);
		} else {
			return false;
		}
		
	}

	function metadata_customizations () {
        include_once(PLS_Route::locate_blueprint_option('meta.php'));        
		
		//throws random errors if you aren't an admin, can't be loaded with admin_init...
        if (!is_admin()) {
        	return;	
        }
        
		$config = array('id' => 'demo_meta_box', 'title' => 'Demo Meta Box', 'pages' => array('state', 'city', 'zip', 'street', 'neighborhood'), 'context' => 'normal', 'fields' => array(), 'local_images' => false, 'use_with_theme' => false );
		$my_meta = new Tax_Meta_Class($config);
		foreach (self::$custom_meta as $meta) {
			switch ($meta['type']) {
				case 'text':
					$my_meta->addText($meta['id'],array('name'=> $meta['label']));
					break;
				case 'textarea':
					$my_meta->addTextarea($meta['id'],array('name'=> $meta['label']));
					break;
				case 'wysiwyg':
					$my_meta->addCheckbox($meta['id'],array('name'=> $meta['label']));
					break;
				case 'image':
					$my_meta->addImage($meta['id'],array('name'=> $meta['label']));
					break;
				case 'file':
					$my_meta->addFile($meta['id'],array('name'=> $meta['label']));
					break;				
				case 'checkbox':
					$my_meta->addCheckbox($meta['id'],array('name'=> $meta['label']));
					break;				
			}
		}
		$my_meta->Finish();
	}

	function process_args ($args) {
		$defaults = array(
        	
        );
        $args = wp_parse_args( $args, $defaults );
        return $args;
	}

//end of class
}