<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.ignitro.com/
 * @since      1.0.0
 *
 * @package    Flex_Maps
 * @subpackage Flex_Maps/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Flex_Maps
 * @subpackage Flex_Maps/includes
 * @author     Ignitro <plugin@ignitro.com>
 */

class Flex_Maps {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Flex_Maps_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

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
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
    $this->define('FLEX_MAPS_VERSION', '1.0');
    $this->define('FLEX_MAPS_PLUGIN_DIR', plugin_dir_path( dirname( __FILE__ ) ) );
    $this->define('FLEX_MAPS_PLUGIN_PATH', plugin_dir_url( dirname( __FILE__ ) ) );
    
    $this->define('FLEX_MAPS_BROWSER_API_KEY', get_option('flex_maps_google_maps_browser_key', false));
    $this->define('FLEX_MAPS_SERVER_API_KEY', get_option('flex_maps_google_maps_server_key', false));
    $this->define('FLEX_MAPS_DEFAULT_RADIUS', get_option('flex_maps_radius', 25) ?: 25);

		$this->plugin_name = 'flex-maps';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Flex_Maps_Loader. Orchestrates the hooks of the plugin.
	 * - Flex_Maps_i18n. Defines internationalization functionality.
	 * - Flex_Maps_Admin. Defines all hooks for the admin area.
	 * - Flex_Maps_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

    /**
     * includes the helper functions to get data across site
     */
    require_once FLEX_MAPS_PLUGIN_DIR . 'includes/api/helper-functions.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once FLEX_MAPS_PLUGIN_DIR . 'includes/class-flex-maps-loader.php';

		/**
     * The class responsible for defining internationalization functionality
     * of the plugin.
     */
    require_once FLEX_MAPS_PLUGIN_DIR . 'includes/class-flex-maps-i18n.php';

    /**
     * The class responsible for defining the functionality needed getting data from Google Maps API
     */
    require_once FLEX_MAPS_PLUGIN_DIR . 'includes/class-flex-maps-google-maps.php';

		/**
     * The class responsible for defining all actions that occur in the admin area.
     */
    require_once FLEX_MAPS_PLUGIN_DIR . 'admin/class-flex-maps-admin.php';

    /**
     * The class responsible for defining all custom post types that occur in the admin area.
     */
    require_once FLEX_MAPS_PLUGIN_DIR . 'admin/includes/class-flex-maps-post-types.php';

    /**
     * The following classes loads all field groups needed
     */
    require_once FLEX_MAPS_PLUGIN_DIR . 'admin/includes/fields/class-flex-maps-fields.php';
    require_once FLEX_MAPS_PLUGIN_DIR . 'admin/includes/fields/class-flex-maps-single-location-fields.php';
    require_once FLEX_MAPS_PLUGIN_DIR . 'admin/includes/fields/class-flex-maps-setting-fields.php';
    require_once FLEX_MAPS_PLUGIN_DIR . 'admin/includes/fields/class-flex-maps-global-setting-fields.php';

    /**
     * The following classes load and modifies the global map settings
     */
    require_once FLEX_MAPS_PLUGIN_DIR . 'admin/includes/class-flex-maps-global-settings.php';

    /**
     * The following classes load and modifies the map settings post type
     */
    require_once FLEX_MAPS_PLUGIN_DIR . 'admin/includes/class-flex-maps-settings.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once FLEX_MAPS_PLUGIN_DIR . 'public/class-flex-maps-public.php';

		$this->loader = new Flex_Maps_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Flex_Maps_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Flex_Maps_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Flex_Maps_Admin( $this->get_plugin_name(), $this->get_version() );
    $global_settings = new Flex_Maps_Global_Setting();
    $settings = new Flex_Maps_Settings($this->get_plugin_name(), $this->get_version());

    /* Admin */
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

    $this->loader->add_action( 'init', $plugin_admin, 'register_post_types' );
    $this->loader->add_action( 'acf/init', $plugin_admin, 'register_custom_fields' );

    /* Global Settings */
    $this->loader->add_action( 'acf/init', $global_settings, 'register_settings_page' );

    /* Map Settings */
    $this->loader->add_action( 'add_meta_boxes', $settings, 'example_map_section' );
    $this->loader->add_filter( 'acf/load_field/key=fm_field_rule_meta_key', $settings, 'load_fm_meta_keys');

    $this->loader->add_filter( 'acf/load_field/key=fm_search_taxonomy_param_list', $settings, 'maybe_disable_taxonomy_filter', 5);
    $this->loader->add_filter( 'acf/prepare_field/key=fm_custom_param_field', $settings, 'load_param_values');
    $this->loader->add_filter( 'acf/prepare_field/key=fm_taxonomy_param_field', $settings, 'load_param_values');
    $this->loader->add_filter( 'acf/validate_value/key=fm_custom_param_field', $settings, 'validate_field', 10, 4 );
    $this->loader->add_filter( 'acf/validate_value/key=fm_taxonomy_param_field', $settings, 'validate_field', 10, 4 );
    $this->loader->add_filter( 'acf/validate_value/key=fm_custom_param_name', $settings, 'validate_field', 10, 4 );
    $this->loader->add_filter( 'acf/validate_value/key=fm_taxonomy_param_name', $settings, 'validate_field', 10, 4 );

    $this->loader->add_filter( 'acf/prepare_field/key=fm_taxonomy_param_value', $settings, 'load_fm_tax_values');
    
    $this->loader->add_action( 'acf/render_field/type=repeater', $settings, 'rule_group_repeater_before', 5);
    $this->loader->add_action( 'acf/render_field/key=fm_repeater_rule_group', $settings, 'rule_group_repeater_after', 15);

    $this->loader->add_filter( 'wp_ajax_load_fm_rule_tax_values', $settings, 'ajax_load_fm_rule_tax_values');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Flex_Maps_Public( $this->get_plugin_name(), $this->get_version() );
    
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

    $this->loader->add_action( 'plugins_loaded', $plugin_public, 'register_shortcodes', 15 );

    $this->loader->add_action( 'wp_ajax_nopriv_flex_maps_get_locations', $plugin_public, 'get_locations' );
    $this->loader->add_action( 'wp_ajax_flex_maps_get_locations', $plugin_public, 'get_locations' );

    $this->loader->add_action( 'pre_get_posts', $plugin_public, 'query_custom_fields', 10, 2 );

    $this->loader->add_filter( 'script_loader_tag', $plugin_public, 'add_defer_async_attr', 10, 3 );
    $this->loader->add_filter( 'posts_clauses', $plugin_public, 'modify_search_query', 10, 2 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Flex_Maps_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

  /**
   * Defines a constant if doesnt already exist.
   *
   * @date  3/5/17
   * @since 1.0.0
   *
   * @param string $name The constant name.
   * @param mixed $value The constant value.
   * @return  void
   */
  function define( $name, $value = true ) {
    if( !defined($name) ) {
      define( $name, $value );
    }
  }

}
