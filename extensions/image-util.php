<?php 

/*
Based heavily on Wes Edling's chaching/scaling script, modified to work properly in our context.
Modifications include:
	 - fixing the way urls are handled to remove get vars in the image name
	 - rewrote to use GD for image manipulation rather then ImageMagic

TODO: 
	- break this out into reusable functions so the logic is more obvious
	- performance testing / optimization.

Here's Wes' requested attribution for the modified "resize" function:

function by Wes Edling .. http://joedesigns.com
feel free to use this in any project, i just ask for a credit in the source code.
a link back to my site would be nice too.
*/

// Include the GD image manipulation library. 
include(trailingslashit ( PLS_EXT_DIR ) . 'image-util/image-resize-writer.php');


class PLS_Image {
	
	static function init() {
	
        self::enqueue();
		
	}

    static private function enqueue()
	{

        $image_util_support = get_theme_support( 'pls-image-util' );

		if ( !wp_script_is('pls-image-util-fancybox' , 'registered') ) {
	        wp_register_script( 'pls-image-util-fancybox', trailingslashit( PLS_EXT_URL ) . 'image-util/fancybox/jquery.fancybox-1.3.4.pack.js' , array( 'jquery' ), NULL, true );
		}

		if ( !wp_script_is('pls-image-util-fancybox-default-settings' , 'registered') ) {
        	wp_register_script( 'pls-image-util-fancybox-default-settings', trailingslashit( PLS_EXT_URL ) . 'image-util/fancybox/default-settings.js' , array( 'jquery' ), NULL, true );
		}
		
		if ( !wp_style_is('pls-image-util-fancybox-style' , 'registered') ) {
        	wp_register_style( 'pls-image-util-fancybox-style', trailingslashit( PLS_EXT_URL ) . 'image-util/fancybox/jquery.fancybox-1.3.4.css' );
		}

        if ( is_array( $image_util_support ) ) {
            if ( in_array( 'fancybox', $image_util_support[0] ) ) {
              	if ( !wp_script_is('pls-image-util-fancybox' , 'queue') ) {
	  				wp_enqueue_script( 'pls-image-util-fancybox' );
              	}

				if ( !wp_script_is('pls-image-util-fancybox-default-settings' , 'queue') ) {
	                wp_enqueue_script( 'pls-image-util-fancybox-default-settings' );
				}

				if ( !wp_style_is('pls-image-util-fancybox-style' , 'queue') ) {
	                wp_enqueue_style( 'pls-image-util-fancybox-style' );
				}
            }
            return;
        }
    }

	static function load ( $old_image = '', $args = null )
	{
		$new_image = false;

		if (isset($args['fancybox']) && $args['fancybox']) {
			unset($args['fancybox']);
		}

        /** Define the default argument array. */
        $defaults = array(
            'resize' => array(
            	'w' => false,
				'h' => false,
				'method' => 'auto'
            ),
            'as_html' => false,
            'as_url' => true,
			'fancybox' => array(
				'trigger_class' => 'pls_use_fancy',
				'additional_classes' => '' 
			),
        );

        /** Merge the arguments with the defaults. */
        $args = wp_parse_args( $args, $defaults );

		// something is wrong with wp_parse_args
		if ($args['resize'] && !isset($args['resize']['method'])) {
			$args['resize']['method'] = 'auto';
		}

		// use standard default image
		if ( $old_image === '' || empty($old_image)) {
			$old_image = trailingslashit(PLS_EXT_DIR) . "image-util/default-images/default.gif";
		}
		
		if ( $args['resize']['w'] && $args['resize']['h'] ) {
			$new_image = self::resize($old_image, array('w' => $args['resize']['w'], 'h' => $args['resize']['h'], 'method' => $args['resize']['method']));
		}
		
		if ( $args['fancybox'] || $args['as_html']) {
			if ($new_image) {
				$new_image = self::as_html($old_image, $new_image, array('fancybox' => $args['fancybox']));
			} else {
				$new_image = self::as_html($old_image, null, array('fancybox' => $args['fancybox'], 'resize' => array('w' => $args['resize']['w'], 'h' => $args['resize']['h'])));
			}
		}

		// return the new image if we've managed to create one
		if ($new_image) {
			return $new_image;
		} else {
			return $old_image;
		}

	}
	
