<?php
/**
 * Clerk Controller
 *
 * @package WooCommerce_pos_host/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * POS_HOST_Users Class
 */
class POS_HOST_Users {

	/**
	 * The single instance of the class.
	 *
	 * @var POS_HOST_Users
	 */
	protected static $_instance = null;

	/**
	 * Main POS_HOST_Users Instance.
	 *
	 * Ensures only one instance of POS_HOST_Users is loaded or can be loaded.
	 *
	 * @return POS_HOST_Users Main instance.
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
         

        public function display_single_user_page($user_id) {

        /**
         * Edit user administration panel.
         *
         * @package WordPress
         * @subpackage Administration
         */
        /** WordPress Administration Bootstrap */
        $user_id      = (int) $user_id;
        $profileuser = get_user_to_edit( $user_id );
        
        if ( ! $user_id ) {
            wp_die( __( 'Invalid user ID.' ));
        }else if ( !is_user_member_of_blog($user_id) ||
                    in_array( 'administrator', (array) $profileuser->roles ) ){
            wp_die( __( 'Sorry, you are not allowed to edit this user.' ));
        } 

        require_once( ABSPATH . 'wp-admin/admin.php' );
        
        wp_reset_vars( array( 'action', 'user_id' ) );
        $wp_http_referer = add_query_arg( 'user_id', $user_id, get_admin_url('','admin.php'));
        
        $action = $GLOBALS['action'];
        
        $current_user = wp_get_current_user();

        /* get the current_user's primary site,
         * must match its primary blog
         */
        if ( get_current_blog_id() != get_user_meta( $current_user->ID, 'primary_blog', true )){
            wp_die( __( 'Sorry, you are not allowed to edit this user.' ));
        };

        if ( ! current_user_can( 'manage_woocommerce_pos_host' ) ) {
            wp_die( __( 'Sorry, you are not allowed to edit this user.' ) );
        }
        wp_enqueue_script( 'user-profile' );

        $page_title = __( 'Assign User To Locations' );

        if ( 'pos-host-user-update' == $action ) {
                        check_admin_referer( 'update-user_' . $user_id );

                        /* shop manager, 
                          if ( ! current_user_can( 'edit_users', $user_id ) ) {
                                wp_die( __( 'Sorry, you are not allowed to edit this user.' ) );
                        }
                         * 
                         */
                        if ( $current_user->ID  == $user_id ) {
                                /**
                                 * Fires before the page loads on the 'Your Profile' editing screen.
                                 *
                                 * The action only fires if the current user is editing their own profile.
                                 *
                                 * @since 2.0.0
                                 *
                                 * @param int $user_id The user ID.
                                 */
                                do_action( 'personal_options_update', $user_id );
                        } else {
                                /**
                                 * Fires before the page loads on the 'Edit User' screen.
                                 *
                                 * @since 2.7.0
                                 *
                                 * @param int $user_id The user ID.
                                 */
                                do_action( 'edit_user_profile_update', $user_id );
                        }

                        // Update the user.
                        $errors = edit_user( $user_id );

                        if ( ! is_wp_error( $errors ) ) {
                                $redirect = add_query_arg( 'updated', true, $wp_http_referer );
                                //change action to show 
                                $redirect = add_query_arg( 'action', 'pos-host-user-show', $redirect );
                                wp_redirect( $redirect );
                                exit;
                        }

        }
        $wp_http_referer = add_query_arg( 'action', 'pos-host-user-update', $wp_http_referer );

        /* checked role = shop_manager, user_is is a member of the blog already
        if ( ! current_user_can( 'edit_users', $user_id ) ) {
                wp_die( __( 'Sorry, you are not allowed to edit this user.' ) );
        }
        */
        
        $page_title    = sprintf( $page_title, $profileuser->display_name );
        $sessions = WP_Session_Tokens::get_instance( $profileuser->ID );

        include( ABSPATH . 'wp-admin/admin-header.php' );
        
        if ( isset( $_GET['updated'] ) ) : 
        ?>
            <div id="message" class="updated notice is-dismissible">
                <p><strong><?php _e( 'User updated.' ); ?></strong></p>
            </div>
        <?php endif; ?>

