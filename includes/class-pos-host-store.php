<?php
/**
 * Store Setting Page
 *
 * @package WooCommerce_pos_host/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_Store Class
 */
class POS_HOST_Store {

	/**
	 * The single instance of the class.
	 *
	 * @var POS_HOST_Barcodes
	 */
	protected static $_instance = null;

	/**
	 * Main POS_HOST_Store Instance.
	 *
	 * Ensures only one instance of POS_HOST_Barcodes is loaded or can be loaded.
	 *
	 * @return POS_HOST_Barcodes Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {}

	public function display_store_page() {
            $current_user = wp_get_current_user();
            /* get the current_user's primary site,
             * must match its primary blog
             */
            if ( get_current_blog_id() != get_user_meta( $current_user->ID, 'primary_blog', true )){
                wp_die( __( 'Sorry, you are not allowed to manage the store.' ));
            };

            if ( ! current_user_can( 'manage_woocommerce_pos_host' ) ) {
                wp_die( __( 'Sorry, you are not allowed to manage the store.' ) );
            }
            
            /** WordPress Administration Bootstrap */
            //require_once( dirname( __FILE__ ) . '/admin.php' );

            /** WordPress Translation Installation API */
            require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );

            $title       = __( 'General Settings' );
            
            /* translators: Date and time format for exact current time, mainly about timezones, see https://secure.php.net/date */
            $timezone_format = _x( 'Y-m-d H:i:s', 'timezone date format' );

            $wp_http_referer = add_query_arg( 'page', POS_HOST()->store_menu_slug, get_admin_url('','admin.php') );

            wp_reset_vars( array( 'action' ) );
            $action = $GLOBALS['action'];
            if ( 'pos-host-store-update' == $action ) {
                             if ( ! check_admin_referer( 'update-store' ) ){
                                    wp_die( __( 'Sorry, something is wrong to manage the store.' ) );
                             }
                             /* options allow for update */
                             $options = array(
                                                    'blogname',
                                                    'blogdescription',
                                                    'gmt_offset',
                                                    'date_format',
                                                    'time_format',
                                                    'start_of_week',
                                                    'timezone_string',
                                                    'WPLANG',
                                            );

                            // Handle custom date/time formats.
                            if ( ! empty( $_POST['date_format'] ) && isset( $_POST['date_format_custom'] ) && '\c\u\s\t\o\m' == wp_unslash( $_POST['date_format'] ) ) {
                                    $_POST['date_format'] = $_POST['date_format_custom'];
                            }
                            if ( ! empty( $_POST['time_format'] ) && isset( $_POST['time_format_custom'] ) && '\c\u\s\t\o\m' == wp_unslash( $_POST['time_format'] ) ) {
                                    $_POST['time_format'] = $_POST['time_format_custom'];
                            }
                            // Map UTC+- timezones to gmt_offsets and set timezone_string to empty.
                            if ( ! empty( $_POST['timezone_string'] ) && preg_match( '/^UTC[+-]/', $_POST['timezone_string'] ) ) {
                                    $_POST['gmt_offset']      = $_POST['timezone_string'];
                                    $_POST['gmt_offset']      = preg_replace( '/UTC\+?/', '', $_POST['gmt_offset'] );
                                    $_POST['timezone_string'] = '';
                            }

                            // Handle translation installation.
                            if ( ! empty( $_POST['WPLANG'] ) && current_user_can( 'install_languages' ) ) {
                                    require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );

                                    if ( wp_can_install_language_pack() ) {
                                            $language = wp_download_language_pack( $_POST['WPLANG'] );
                                            if ( $language ) {
                                                    $_POST['WPLANG'] = $language;
                                            }
                                    }
                            }
                            
                            if ( $options ) {
                                    $user_language_old = get_user_locale();

                                    foreach ( $options as $option ) {

                                            $option = trim( $option );
                                            $value  = null;
                                            if ( isset( $_POST[ $option ] ) ) {
                                                    $value = $_POST[ $option ];
                                                    if ( ! is_array( $value ) ) {
                                                            $value = trim( $value );
                                                    }
                                                    $value = wp_unslash( $value );
                                            }
                                            update_option( $option, $value );
                                    }

                                    /*
                                     * Switch translation in case WPLANG was changed.
                                     * The global $locale is used in get_locale() which is
                                     * used as a fallback in get_user_locale().
                                     */
                                    unset( $GLOBALS['locale'] );
                                    $user_language_new = get_user_locale();
                                    if ( $user_language_old !== $user_language_new ) {
                                            load_default_textdomain( $user_language_new );
                                    }
                            }
                            
                            if ( ! is_wp_error( $errors ) ) {
                                     $redirect = add_query_arg( 'updated', true, $wp_http_referer );
                                     wp_redirect( $redirect );
                                     exit;
                             }

             }

             //$wp_http_referer = add_query_arg( 'action', 'pos-host-store-update', $wp_http_referer );

            ?>

            <div class="wrap">
            <h1><?php echo esc_html( $title ); ?></h1>

            <form method="post" action="<?php echo $$wp_http_referer; ?>" novalidate="novalidate">
                
            <?php wp_nonce_field( 'update-store');
            
            ?>
            <input name="action" type="hidden" id="store_action" value="<?php echo 'pos-host-store-update';  ?>" class="regular-text" />
            <table class="form-table" role="presentation">

            <tr>
            <th scope="row"><label for="blogname"><?php _e( 'Site Title' ); ?></label></th>
            <td><input name="blogname" type="text" id="blogname" value="<?php form_option( 'blogname' ); ?>" class="regular-text" /></td>
            </tr>

            <tr>
            <th scope="row"><label for="blogdescription"><?php _e( 'Tagline' ); ?></label></th>
            <td><input name="blogdescription" type="text" id="blogdescription" aria-describedby="tagline-description" value="<?php form_option( 'blogdescription' ); ?>" class="regular-text" />
            <p class="description" id="tagline-description"><?php _e( 'In a few words, explain what this site is about.' ); ?></p></td>
            </tr>
            
            <?php
            $languages    = get_available_languages();
            $translations = wp_get_available_translations();
            if ( ! is_multisite() && defined( 'WPLANG' ) && '' !== WPLANG && 'en_US' !== WPLANG && ! in_array( WPLANG, $languages ) ) {
                    $languages[] = WPLANG;
            }
            if ( ! empty( $languages ) || ! empty( $translations ) ) {
                    ?>
                    <tr>
                            <th scope="row"><label for="WPLANG"><?php _e( 'Site Language' ); ?><span class="dashicons dashicons-translation" aria-hidden="true"></span></label></th>
                            <td>
                                    <?php
                                    $locale = get_locale();
                                    if ( ! in_array( $locale, $languages ) ) {
                                            $locale = '';
                                    }

                                    wp_dropdown_languages(
                                            array(
                                                    'name'                        => 'WPLANG',
                                                    'id'                          => 'WPLANG',
                                                    'selected'                    => $locale,
                                                    'languages'                   => $languages,
                                                    'translations'                => $translations,
                                                    'show_available_translations' => current_user_can( 'install_languages' ) && wp_can_install_language_pack(),
                                            )
                                    );

                                    // Add note about deprecated WPLANG constant.
                                    if ( defined( 'WPLANG' ) && ( '' !== WPLANG ) && $locale !== WPLANG ) {
                                            _deprecated_argument(
                                                    'define()',
                                                    '4.0.0',
                                                    /* translators: 1: WPLANG, 2: wp-config.php */
                                                    sprintf( __( 'The %1$s constant in your %2$s file is no longer needed.' ), 'WPLANG', 'wp-config.php' )
                                            );
                                    }
                                    ?>
                            </td>
                    </tr>
                    <?php
            }
            ?>
            <tr>
            <?php
            $current_offset = get_option( 'gmt_offset' );
            $tzstring       = get_option( 'timezone_string' );

            $check_zone_info = true;

            // Remove old Etc mappings. Fallback to gmt_offset.
            if ( false !== strpos( $tzstring, 'Etc/GMT' ) ) {
                    $tzstring = '';
            }

            if ( empty( $tzstring ) ) { // Create a UTC+- zone if no timezone string exists
                    $check_zone_info = false;
                    if ( 0 == $current_offset ) {
                            $tzstring = 'UTC+0';
                    } elseif ( $current_offset < 0 ) {
                            $tzstring = 'UTC' . $current_offset;
                    } else {
                            $tzstring = 'UTC+' . $current_offset;
                    }
            }

            ?>
            <th scope="row"><label for="timezone_string"><?php _e( 'Timezone' ); ?></label></th>
            <td>

            <select id="timezone_string" name="timezone_string" aria-describedby="timezone-description">
                    <?php echo wp_timezone_choice( $tzstring, get_user_locale() ); ?>
            </select>

            <p class="description" id="timezone-description">
            <?php
                    printf(
                            /* translators: %s: UTC abbreviation */
                            __( 'Choose either a city in the same timezone as you or a %s (Coordinated Universal Time) time offset.' ),
                            '<abbr>UTC</abbr>'
                    );
                    ?>
            </p>

            <p class="timezone-info">
                    <span id="utc-time">
                    <?php
                            printf(
                                    /* translators: %s: UTC time. */
                                    __( 'Universal time is %s.' ),
                                    '<code>' . date_i18n( $timezone_format, false, true ) . '</code>'
                            );
                            ?>
                    </span>
            <?php if ( get_option( 'timezone_string' ) || ! empty( $current_offset ) ) : ?>
                    <span id="local-time">
                    <?php
                            printf(
                                    /* translators: %s: Local time. */
                                    __( 'Local time is %s.' ),
                                    '<code>' . date_i18n( $timezone_format ) . '</code>'
                            );
                    ?>
                    </span>
            <?php endif; ?>
            </p>

            <?php if ( $check_zone_info && $tzstring ) : ?>
            <p class="timezone-info">
            <span>
                    <?php
                    $now = new DateTime( 'now', new DateTimeZone( $tzstring ) );
                    $dst = (bool) $now->format( 'I' );

                    if ( $dst ) {
                            _e( 'This timezone is currently in daylight saving time.' );
                    } else {
                            _e( 'This timezone is currently in standard time.' );
                    }
                    ?>
                    <br />
                    <?php
                    if ( in_array( $tzstring, timezone_identifiers_list() ) ) {
                            $transitions = timezone_transitions_get( timezone_open( $tzstring ), time() );

                            // 0 index is the state at current time, 1 index is the next transition, if any.
                            if ( ! empty( $transitions[1] ) ) {
                                    echo ' ';
                                    $message = $transitions[1]['isdst'] ?
                                            /* translators: %s: Date and time. */
                                            __( 'Daylight saving time begins on: %s.' ) :
                                            /* translators: %s: Date and time. */
                                            __( 'Standard time begins on: %s.' );
                                    printf(
                                            $message,
                                            '<code>' . wp_date( __( 'F j, Y' ) . ' ' . __( 'g:i a' ), $transitions[1]['ts'] ) . '</code>'
                                    );
                            } else {
                                    _e( 'This timezone does not observe daylight saving time.' );
                            }
                    }
                    ?>
                    </span>
            </p>
            <?php endif; ?>
            </td>

            </tr>
            <tr>
            <th scope="row"><?php _e( 'Date Format' ); ?></th>
            <td>
                    <fieldset><legend class="screen-reader-text"><span><?php _e( 'Date Format' ); ?></span></legend>
            <?php
                    /**
                     * Filters the default date formats.
                     *
                     * @since 2.7.0
                     * @since 4.0.0 Added ISO date standard YYYY-MM-DD format.
                     *
                     * @param string[] $default_date_formats Array of default date formats.
                     */
                    $date_formats = array_unique( apply_filters( 'date_formats', array( __( 'F j, Y' ), 'Y-m-d', 'm/d/Y', 'd/m/Y' ) ) );

                    $custom = true;

            foreach ( $date_formats as $format ) {
                    echo "\t<label><input type='radio' name='date_format' value='" . esc_attr( $format ) . "'";
                    if ( get_option( 'date_format' ) === $format ) { // checked() uses "==" rather than "==="
                            echo " checked='checked'";
                            $custom = false;
                    }
                    echo ' /> <span class="date-time-text format-i18n">' . date_i18n( $format ) . '</span><code>' . esc_html( $format ) . "</code></label><br />\n";
            }

                    echo '<label><input type="radio" name="date_format" id="date_format_custom_radio" value="\c\u\s\t\o\m"';
                    checked( $custom );
                    echo '/> <span class="date-time-text date-time-custom-text">' . __( 'Custom:' ) . '<span class="screen-reader-text"> ' . __( 'enter a custom date format in the following field' ) . '</span></span></label>' .
                            '<label for="date_format_custom" class="screen-reader-text">' . __( 'Custom date format:' ) . '</label>' .
                            '<input type="text" name="date_format_custom" id="date_format_custom" value="' . esc_attr( get_option( 'date_format' ) ) . '" class="small-text" />' .
                            '<br />' .
                            '<p><strong>' . __( 'Preview:' ) . '</strong> <span class="example">' . date_i18n( get_option( 'date_format' ) ) . '</span>' .
                            "<span class='spinner'></span>\n" . '</p>';
            ?>
                    </fieldset>
            </td>
            </tr>
            <tr>
            <th scope="row"><?php _e( 'Time Format' ); ?></th>
            <td>
                    <fieldset><legend class="screen-reader-text"><span><?php _e( 'Time Format' ); ?></span></legend>
            <?php
                    /**
                     * Filters the default time formats.
                     *
                     * @since 2.7.0
                     *
                     * @param string[] $default_time_formats Array of default time formats.
                     */
                    $time_formats = array_unique( apply_filters( 'time_formats', array( __( 'g:i a' ), 'g:i A', 'H:i' ) ) );

                    $custom = true;

            foreach ( $time_formats as $format ) {
                    echo "\t<label><input type='radio' name='time_format' value='" . esc_attr( $format ) . "'";
                    if ( get_option( 'time_format' ) === $format ) { // checked() uses "==" rather than "==="
                            echo " checked='checked'";
                            $custom = false;
                    }
                    echo ' /> <span class="date-time-text format-i18n">' . date_i18n( $format ) . '</span><code>' . esc_html( $format ) . "</code></label><br />\n";
            }

                    echo '<label><input type="radio" name="time_format" id="time_format_custom_radio" value="\c\u\s\t\o\m"';
                    checked( $custom );
                    echo '/> <span class="date-time-text date-time-custom-text">' . __( 'Custom:' ) . '<span class="screen-reader-text"> ' . __( 'enter a custom time format in the following field' ) . '</span></span></label>' .
                            '<label for="time_format_custom" class="screen-reader-text">' . __( 'Custom time format:' ) . '</label>' .
                            '<input type="text" name="time_format_custom" id="time_format_custom" value="' . esc_attr( get_option( 'time_format' ) ) . '" class="small-text" />' .
                            '<br />' .
                            '<p><strong>' . __( 'Preview:' ) . '</strong> <span class="example">' . date_i18n( get_option( 'time_format' ) ) . '</span>' .
                            "<span class='spinner'></span>\n" . '</p>';

                    echo "\t<p class='date-time-doc'>" . __( '<a href="https://wordpress.org/support/article/formatting-date-and-time/">Documentation on date and time formatting</a>.' ) . "</p>\n";
            ?>
                    </fieldset>
            </td>
            </tr>
            <tr>
            <th scope="row"><label for="start_of_week"><?php _e( 'Week Starts On' ); ?></label></th>
            <td><select name="start_of_week" id="start_of_week">
            <?php
            /**
             * @global WP_Locale $wp_locale WordPress date and time locale object.
             */
            global $wp_locale;

            for ( $day_index = 0; $day_index <= 6; $day_index++ ) :
                    $selected = ( get_option( 'start_of_week' ) == $day_index ) ? 'selected="selected"' : '';
                    echo "\n\t<option value='" . esc_attr( $day_index ) . "' $selected>" . $wp_locale->get_weekday( $day_index ) . '</option>';
            endfor;
            ?>
            </select></td>
            </tr>
            </table>

            <?php submit_button(); ?>
            </form>

            </div>

		<?php
	}
}
