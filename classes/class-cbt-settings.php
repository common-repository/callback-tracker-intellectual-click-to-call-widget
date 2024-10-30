<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class CBT_Settings
{

    /**
     * @var array
     */
    protected $_form_messages = [];

    /**
     * @var string
     */
    protected $_option_slug;

    protected $_api_login_url = 'https://app.callbacktracker.com/api/login';

    protected function _do_redirect_option_page($message_code = null)
    {
        $return_url = add_query_arg('page', $this->_option_slug, admin_url('admin.php'));

        if (!is_null($message_code)) {
            $return_url = add_query_arg('message', $message_code, $return_url);
        }

        wp_redirect($return_url);
        die();
    }

    public function admin_init()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (!empty($_REQUEST['cbt-action'])) {
            if ('logout' === $_REQUEST['cbt-action']) {
                check_admin_referer('cbt_logout_' . get_current_user_id());
                delete_option($this->_option_slug);
                $this->_do_redirect_option_page(5);
            }

            if ('login' === $_REQUEST['cbt-action']) {
                if (empty($_REQUEST['nonce']) || !check_ajax_referer('cbt_' . $_REQUEST['cbt-action'] . '_' . get_current_user_id(), 'nonce', false)) {
                    $this->_do_redirect_option_page(6);
                }

                $response = wp_remote_post($this->_api_login_url, [
                    'body' => [
                        'username' => $_POST['cbt']['username'],
                        'password' => $_POST['cbt']['password'],
                    ],
                ]);

                if (is_wp_error($response)) {
                    $this->_do_redirect_option_page(4);
                }
                if (200 !== (int) $response['response']['code']) {
                    if ((int) $response['response']['code'] === 401) {
                        $this->_do_redirect_option_page(3);
                    }
                    $this->_do_redirect_option_page(4);
                }

                $data_return = json_decode($response['body']);

                if (200 !== $data_return->code || is_null($data_return)) {
                    $this->_do_redirect_option_page(3);
                }

                $options = get_option($this->_option_slug, []);
                $options['token'] = $data_return->token;
                $options['username'] = $_POST['cbt']['username'];
                $options['password'] = $_POST['cbt']['password'];

                update_option($this->_option_slug, $options);

                $this->_do_redirect_option_page(2);
            }
        }
    }

    public function cbt_logout()
    {
        $options = get_option($this->_option_slug, []);
        $options['token'] = '';
        $options['username'] = '';
        $options['password'] = '';
        update_option($this->_option_slug, $options);
        echo '<div class="notice notice-success is-dismissible"><p><strong>You have disconnected your account</strong>';
    }

    public function cbt_setting_content()
    {
        $username = $this->get_option( 'username' );
        $password = $this->get_option( 'password' );
        ?>
        <!-- Create a header in the default WordPress 'wrap' container -->
        <div class="wrap">
            <a href="https://callbacktracker.com" target="_blank">
                <img class="cbt-logo" src="https://callbacktracker.com/wp-content/uploads/2019/08/callback-logo-1.svg" alt="Callback Tracker" />
            </a>

            <?php if (!empty($_GET['message']) && !empty($this->_form_messages[$_GET['message']])) : ?>
                <div class="notice notice-<?php echo $this->_form_messages[$_GET['message']]['status']; ?> is-dismissible">
                    <p><strong><?php echo $this->_form_messages[$_GET['message']]['msg']; ?></strong></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($username) && !empty($password)) : ?>
                <div class="notice notice-success">
                    <p><strong>Your Callback Tracker widget is activated.</strong></p>
                </div>
            <?php else : ?>
                <div class="notice notice-info is-dismissible">
                    <p><strong>You are just a few seconds away from increasing your online leads ...</strong></p>
                    <p><b>Need a Callback Tracker Account?</b> <a id="linkin" href="https://app.callbacktracker.com/user/register" target="_blank">Sign up now</a></p>
                </div>
                <form class="form-table" name="cbt_form" id="cbt_settings_form" method="POST" action="">
                    <input type="hidden" name="page" value="<?php echo $this->_option_slug; ?>" />
                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('cbt_login_' . get_current_user_id()); ?>" />
                    <input type="hidden" name="cbt-action" value="login" />
                    <table class="form-table">
                        <tbody>
                        <tr valign="top">
                            <td colspan="2" class="cbt-header">
                                <h3 class="cbt-settings-top-header">Sign In</h3>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Your E-mail:</th>
                            <td>
                                <input type="text" name="cbt[username]" id="email" size="20" placeholder="Enter your email" class="regular-text" required>
                                <p class="description">E-mail address you use to log in to your Callback Tracker account</p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Your password:</th>
                            <td>
                                <input type="password" name="cbt[password]" id="pass" size="20" placeholder="Type a password" required>
                                <p class="description">Password you use to log in to your Callback Tracker account</p>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <p class="submit">
                        <input class="button button-primary" type="submit" name="Submit" value="SIGN IN" />
                    </p>
                </form>
                <br />
            <?php endif; ?>
        </div><!-- /.wrap -->
        <?php
    }

    public function admin_menu()
    {
        $username = $this->get_option('username');
        $password = $this->get_option('password');
        add_menu_page('Callback Tracker Settings', 'Callback Tracker', 'manage_options', 'cbt-settings', [&$this, 'cbt_setting_content'], plugins_url('assets/images/logo-cbt-16x16.png', CBT_BASE));

        if (empty($username) && empty($password)) {
            add_action('all_admin_notices', [&$this, 'cbtNotice']);
        }
        if (!empty($username) && !empty($password)) {
            add_submenu_page('cbt-settings', 'Disconnect account', 'Disconnect account', 'administrator', 'cbt-logout', [&$this, 'cbt_logout']);
        }
    }

    public function get_option($key)
    {
        $options = get_option($this->_option_slug);
        return isset($options[$key]) ? $options[$key] : '';
    }

    function cbtNotice()
    {
        echo '<div class="notice notice-error is-dismissible"><p><strong>Callback Tracker widget is not active</strong> - <a href="edit.php?page=cbt-settings">Activate your account now</a> to start increasing your leads</p></div>';
    }

    public function __construct()
    {
        $this->_form_messages = [
            '', // Just skip from zero array.
            [
                'msg'    => __('Registration was successful.', 'cbt'),
                'status' => 'success',
            ],
            [
                'msg'    => __('Login was successful.', 'cbt'),
                'status' => 'success',
            ],
            [
                'msg'    => __('Invalid login or password.', 'cbt'),
                'status' => 'error',
            ],
            [
                'msg'    => __('Could not connect to API server, please try later or contact us at support@callbacktracker.com.', 'cbt'),
                'status' => 'error',
            ],
            [
                'msg'    => __('Your logout was successful.', 'cbt'),
                'status' => 'success',
            ],
            [
                'msg'    => __('Action expired.', 'cbt'),
                'status' => 'error',
            ],
        ];

        $this->_option_slug = 'cbt-settings';

        add_action('admin_init', [&$this, 'admin_init']);
        add_action('admin_menu', [&$this, 'admin_menu']);
    }
}