        <?php if ( isset( $errors ) && is_wp_error( $errors ) ) : ?>
            <div class="error"><p><?php echo implode( "</p>\n<p>", $errors->get_error_messages() ); ?></p></div>
        <?php endif; ?>

            <div class="wrap" id="profile-page">
            <h1 class="wp-heading-inline">
                        <?php
                        echo esc_html( $page_title );
                        ?>
            </h1>

        <?php if ( is_multisite() && current_user_can( 'promote_users' ) ) { ?>
                <a href="user-new.php" class="page-title-action"><?php echo esc_html_x( 'Add User', 'user' ); ?></a>
        <?php
        }
        ?>

        <hr class="wp-header-end">

        <form id="your-profile" action="<?php echo $wp_http_referer  ?>" method="post" novalidate="novalidate" >
                    <?php wp_nonce_field( 'update-user_' . $user_id ); ?>
        <h2><?php _e( 'Name' ); ?></h2>

        <table class="form-table" role="presentation">
                <tr class="user-user-login-wrap">
                        <th><label for="user_login"><?php _e( 'Username' ); ?></label></th>
                        <td><input type="text" name="user_login" id="user_login" value="<?php echo esc_attr( $profileuser->user_login ); ?>" disabled="disabled" class="regular-text" /> <span class="description"><?php _e( 'Usernames cannot be changed.' ); ?></span></td>
                </tr>

        <?php if ( current_user_can( 'promote_user', $profileuser->ID ) ) : ?>
        <tr class="user-role-wrap"><th><label for="role"><?php _e( 'Role' ); ?></label></th>
        <td><select name="role" id="role">
                                <?php
                                // Compare user role against currently editable roles
                                $user_roles = array_intersect( array_values( $profileuser->roles ), array_keys( get_editable_roles() ) );
                                $user_role  = reset( $user_roles );

                                // print the full list of roles with the primary one selected.
                                wp_dropdown_roles( $user_role );

                                // print the 'no role' option. Make it selected if the user has no role yet.
                                if ( $user_role ) {
                                        echo '<option value="">' . __( '&mdash; No role for this site &mdash;' ) . '</option>';
                                } else {
                                        echo '<option value="" selected="selected">' . __( '&mdash; No role for this site &mdash;' ) . '</option>';
                                }
                                ?>
        </select></td></tr>
        <?php endif; ?>
        <tr class="user-first-name-wrap">
                <th><label for="first_name"><?php _e( 'First Name' ); ?></label></th>
                <td><input type="text" name="first_name" id="first_name" value="<?php echo esc_attr( $profileuser->first_name ); ?>" class="regular-text" /></td>
        </tr>

        <tr class="user-last-name-wrap">
                <th><label for="last_name"><?php _e( 'Last Name' ); ?></label></th>
                <td><input type="text" name="last_name" id="last_name" value="<?php echo esc_attr( $profileuser->last_name ); ?>" class="regular-text" /></td>
        </tr>

        <tr class="user-nickname-wrap">
                <th><label for="nickname"><?php _e( 'Nickname' ); ?> <span class="description"><?php _e( '(required)' ); ?></span></label></th>
                <td><input type="text" name="nickname" id="nickname" value="<?php echo esc_attr( $profileuser->nickname ); ?>" class="regular-text" /></td>
        </tr>

        <tr class="user-display-name-wrap">
                <th><label for="display_name"><?php _e( 'Display name publicly as' ); ?></label></th>
                <td>
                        <select name="display_name" id="display_name">
                        <?php
                                $public_display                     = array();
                                $public_display['display_nickname'] = $profileuser->nickname;
                                $public_display['display_username'] = $profileuser->user_login;

                        if ( ! empty( $profileuser->first_name ) ) {
                                $public_display['display_firstname'] = $profileuser->first_name;
                        }

                        if ( ! empty( $profileuser->last_name ) ) {
                                $public_display['display_lastname'] = $profileuser->last_name;
                        }

                        if ( ! empty( $profileuser->first_name ) && ! empty( $profileuser->last_name ) ) {
                                $public_display['display_firstlast'] = $profileuser->first_name . ' ' . $profileuser->last_name;
                                $public_display['display_lastfirst'] = $profileuser->last_name . ' ' . $profileuser->first_name;
                        }

