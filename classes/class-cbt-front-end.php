<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CBT_Front_End {
	
	public function wp_footer() {
		global $cbt_main_class;
		$token = $cbt_main_class->settings->get_option( 'token' );
		
		if ( empty( $token ) ) {
            return;
        }
		
		?>
		<script type='text/javascript'>
			var cbtCustomer = '<?php echo $token; ?>';
			var locationRoot = (('https:' == document.location.protocol) ? 'https://' : 'http://');
			var newElem = document.createElement('script');
			newElem.setAttribute('src', locationRoot+'widget.callbacktracker.com/tracker/'+cbtCustomer);
			newElem.setAttribute('type', 'text/javascript');
			document.getElementsByTagName('head')[0].appendChild(newElem);
		</script>
	<?php
	}

	public function __construct() {		
		add_action( 'wp_footer', array( &$this, 'wp_footer' ), 80 );
	}
}
