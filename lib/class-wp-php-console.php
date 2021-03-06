<?php

/**
 * WP PHP Console Plugin Core Class
 *
 * @link       https://github.com/nekojira/wp-php-console
 * @since      1.0.0
 *
 * @package    WP_PHP_Console
 * @subpackage WP_PHP_Console/lib
 */

/**
 * WP PHP Console.
 *
 * @since      1.0.0
 * @package    WP_PHP_Console
 * @subpackage WP_PHP_Console/lib
 */
class WP_PHP_Console {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Options.
	 *
	 * @since    1.0.0
	 * @access   protected
	 */
	protected $options;

	/**
	 * Construct.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'wp-php-console';
		$this->version = '1.2.3';
		$this->options = get_option( 'wp-php-console' );

		add_action( 'plugins_loaded',   array( $this, 'set_locale' ) );
		add_action( 'admin_menu',       array( $this, 'register_settings_page' ) );
		add_action( 'wp_loaded',   array( $this, 'init' ) );
	}

	/**
	 * Set plugin text domain.
	 *
	 * @since   1.0.0
	 */
	public function set_locale() {
		load_plugin_textdomain(
			$this->plugin_name,
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	
	/**
	 * Plugin Settings menu.
	 *
	 * @since   1.0.0
	 */
	public function register_settings_page() {

		add_options_page(
			__( 'WP PHP Console', $this->plugin_name ),
			__( 'PHP Console', $this->plugin_name ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'settings_page' )
		);

		add_action( 'admin_init', array( $this, 'register_settings'  ) );
	}

	/**
	 * Register plugin settings.
	 *
	 * @since   1.0.0
	 */
	function register_settings() {

		register_setting(
			'wp_php_console',
			'wp_php_console',
			array( $this, 'sanitize_field' )
		);

		add_settings_section(
			'wp_php_console',
			__( 'Settings', $this->plugin_name ),
			array( $this, 'settings_info' ),
			$this->plugin_name
		);

		add_settings_field(
			'password',
			__( 'Password', $this->plugin_name ),
			array( $this, 'password_field' ),
			$this->plugin_name,
			'wp_php_console'
		);

		add_settings_field(
			'ssl',
			__( 'Allow only on SSL', $this->plugin_name ),
			array( $this, 'ssl_field' ),
			$this->plugin_name,
			'wp_php_console'
		);

		add_settings_field(
			'ip',
			__( 'Allowed IP Masks', $this->plugin_name ),
			array( $this, 'ip_field' ),
			$this->plugin_name,
			'wp_php_console'
		);

	}

	/**
	 * Settings Page Password field.
	 *
	 * @since   1.0.0
	 */
	public function password_field() {

		printf (
			'<input type="password" id="wp-php-console-password" name="wp_php_console[password]" value="%s" />',
			isset( $this->options['password'] ) ? esc_attr( $this->options['password'] ) : ''
		);
		echo '<label for="wp-php-console-ip">' . __( 'Required', $this->plugin_name ) . '</label>';
		echo '<br />';
		echo '<small class="description">' . __( 'The password for eval terminal. If empty, the plugin will not work.', $this->plugin_name ) . '</small>';
	}

	/**
	 * Settings Page SSL option field.
	 *
	 * @since   1.0.0
	 */
	public function ssl_field() {

		$ssl = isset( $this->options['ssl'] ) ? esc_attr( $this->options['ssl']) : '';

		printf (
			'<input type="checkbox" id="wp-php-console-ssl" name="wp_php_console[ssl]" value="1" %s /> ',
			$ssl ? 'checked="checked"' : ''
		);
		echo '<label for="wp-php-console-ssl">' . __( 'Yes (optional)', $this->plugin_name ) . '</label>';
		echo '<br />';
		echo '<small class="description">' . __( 'Choose if you want the eval terminal to work only on a SSL connection.', $this->plugin_name ) . '</small>';
	}

	/**
	 * Settings page IP Range field.
	 *
	 * @since   1.0.0
	 */
	public function ip_field() {

		printf (
			'<input type="text" class="regular-text" id="wp-php-console-ip" name="wp_php_console[ip]" value="%s" /> ',
			isset( $this->options['ip'] ) ? esc_attr( $this->options['ip']) : ''
		);
		echo '<label for="wp-php-console-ip">' . __( 'IP addresses (optional)', $this->plugin_name ) . '</label>';
		echo '<br />';
		echo '<small class="description">' . __( 'You may specify an IP address (e.g. 192.169.1.50), a range of addresses (192.168.*.*) or multiple addresses, comma separated (192.168.10.25,192.168.10.28) to grant access to eval terminal.', $this->plugin_name ) . '</small>';
	}

	/**
	 * Sanitize user input in settings page.
	 *
	 * @since   1.0.0
	 *
	 * @param   string  $input  user input
	 *
	 * @return  array   sanitized inputs
	 */
	public function sanitize_field( $input ) {

			$sanitized_input = array();

			if ( isset( $input['password'] ) )
				$sanitized_input['password'] = sanitize_text_field( $input['password'] );

			if ( isset( $input['ssl'] ) )
				$sanitized_input['ssl'] = ! empty( $input['ssl'] ) ? 1 : '';

			if ( isset( $input['ip'] ) )
				$sanitized_input['ip'] = sanitize_text_field( $input['ip'] );

			return $sanitized_input;
	}

	/**
	 * Settings page.
	 *
	 * @since   1.0.0
	 */
	function settings_page() {

		$this->options = get_option( 'wp_php_console' );
		?>
		<div class="wrap">
			<h2><?php _e( 'WP PHP Console', $this->plugin_name ); ?></h2>
			<hr />
			<form method="post" action="options.php">
				<?php
				settings_fields( 'wp_php_console' );
				do_settings_sections( $this->plugin_name );
				submit_button();
				?>
			</form>
		</div>
		<hr />
		<p><?php _e( 'Like this plugin? Was it useful to you? Please consider a donation to support open source software development.', $this->plugin_name ); ?></p>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="GSTFUY3LMCA5W">
			<input type="image" src="https://www.paypalobjects.com/en_GB/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
			<img alt="" border="0" src="https://www.paypalobjects.com/it_IT/i/scr/pixel.gif" width="1" height="1">
		</form>
		<?php

	}

	public function settings_info() {

		?>
		<p><?php printf( _x( 'This plugin allows you to use %s within your WordPress installation for testing, debugging and development purposes.<br/>Usage instructions:', 'PHP Console', $this->plugin_name ), '<a href="https://github.com/barbushin/php-console" target="_blank">PHP Console</a>' ); ?></p>
		<ol>
			<li><?php printf( __( 'Make sure you have downloaded and installed %s.', $this->plugin_name ), '<a target="_blank" href="https://chrome.google.com/webstore/detail/php-console/nfhmhhlpfleoednkpnnnkolmclajemef">PHP Console extension for Google Chrome</a>' ); ?></li>
			<li><?php _e( 'Set a password for the eval terminal in the options below and hit `save changes`.', $this->plugin_name ); ?></li>
			<li><?php _e( 'Reload any page of your installation and click on the key icon in your Chrome browser address bar, enter your password and access the terminal.', $this->plugin_name ); ?></li>
			<li><?php _e( 'From the eval terminal you can execute any PHP or WordPress specific function, including functions from your plugins and active theme.', $this->plugin_name ); ?></li>
		</ol>
		<?php

	}

	/**
	 * Initialize PHP Console.
	 *
	 * @since   1.0.0
	 */
	public function init() {

		if ( ! class_exists( 'PhpConsole\Connector' ) )
			return;

		$options = get_option( 'wp_php_console' );

		$password = isset( $options['password'] ) ? $options['password'] : '';
		if ( ! $password )
			return;

		// Selectively remove slashes added by WordPress as expected by PhpConsole
		if(isset($_POST[PhpConsole\Connector::POST_VAR_NAME])) {
			$_POST[PhpConsole\Connector::POST_VAR_NAME] = stripslashes_deep($_POST[PhpConsole\Connector::POST_VAR_NAME]);
		}

		$connector = PhpConsole\Connector::getInstance();
		$connector->setPassword( $password );

		$handler = PhpConsole\Handler::getInstance();
		if ( PhpConsole\Handler::getInstance()->isStarted() != true )
			$handler->start();

		$enableSslOnlyMode = isset( $options['ssl'] ) ? ( ! empty( $options['ssl'] ) ? $options['ssl'] : '' ) : '';
		if ( $enableSslOnlyMode == true )
			$connector->enableSslOnlyMode();

		$allowedIpMasks = isset( $options['ip'] ) ? ( ! empty( $options['ip'] ) ? explode( ',', $options['ip'] ) : '' ) : '';
		if ( ! is_array( $allowedIpMasks ) && ! empty( $allowedIpMasks ) )
			$connector->setAllowedIpMasks( (array) $allowedIpMasks );

		$evalProvider = $connector->getEvalDispatcher()->getEvalProvider();

		$evalProvider->addSharedVar( 'uri', $_SERVER['REQUEST_URI'] );
		$evalProvider->addSharedVarReference( 'post', $_POST );

		// $evalProvider->disableFileAccessByOpenBaseDir();
		$openBaseDirs = array( ABSPATH, get_template_directory() );
		$evalProvider->addSharedVarReference( 'dirs', $openBaseDirs );
   	    $evalProvider->setOpenBaseDirs( $openBaseDirs );

		$connector->startEvalRequestsListener();

	}

}