                        if ( ! in_array( $profileuser->display_name, $public_display ) ) { // Only add this if it isn't duplicated elsewhere
                                $public_display = array( 'display_displayname' => $profileuser->display_name ) + $public_display;
                        }

                                $public_display = array_map( 'trim', $public_display );
                                $public_display = array_unique( $public_display );

                        foreach ( $public_display as $id => $item ) {
                                ?>
                        <option <?php selected( $profileuser->display_name, $item ); ?>><?php echo $item; ?></option>
                                <?php
                        }
                        ?>
                        </select>
                        </td>
                </tr>
                </table>


                <?php
                /**
                 * Filters the display of the password fields.
                 *
                 * @since 1.5.1
                 * @since 2.8.0 Added the `$profileuser` parameter.
                 * @since 4.4.0 Now evaluated only in user-edit.php.
                 *
                 * @param bool    $show        Whether to show the password fields. Default true.
                 * @param WP_User $profileuser User object for the current user to edit.
                 */
                $show_password_fields = apply_filters( 'show_password_fields', true, $profileuser );
                if ( $show_password_fields ) :
                        ?>
	<h2><?php _e( 'Contact Info' ); ?></h2>

	<table class="form-table" role="presentation">
	<tr class="user-email-wrap">
		<th><label for="email"><?php _e( 'Email' ); ?> <span class="description"><?php _e( '(required)' ); ?></span></label></th>
		<td><input type="email" name="email" id="email" aria-describedby="email-description" value="<?php echo esc_attr( $profileuser->user_email ); ?>" class="regular-text ltr" />
		<?php
		if ( $profileuser->ID == $current_user->ID ) :
			?>
		<p class="description" id="email-description">
			<?php _e( 'If you change this we will send you an email at your new address to confirm it. <strong>The new address will not become active until confirmed.</strong>' ); ?>
		</p>
			<?php
		endif;

		$new_email = get_user_meta( $current_user->ID, '_new_email', true );
		if ( $new_email && $new_email['newemail'] != $current_user->user_email && $profileuser->ID == $current_user->ID ) :
			?>
		<div class="updated inline">
		<p>
			<?php
			printf(
				/* translators: %s: New email. */
				__( 'There is a pending change of your email to %s.' ),
				'<code>' . esc_html( $new_email['newemail'] ) . '</code>'
			);
			printf(
				' <a href="%1$s">%2$s</a>',
				esc_url( wp_nonce_url( self_admin_url( 'profile.php?dismiss=' . $current_user->ID . '_new_email' ), 'dismiss-' . $current_user->ID . '_new_email' ) ),
				__( 'Cancel' )
			);
			?>
		</p>
		</div>
		<?php endif; ?>
	</td>
	</tr>

	<tr class="user-url-wrap">
	<th><label for="url"><?php _e( 'Website' ); ?></label></th>
	<td><input type="url" name="url" id="url" value="<?php echo esc_attr( $profileuser->user_url ); ?>" class="regular-text code" /></td>
	</tr>

		<?php
		foreach ( wp_get_user_contact_methods( $profileuser ) as $name => $desc ) {
			?>
	<tr class="user-<?php echo $name; ?>-wrap">
         <th><label for="<?php echo $name; ?>">
			<?php
			/**
			 * Filters a user contactmethod label.
			 *
			 * The dynamic portion of the filter hook, `$name`, refers to
			 * each of the keys in the contactmethods array.
			 *
			 * @since 2.9.0
			 *
			 * @param string $desc The translatable label for the contactmethod.
			 */
			echo apply_filters( "user_{$name}_label", $desc );
			?>
	</label></th>
	<td><input type="text" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo esc_attr( $profileuser->$name ); ?>" class="regular-text" /></td>
	</tr>
			<?php
		}
		?>
	</table>