	static private function as_html ($old_image, $new_image = false, $args )
	{

		if ($args['fancybox']) {

			/** Define the default argument array. */
	        $fancy_defaults = array(
				'fancybox' => array(
					'trigger_class' => 'pls_use_fancy',
					'additional_classes' => '' 
				),
				'resize' => array(
					'w' => false,
					'h' => false
					)
	        );

	        /** Merge the arguments with the defaults. */
	        $fancy_args = wp_parse_args( $args, $fancy_defaults );

			ob_start();
			// our basic fancybox html
			?>
				<a class="<?php echo $fancy_args['fancybox']['trigger_class'] . ' ' .  $fancy_args['fancybox']['additional_classes']; ?>" href="<?php echo $old_image; ?>" >
					<img style="width: <?php echo $fancy_args['resize']['w']; ?>px; height: <?php echo $fancy_args['resize']['w']; ?>px; overflow: hidden;" src="<?php echo $new_image ? $new_image : $old_image; ?>" alt="" />
				</a>
			<?php
			
			return trim( ob_get_clean() );
		} else {


			if ($new_image) {
				return '<img src="' . $new_image . '" />';				
			} else {
				
				$fancy_defaults = array(
				'fancybox' => array(
					'trigger_class' => 'pls_use_fancy',
					'additional_classes' => '' 
				),
				'resize' => array(
					'w' => false,
					'h' => false
					)
	        	);
				$fancy_args = wp_parse_args( $args, $fancy_defaults );

				ob_start();
				?>
				<img style="width: <?php echo $fancy_args['resize']['w']; ?>px; height: <?php echo $fancy_args['resize']['h']; ?>px; overflow: hidden;" src="<?php echo $old_image ?>" <?php echo $fancy_args['resize']['w'] ? ('width=' . $fancy_args['resize']['w']) : '' ?> <?php echo $fancy_args['resize']['h'] ? ('height=' . $fancy_args['resize']['h']) : '' ?> />
				<?php
				return trim(ob_get_clean());
				
			}

		}
		
	}

	static private function resize($imagePath,$opts=null)
	{
		
		# start configuration
		$cacheFolder = trailingslashit ( PLS_EXT_DIR ) . 'image-util/cache/'; # path to your cache folder, must be writeable by web server
		$remoteFolder = trailingslashit ( PLS_EXT_DIR ) . 'image-util/remote/'; # path to the folder you wish to download remote images into
		$quality = 90; # image quality to use for ImageMagick (0 - 100)

		$cache_http_minutes = 7200; 	# cache downloaded http images 20 minutes

		// our api auto timstamps urls with a pid request, we just need to dump any get vars off the end
		// of the request so they don't get saved in the image name (and then url encoded - which was a _nightmare_) 

		$imagePath = explode('?', (string) $imagePath);
		$imagePath = $imagePath[0];

		## you shouldn't need to configure anything else beyond this point
		$purl = parse_url($imagePath);
		$finfo = pathinfo($imagePath);

		// if theres no path, it's not an image, or it might be...
		// but we'll get in trouble trying to fool with it.
		if (is_array($finfo) && isset($finfo['extension'])) {
			$ext = $finfo['extension'];
		} else {
			return false;
		}
		

		# check for remote image..
		if(isset($purl['scheme']) && $purl['scheme'] == 'http'):
			# grab the image, and cache it so we have something to work with..
			list($filename) = explode('?',$finfo['basename']);
			$local_filepath = $remoteFolder.$filename;
			$download_image = true;
			if(file_exists($local_filepath)):
				if(filemtime($local_filepath) < strtotime('+'.$cache_http_minutes.' minutes')):
					$download_image = false;
				endif;
			endif;
			if($download_image == true):
				$img = file_get_contents($imagePath);
				file_put_contents($local_filepath,$img);
			endif;
			$imagePath = $local_filepath;
		endif;

		if(file_exists($imagePath) == false):
			$imagePath = $_SERVER['DOCUMENT_ROOT'].$imagePath;
			if(file_exists($imagePath) == false):
				return 'image not found';
			endif;
		endif;

		if(isset($opts['w'])): $w = $opts['w']; endif;
		if(isset($opts['h'])): $h = $opts['h']; endif;

		$filename = md5_file($imagePath);

		if(!empty($w) and !empty($h)):
			$newPath = $cacheFolder.$filename.'_w'.$w.'_h'.$h.(isset($opts['crop']) && $opts['crop'] == true ? "_cp" : "").(isset($opts['scale']) && $opts['scale'] == true ? "_sc" : "").'.'.$ext;
		elseif(!empty($w)):
			$newPath = $cacheFolder.$filename.'_w'.$w.'.'.$ext;	
		elseif(!empty($h)):
			$newPath = $cacheFolder.$filename.'_h'.$h.'.'.$ext;
		else:
			return false;
		endif;

		$create = true;

		if(file_exists($newPath) == true):
			$create = false;
			$origFileTime = date("YmdHis",filemtime($imagePath));
			$newFileTime = date("YmdHis",filemtime($newPath));
			if($newFileTime < $origFileTime):
				$create = true;
			endif;
		endif;

		if($create == true):
			if(!empty($w) and !empty($h)):
			
				// modified to use a GD based writer which has much stronger
				// support across wordpress users (as opposed to imagemagic)
				$resizeObj = new resize($imagePath);
				
				if ($resizeObj->status ) {
					$resizeObj -> resizeImage($w, $h, $opts['method']);
					$resizeObj -> saveImage($newPath, 80);
				} else {
					unset($resizeObj);
				}
				
			endif;
		endif;

		// pls_dump($newPath);
		// pls_dump(strpos($newPath, '/wp-content'));

		if (strpos($newPath, '/wp-content')) {
			$exploded_path = explode('/wp-content', $newPath);
			// pls_dump($exploded_path);
			return '/wp-content' . $exploded_path[1];
		} else {
			return $newPath;
		}

		# return cache file path
		return str_replace(strtolower($_SERVER['DOCUMENT_ROOT']),'', strtolower($newPath));

	}
}
PLS_Image::init();
?>