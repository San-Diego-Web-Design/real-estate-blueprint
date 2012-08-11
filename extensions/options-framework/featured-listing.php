<?php 
PLS_Featured_Listing_Option::register();
class PLS_Featured_Listing_Option {

	function register () {
		add_action('wp_ajax_list_options', array(__CLASS__, 'get_listings' ));
	}

	function init ( $params = array() ) {
		//pls_dump($params);
		ob_start();
		?>
			<div class="featured-listings-wrapper">
				<div class="head">
					<h3>Featured Listings</h3>
					<button class="featured-listings" id="<?php echo $params['value']['id'] ?>">Pick featured listings</button>
				</div>
				
				<div class="featured-listings" id="<?php echo $params['option_name'] ?>">
					<?php if ( is_array($params['val']) ): ?>
						<ul>
							<?php foreach ($params['val'] as $id => $address): ?>
							<li>
								<div id="pls-featured-text" ref="<?php echo $id ?>"><?php echo $address ?></div>
								<input type="hidden" name="<?php echo $params['option_name'] . '[' . $params['value']['id'] . '][' . $id . ']' ?>=" value="<?php echo $address ?>">
							</li>
							<?php endforeach ?>
						</ul>	
					<?php else: ?>
						<p>You haven't set any featured listings yet. Currently, a random selection of listings are being displayed until you pick some. If you previously picked listings, and now they are missing, it's because you (or your MLS), has marked them inactive, sold, rented, or they've been deleted.</p>
					<?php endif ?>
					
				</div>
				
			</div>
			
		<?php
		return ob_get_clean();
	}

	function load ( $params = array() ) {
		ob_start();
			extract( $params );
			include( trailingslashit( PLS_OPTRM_DIR ) . 'views/featured-listings.php' );
		echo ob_get_clean();
	}

	function get_filters ( $params = array() ) {
		ob_start();
			extract( $params );
			include( trailingslashit( PLS_OPTRM_DIR ) . 'views/featured-listings-filters.php' );
		echo ob_get_clean();	
	}

	function get_datatable ( $params = array() ) {
		ob_start();
			extract( $params );
			include( trailingslashit( PLS_OPTRM_DIR ) . 'views/featured-listings-datatable.php' );
		echo ob_get_clean();	
	}

	function get_listings () {
		$response = array();
		//exact addresses should be shown. 
		$_POST['address_mode'] = 'exact';

		// Sorting
		$columns = array('location.address');
		$_POST['sort_by'] = $columns[$_POST['iSortCol_0']];
		$_POST['sort_type'] = $_POST['sSortDir_0'];
		
		// text searching on address
		$_POST['location']['address'] = @$_POST['sSearch'];
		$_POST['location']['address_match'] = 'like';

		// Pagination
		$_POST['limit'] = $_POST['iDisplayLength'];
		$_POST['offset'] = $_POST['iDisplayStart'];		
		
		// Get listings from model
		$api_response = PL_Listing::get($_POST);
		
		// build response for datatables.js
		$listings = array();
		foreach ($api_response['listings'] as $key => $listing) {
			$listings[$key][] = $listing['location']['address'] . ', ' . $listing['location']['locality'] . ' ' . $listing['location']['region']; 
			$listings[$key][] = '<a id="pls_add_option_listing" href="#" ref="'.$listing['id'].'">Make Featured</a>';
		}

		// Required for datatables.js to function properly.
		$response['sEcho'] = $_POST['sEcho'];
		$response['aaData'] = $listings;
		$response['iTotalRecords'] = $api_response['total'];
		$response['iTotalDisplayRecords'] = $api_response['total'];
		echo json_encode($response);

		//wordpress echos out a 0 randomly. die prevents it.
		die();
	}

}

/*

<div class="featured-listing-search" id="featured-listing-search-<?php echo $value['id']; ?>">
				<div class="fls-address">
					<select name="<?php echo $value['id']; ?>" <?php echo $iterator ? 'ref="' . $iterator. '"' : '' ?> class="fls-address-select" id="fls-select-address"></select><div id="search_message" style="display:none; margin-top: 23px; font-weight: bold;">Searching...</div>
					<input type="submit" name="<?php echo $value['id']; ?>" value="Add Listing" class="fls-add-listing button <?php echo $for_slideshow ? 'for_slideshow' : '' ?>" id="add-listing-<?php echo $value['id']; ?>">	
					<input type="hidden" value="<?php echo esc_attr( $option_name . '[' . $value['id'] . ']' ) ?>" id="option-name">	
				</div>

				<h4 class="heading">Featured Listings</h4>
				<div class="fls-option">
					<div class="controls">
						<ul name="<?php echo $value['id']; ?>" id="fls-added-listings">
							<?php if ( isset($val) && !empty($val) && ( !isset($val['type']) || ( isset($val['type']) && $val['type'] == 'listing') ) ): ?>
								<?php foreach ($val as $key => $text): ?>
									<?php if ($key == 'type' || $key == 'html' || $key == 'image' || $key == 'link'): ?>
										<?php continue; ?>
									<?php endif ?>
									<li style='float:left; list-style-type: none;'>
										<div id='pls-featured-text' style='width: 200px; float: left;'>
											<?php echo $text ?>
										</div>
										<a style='float:left;' href='#' id='pls-option-remove-listing'>Remove</a>
										<?php if ($iterator == false): ?>
											<input type='hidden' name='<?php echo esc_attr( $option_name . '[' . $value['id'] . '][' . $key . ']=' ) ?>' value='<?php echo $text ?>' />	
										<?php else: ?>
											<input type='hidden' name='<?php echo esc_attr( $option_name . '[' . $value['id'] . ']['.$iterator.'][' . $key . ']=' ) ?>' value='<?php echo $text ?>' />	
										<?php endif ?>
										
									</li>
								<?php endforeach ?>
							<?php endif ?>
						</ul>
					</div>
					<div class="clear"></div>
				</div>
			</div>

			*/