            <h2><?php _e( 'Generate Password'  ); ?></h2>
            <table class="form-table" role="presentation">
            <tr id="password" class="user-pass1-wrap">
                <th><label for="pass1"><?php _e( 'New Password' ); ?></label></th>
                <td>
                        <input class="hidden" value=" " /><!-- #24364 workaround -->
                        <button type="button" class="button wp-generate-pw hide-if-no-js"><?php _e( 'Generate Password' ); ?></button>
                        <div class="wp-pwd hide-if-js">
                                <span class="password-input-wrapper">
                                        <input type="password" name="pass1" id="pass1" class="regular-text" value="" autocomplete="off" data-pw="<?php echo esc_attr( wp_generate_password( 24 ) ); ?>" aria-describedby="pass-strength-result" />
                                </span>
                                <button type="button" class="button wp-hide-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Hide password' ); ?>">
                                        <span class="dashicons dashicons-hidden" aria-hidden="true"></span>
                                        <span class="text"><?php _e( 'Hide' ); ?></span>
                                </button>
                                <button type="button" class="button wp-cancel-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Cancel password change' ); ?>">
                                        <span class="dashicons dashicons-no" aria-hidden="true"></span>
                                        <span class="text"><?php _e( 'Cancel' ); ?></span>
                                </button>
                                <div style="display:none" id="pass-strength-result" aria-live="polite"></div>
                        </div>
                </td>
            </tr>
            <tr class="user-pass2-wrap hide-if-js">
                <th scope="row"><label for="pass2"><?php _e( 'Repeat New Password' ); ?></label></th>
                <td>
                <input name="pass2" type="password" id="pass2" class="regular-text" value="" autocomplete="off" />
                <p class="description"><?php _e( 'Type your new password again.' ); ?></p>
                </td>
        </tr>
        <tr class="pw-weak">
                <th><?php _e( 'Confirm Password' ); ?></th>
                <td>
                        <label>
                                <input type="checkbox" name="pw_weak" class="pw-checkbox" />
                                <span id="pw-weak-text-label"><?php _e( 'Confirm use of potentially weak password' ); ?></span>
                        </label>
                </td>
        </tr>
        <?php endif; ?>

        <?php if ( $sessions->get_all() ) : ?>
                <tr class="user-sessions-wrap hide-if-no-js">
                        <th><?php _e( 'Sessions' ); ?></th>
                        <td>
                                <p><button type="button" class="button" id="destroy-sessions"><?php _e( 'Log Out Everywhere' ); ?></button></p>
                                <p class="description">
                                        <?php
                                        /* translators: %s: User's display name. */
                                        printf( __( 'Log %s out of all locations.' ), $profileuser->display_name );
                                        ?>
                                </p>
                        </td>
                </tr>
        <?php endif; ?>

        </table>

            <?php
                /**
                 * Fires after the 'About the User' settings table on the 'Edit User' screen.
                 *
                 * @since 2.0.0
                 *
                 * @param WP_User $profileuser The current WP_User object.
                 */
                do_action( 'edit_user_profile', $profileuser );
                /**
                 * Filters whether to display additional capabilities for the user.
                 *
                 * The 'Additional Capabilities' section will only be enabled if
                 * the number of the user's capabilities exceeds their number of
                 * roles.
                 *
                 * @since 2.8.0
                 *
                 * @param bool    $enable      Whether to display the capabilities. Default true.
                 * @param WP_User $profileuser The current WP_User object.
                 */
                if ( count( $profileuser->caps ) > count( $profileuser->roles )
                && apply_filters( 'additional_capabilities_display', true, $profileuser )
                ) :
                ?>
                <h2><?php _e( 'Additional Capabilities' ); ?></h2>
                <table class="form-table" role="presentation">
                <tr class="user-capabilities-wrap">
                        <th scope="row"><?php _e( 'Capabilities' ); ?></th>
                        <td>
                            <?php
                            $output = '';
                            foreach ( $profileuser->caps as $cap => $value ) {
                                    if ( ! $wp_roles->is_role( $cap ) ) {
                                            if ( '' != $output ) {
                                                    $output .= ', ';
                                            }

                                            if ( $value ) {
                                                    $output .= $cap;
                                            } else {
                                                    /* translators: %s: Capability name. */
                                                    $output .= sprintf( __( 'Denied: %s' ), $cap );
                                            }
                                    }
                            }
                            echo $output;
                            ?>
                        </td>
                </tr>
                </table>
        <?php endif; ?>

        <input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $user_id ); ?>" />

        <?php submit_button( __( 'Update User' ) ); ?>

        </form>
        </div>
        <script type="text/javascript">
                if ( window.location.hash == '#password') {
                       document.getElementById('pass1').focus();
                };
        </script>
        <?php
        include( ABSPATH . 'wp-admin/admin-footer.php' );
    }
}
