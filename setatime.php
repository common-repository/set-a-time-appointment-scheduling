<?php
/*
Plugin Name: Set a Time - Online Appointment Scheduling Software
Plugin URI: https://setatime.co/business/integrations/wordpress
Description: Add Set a Time into your WordPress website and accept appointments online from your site directly.
Version: 1.1.0
Author: Set a Time
Author URI: https://setatime.co
*/

class Set_a_Time {
	public function __construct() {
    // Hook into the admin menu
		add_action('admin_menu', [$this, 'create_sat_settings_page']);
		// add_action('admin_init', [$this, 'setup_sat_sections']);
		// add_action('admin_init', [$this, 'setup_sat_settings_fields']);
		wp_enqueue_style('sat-admin-style', plugins_url('assets/css/sat-style.css', __FILE__));
		add_shortcode('setatime', [$this, 'setatime_shortcode']);
		update_option('sat_width', '1000');
		update_option('sat_height', '500');
		add_filter( 'plugin_action_links', [$this, 'sat_plugin_settings_link'], 10, 2 );
	}

	public function create_sat_settings_page() {
    // Add the menu item and page
		$page_title = 'Set a Time Settings';
		$menu_title = 'Set a Time';
		$capability = 'manage_options';
		$slug = 'set-a-time';
		$callback = [$this, 'set_a_time_admin_page_settings'];
		$icon = esc_url(plugins_url('assets/images/set-a-time-icon.png', __FILE__));
		$position = 100;

		add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon, $position );
	}

	public function set_a_time_admin_page_settings() {
		if( $_POST['sat-form-updated'] === 'true' ){
			$this->handle_sat_form();
		} ?>
		<div class="wrap">
			<?php
			if(get_option('sat_domain') !== '') {
				echo '<h1 class="sat-heading">Get Shortcode</h1>';
				echo '<p class="sat-paragraph">Copy and paste the shortcode into any page or post and your booking form will show up.</p>';
				echo '<code>[setatime]</code>';
				echo '<br><br><br>';
			}
			?>

			<h1 class="sat-heading">Set a Time Settings</h1>
			<form method="post">
				<input type="hidden" name="sat-form-updated" value="true" />
				<?php wp_nonce_field( 'sat-form-update-form', 'sat-form' ); ?>
				<p class="sat-paragraph" style="margin-bottom:0;">Enter your Set a Time domain. Your Set a Time domain is your booking page URL (without the setatime.co at the beginning). Or you can find it by <a href="https://setatime.co/business/apps/wordpress" target="_blank" ref="noopener noreferrer">clicking here</a>.</p>
				<p class="sat-paragraph">For example: https://setatime.co/abcde - where "abcde" is the domain.</p>
				<?php
				echo 'Domain: <input name="set-a-time-domain" id="set-a-time-domain" type="text" value="'.get_option('sat_domain').'" /><br><br>';
				echo 'Booking Form Width (in px - Do not include "px"): <input name="set-a-time-width" id="set-a-time-width" type="number" placeholder="500" value="'.get_option('sat_width').'" /><br><br>';
				echo 'Booking Form Height (in px - Do not include "px"): <input name="set-a-time-height" id="set-a-time-height" type="number" placeholder="200" value="'.get_option('sat_height').'" />';
				submit_button();
				?>
			</form>
		</div>
	<?php }

	public function setup_sat_sections() {
		add_settings_section( 'setatime_domain_section', 'Set a Time Domain', false, 'set-a-time' );
	}

	public function setup_sat_settings_fields() {
		add_settings_field( 'set_a_time_domain_field', 'Domain', [$this, 'field_callback'], 'set-a-time', 'setatime_domain_section' );
	}

	public function field_callback($arguments) {
		echo '<input name="set-a-time-domain" id="set-a-time-domain" type="text" value="'.get_option('sat_domain').'" />';
		register_setting( 'set-a-time', 'set_a_time_domain_field' );
	}

	public function handle_sat_form() {
		if(!isset( $_POST['sat-form'] ) || ! wp_verify_nonce($_POST['sat-form'], 'sat-form-update-form')) { ?>
			<div class="error">
				<p>Sorry, your nonce was not correct. Please try again.</p>
			</div> 
			<?php exit;
		} else {
			$satDomain = sanitize_text_field($_POST['set-a-time-domain']);
			if(!empty($_POST['set-a-time-width'])) {
				$satWidth = sanitize_text_field($_POST['set-a-time-width']);
			} else {
				$satWidth = '1000';
			}
			if(!empty($_POST['set-a-time-height'])) {
				$satHeight = sanitize_text_field($_POST['set-a-time-height']);
			} else {
				$satHeight = '500';
			}
			update_option('sat_domain', $satDomain);
			update_option('sat_width', $satWidth);
			update_option('sat_height', $satHeight);
			?>
			<div class="updated">
				<p>Your settings were saved!</p>
			</div>
		<?php }
	}

	function setatime_shortcode( $atts ) {
		$a = shortcode_atts(null, null);
		$satDomain = 'sat_domain';
		$satWidth = get_option('sat_width').'px';
		$satHeight = get_option('sat_height').'px';
		if(get_option($satDomain) !== '') {
			$iframe = '<iframe src="https://setatime.co/'.get_option($satDomain).'/" style="width:'.$satWidth.'; height:'.$satHeight.'; border:0;"></iframe>';
		} else {
			$iframe = 'Please go into your <a href="'.get_option('siteurl').'/wp-admin/admin.php?page=set-a-time">settings</a> and enter your domain.';
		}
		return $iframe;
	}

	public function sat_plugin_settings_link( $links, $file ) {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) )
			return $links;

		static $plugin;

		$plugin = plugin_basename( __FILE__ );

		if ( $file == $plugin ) {
			$settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php' ) . '?page=set-a-time', __( 'Settings', 'set-a-time' ) );

			array_unshift( $links, $settings_link );
		}

		return $links;
	}
}

new Set_a_Time();
?>