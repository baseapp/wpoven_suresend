<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
//use WPOVEN\LIST\TABLE\WPOven_SMTP_Suresend_List_Table;
use function PHPSTORM_META\type;

use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.wpoven.com/plugins/
 * @since      1.0.0
 *
 * @package    Wpoven_Smtp_Suresend
 * @subpackage Wpoven_Smtp_Suresend/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wpoven_Smtp_Suresend
 * @subpackage Wpoven_Smtp_Suresend/admin
 * @author     WPOven <contact@wpoven.com>
 */
class Wpoven_Smtp_Suresend_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	private $_wpoven_smtp_suresend;
	private $dkim_folder_path;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->dkim_folder_path = WP_CONTENT_DIR . '/uploads';


		if (!class_exists('ReduxFramework') && file_exists(require_once plugin_dir_path(dirname(__FILE__)) . 'includes/libraries/redux-framework/redux-core/framework.php')) {
			require_once plugin_dir_path(dirname(__FILE__)) . 'includes/libraries/redux-framework/redux-core/framework.php';
		}

		if (!function_exists('is_plugin_active')) {
			include_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}
		if (!class_exists('PHPMailer')) {
			require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
			require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
			require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
		}

		require_once  plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php';

		if (!class_exists('WPOven_SMTP_Suresend_List_Table')) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
			require_once plugin_dir_path(dirname(__FILE__)) . '/includes/class-wpoven-smtp-suresend-list-table.php';
		}

		require_once ABSPATH . WPINC . '/pluggable.php';

		add_filter('wp_mail', [$this, 'log_email']);
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpoven_Smtp_Suresend_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpoven_Smtp_Suresend_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wpoven-smtp-suresend-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpoven_Smtp_Suresend_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpoven_Smtp_Suresend_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wpoven-smtp-suresend-admin.js', array('jquery'), $this->version, false);
	}

	/**
	 * Generate DKIM private and public key.
	 */
	function createDkimPublicKeys($dkim_folder_path)
	{
		$public_key = get_option('dkim_public_key') ?? null;
		$options = get_option(WPOVEN_SMTP_SURESEND_SLUG);
		$dkim_public_key = $options['dns-content'] ?? null;

		if (!$public_key && !$dkim_public_key) {
			$private_key = RSA::createKey(2048);;
			$private_key_string = $private_key->toString('PKCS8');
			$public_key = $private_key->getPublicKey();
			$public_key_string = $public_key->toString('PKCS8');
			update_option('dkim_private_key', $private_key_string);
			update_option('dkim_public_key', $public_key_string);
		}
	}

	/**
	 * Call function when wp_mail() trigger.
	 */
	function log_email($mailArray)
	{
		if ($mailArray['to']) {
			$options = get_option(WPOVEN_SMTP_SURESEND_SLUG);
			$smtp_option = $options['smtp-method-option'];

			$status = 'failed';
			$to = $mailArray['to'];
			$subject = $mailArray['subject'];
			$headers = $mailArray['headers'];
			$message = $mailArray['message'];
			$text_format = true;
			$attachments = array();

			if ($smtp_option == 'smtp') {
				// $mail = $this->wpoven_smtp_check_connection();
				// if ($mail) {
				$form_data['status'] = 'success';
				$this->send_mail($to, $subject, $message, $headers, $attachments, $status, $text_format);
				//}
			} else {
				$this->send_mail($to, $subject, $message, $headers, $attachments, $status, $text_format);
				$this->wpoven_save_smtp_logs($to, $subject, $message, $headers, $status);
			}
		}
	}

	/**
	 * Check SMTP Server Connection.
	 */
	function wpoven_smtp_check_connection()
	{
		$options = get_option(WPOVEN_SMTP_SURESEND_SLUG);
		$host = $options['host'] ?? null;
		$auth = $options['auth'] ?? false;
		$username = $options['username'] ?? null;
		$password = $options['password'] ?? null;
		$encryption = $options['encryption'] ?? null;
		$port = $options['port'] ?? null;

		$cache_duration = 3600; // Cache duration in seconds (e.g., 60 minutes)
		$current_time = time();
		$last_checked = get_option('smtp_last_checked');
		$cached_status = get_option('smtp_connection_status');
		$cached_debug_log = get_option('smtp_debug_log');

		if ($last_checked && ($current_time - $last_checked) < $cache_duration && $cached_status != null) {
			$_SESSION['debug_log'] = $cached_debug_log;
			return $cached_status;
		}

		$mail = new PHPMailer(true);

		try {
			$mail->isSMTP();
			$mail->Host = $host;
			$mail->SMTPAuth = $auth;
			if ($auth) {
				$mail->Username = $username;
				$mail->Password = $password;
				$mail->SMTPSecure = $encryption;
			}
			$mail->Port = $port;
			$mail->SMTPDebug = 2; // Set to 2 to see more detailed output
			$mail->Debugoutput = function ($str) {
				static $debug = '';
				$debug .= "$str";
				$str = str_replace(array("CLIENT -> SERVER: ", "SERVER -> CLIENT: "), "", $debug);
				$_SESSION['debug_log'] = $str;
			};
			$mail->smtpConnect();

			$status = true;
			$debug_log = $_SESSION['debug_log'];
		} catch (Exception $e) {
			$status = false;
			$debug_log = $e->getMessage();
		}

		// Cache the status and debug log
		update_option('smtp_last_checked', $current_time);
		update_option('smtp_connection_status', $status);
		update_option('smtp_debug_log', $debug_log);

		return $status;
	}

	/**
	 * Send mail if SMTP server connected.
	 */
	function send_mail($to, $subject, $message, $headers, $attachments, $status, $text_format)
	{

		$options = get_option(WPOVEN_SMTP_SURESEND_SLUG);
		$from = $options['from-email'] ?? null;
		$name = $options['from-name'] ?? null;
		$host = $options['host'] ?? null;
		$auth = $options['auth'] ?? false;
		$username = $options['username'] ?? null;
		$password = $options['password'] ?? null;
		$encryption = $options['encryption'] ?? null;
		$port = $options['port'] ?? null;
		$smtp_option = $options['smtp-method-option'] ?? null;
		$enable_dkim = $options['enable-dkim'] ?? null;
		$dkim_domain = $options['dkim-domain'] ?? null;
		$dkim_selector = !empty($options['dkim-selector']) ? $options['dkim-selector'] : 'mail';
		$dkim_private_key = (get_option('dkim_private_key'));
		$mail = new PHPMailer(true);

		if (!empty($to) && !empty($from)) {
			$status = 'failed';

			try {
				if ($mail) {
					$status = 'success';
					if ($smtp_option == 'smtp') {
						$mail->isSMTP();
						$mail->Host = $host;
						$mail->SMTPAuth = $auth;
						if ($auth) {
							$mail->Username = $username;
							$mail->Password = $password;
							$mail->SMTPSecure = $encryption;
						}
						$mail->Port = $port;
					} else {
						$mail->isMail();
					}
					$mail->setFrom($from, $name);
					$mail->addAddress($to);
					$mail->isHTML($text_format);
					$mail->Subject = $subject;
					$mail->Body    = $message;
					if ($enable_dkim && $dkim_domain) {
						$mail->DKIM_domain = $dkim_domain;
						$mail->DKIM_private_string = $dkim_private_key;
						$mail->DKIM_selector = $dkim_selector;
						$mail->DKIM_passphrase = null;
					}
					//$mail->addAttachment($attachments); 
					$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
					$mail->send();

					$this->wpoven_save_smtp_logs($to, $subject, $message, $headers, $status);
					return true;
				}
			} catch (Exception $e) {
				return false;
			}
		}
	}

	/**
	 *Save logs in database.
	 */
	function wpoven_save_smtp_logs($to, $subject, $message, $headers, $status)
	{
		global $wpdb;
		$options = get_option(WPOVEN_SMTP_SURESEND_SLUG);
		$smtp_logging_status = isset($options['smtp-logging-status']) ? $options['smtp-logging-status'] : false;
		
		if ($smtp_logging_status) {
			$table_name = $wpdb->prefix . 'wpoven_smtp_suresend_logs';
			if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
				$charset_collate = $wpdb->get_charset_collate();
				$sql = "CREATE TABLE $table_name (
					id INT NOT NULL AUTO_INCREMENT,
					time DATETIME NOT NULL,
					recipient VARCHAR(255) NOT NULL,
					subject VARCHAR(255) NOT NULL,
					headers VARCHAR(255) NOT NULL,
					status VARCHAR(20) NOT NULL,
					message TEXT NOT NULL,
					smtplogs TEXT,
					PRIMARY KEY (id)
    		) $charset_collate;";
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);
			}
			$log = isset($_SESSION['debug_log']) ? $_SESSION['debug_log'] : null;
			$data_to_insert = array(
				'time' => current_time('mysql'),
				'recipient' => sanitize_email($to),
				'subject' => sanitize_text_field($subject),
				'headers' => sanitize_text_field($headers),
				'status' => sanitize_text_field($status),
				'message' => $message,
				'smtplogs' => sanitize_textarea_field($log),
			);
			$wpdb->insert($table_name, $data_to_insert);
		}
	}

	function get_form_data()
	{
		$form_data = array();
		$emailTo = isset($_POST['email_to']) ? $_POST['email_to'] : false;
		$text_format = isset($_POST['text_format']) ? $_POST['text_format'] : false;
		if ($emailTo) {
			$emailTo = sanitize_text_field($_POST['email_to']);
			$subject = 'SMTP Test';
			$message = 'This is a SMTP test mail.';

			$form_data = array(
				'emailTo' => $emailTo,
				'subject' => $subject,
				'headers' => '',
				'message' => $message,
				'text_format' => $text_format
			);
		}

		return $form_data;
	}

	/**
	 * SMTP Server General Settings.
	 */
	function wpoven_smtp_suresend_general_settings($options)
	{
		$smtp_option = $options['smtp-method-option'] ?? null;
		$conn = 'Failed to connect to SMTP host.';
		$statusColor = "red";
		$style = 'critical';
		if ($smtp_option == 'smtp') {
			$mail = $this->wpoven_smtp_check_connection();
			if ($mail) {
				$conn = 'SMTP host is connected.';
				$statusColor = "green";
				$style = 'success';
			}
		}

		$fields = array();
		if (isset($smtp_option)) {
			if ($smtp_option != 'php') {
				$smtp_host_desc = array(
					'id'      => 'desc',
					'type'    => 'info',
					'style'     => $style,
					'desc'  => '<strong name="status" style="color:' . $statusColor . ';">' . $conn . '</strong>',
				);
				$fields[] = $smtp_host_desc;
			}
		}

		$smtp_logging_status = array(
			'id'       => 'smtp-logging-status',
			'type'     => 'button_set',
			'title'    => esc_html__('SMTP Logs', 'WPOven SMTP Suresend'),
			'desc'     => esc_html__('Enable or disable smtp mail logging.', 'WPOven SMTP Suresend'),
			'options' => array(
				'0' => 'Disable',
				'1' => 'Enable'
			),
			'default' => '0'
		);

		$from_email_address = array(
			'id'      => 'from-email',
			'type'    => 'text',
			'title'   => 'From Email Address',
			'placeholder'  => 'example@gmail.com',
			'validate' => 'not_empty',
			'desc'  => 'Sender address for sending emails.',
		);

		$from_name = array(
			'id'      => 'from-name',
			'type'    => 'text',
			'title'   => 'From Name',
			'validate' => 'not_empty',
			'desc'  => 'Sender Name.',
		);

		$smtp_method_options = array(
			'id'          => 'smtp-method-option',
			'type'        => 'select',
			'title'       => 'Sending Option',
			'desc'  => 'Choose PHP or SMTP as your email sending method for secure and reliable message delivery.',
			'options'     => array(
				'php'  => 'PHP',
				'smtp'  => 'SMTP'
			),
			'default'     => 'php',
			'validate' => 'not_empty'
		);

		$smtp_host = array(
			'id'      => 'host',
			'type'    => 'text',
			'title'   => 'Host',
			'validate' => 'not_empty',
			'desc'  => 'SMTP client host.',
			'required' => array('smtp-method-option', 'equals', 'smtp'),
			'placeholder'  => 'smtp.example.io',
		);

		$smtp_auth = array(
			'id'      => 'auth',
			'type'    => 'checkbox',
			'title'   => 'Use Authentication',
			//'validate' => 'not_empty',
			'desc'  => 'Check this box if the SMTP server requires authentication.',
			'required' => array('smtp-method-option', 'equals', 'smtp'),
			'placeholder'  => 'smtp.example.io',
		);

		$smtp_username = array(
			'id'      => 'username',
			'type'    => 'text',
			'title'   => 'Username',
			//'validate' => 'not_empty',
			'desc'  => 'SMTP client username.',
			'required' => array('auth', 'equals', true),
		);

		$smtp_password = array(
			'id'      => 'password',
			'type'    => 'password',
			'title'   => 'Password',
			//'validate' => 'not_empty',
			'desc'  => 'SMTP client password.',
			'required' => array('auth', 'equals', true),
		);

		$smtp_encryption = array(
			'id'          => 'encryption',
			'type'        => 'select',
			'title'       => 'Type of Encryption',
			'desc'  => 'The encryption which will be used when sending an email (TLS is recommended).',
			'options'     => array(
				'tls'  => 'TLS',
				'ssl'  => 'SSL'
			),
			//'validate' => 'not_empty',
			'default'     => 'tls',
			'required' => array('auth', 'equals', true),
		);

		$smtp_port = array(
			'id'      => 'port',
			'type'    => 'text',
			'title'   => 'SMTP Port',
			'validate' => 'not_empty',
			'desc'  => 'For TLS, use port 25, 587; for SSL, use port 465.',
			'required' => array('smtp-method-option', 'equals', 'smtp'),

		);

		$fields[] = $smtp_logging_status;
		$fields[] = $from_email_address;
		$fields[] = $from_name;
		$fields[] = $smtp_method_options;
		$fields[] = $smtp_host;
		$fields[] = $smtp_auth;
		$fields[] = $smtp_username;
		$fields[] = $smtp_password;
		$fields[] = $smtp_encryption;
		$fields[] = $smtp_port;

		return $fields;
	}

	/**
	 * Send SMTP test mail after SMTP server connection established.
	 */
	function wpoven_smtp_suresend_smtp_test_settings($options)
	{
		$fields = array();

		$smtp_option = $options['smtp-method-option'] ?? false;

		$form_data = $this->get_form_data();
		$name = $form_data['from-name'] ?? null;
		$to = $form_data['emailTo'] ?? null;
		$from = $form_data['from-email'] ?? null;
		$subject = $form_data['subject'] ?? null;
		$message = $form_data['message'] ?? null;
		$text_format = true;
		$headers = '';
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= 'From: ' . $name . ' <' . $from . '>' . "\r\n";
		$headers .= 'Reply-To: ' . $to . "\r\n";
		$style = null;
		$content = null;
		$attachments = array();

		if ($form_data) {
			switch ($smtp_option) {
				case 'php':
					//wp_mail($to, $subject, $message, $custom_headers, $attachments);
					$responce = $this->send_mail($to, $subject, $message, $headers, $attachments, $status = null, $text_format);
					$style = 'warning';
					$content = 'Failed to send the test email.';
					if ($responce) {
						$content = 'Test email was sent successfully!';
						$style = 'success';
					}
					break;
				case 'smtp':
					$style = 'warning';
					$content = 'Failed to send the test email.';
					$responce = $this->send_mail($to, $subject, $message, $headers, $attachments, $status = null, $text_format);
					if ($responce) {
						$style = 'success';
						$content = 'Test email was sent successfully!';
					}
					break;
				default:
					break;
			}
		}
		if ($style && $content) {
			$fields[] = array(
				'id'      => 'my_notice',
				'type'    => 'info',
				'style'   => $style, // Optional: 'success', 'info', 'warning', 'error'
				'desc' => $content,
			);
		}

		$email_to = array(
			'id'      => 'email_to',
			'type'    => 'text',
			'title'   => 'To',
			'placeholder'  => 'example@gmail.com',
			'validate'    => 'email',
			'desc'  => 'Email address of the recipient.',
		);

		$text_format = array(
			'id'    => 'text_format',
			'type'  => 'switch',
			'title' => 'HTML',
			'desc'  => 'Enable email format: HTML or plain text.',
			'default'  => true,
		);

		$sendMail = array(
			'id'         => 'send-smtp-mail-test',
			'type'       => 'button_set',
			'title'      => '&nbsp;',
			'options'    => array(
				'enabled'  => 'Send',
			),
			'default'    => 'enabled'
		);

		$fields[] = $email_to;
		$fields[] = $text_format;
		$fields[] = $sendMail;

		return $fields;
	}

	function validateDkimDomain($dkimDomain)
	{
		// Regular expression to match DKIM domain format
		$dkimDomainRegex = '/^(?!.{256,})(?!.*[_.-]{2})[a-zA-Z0-9._-]+(?<![-.])\.[a-zA-Z]{2,}$/';

		// Check if the DKIM domain matches the regex pattern
		return preg_match($dkimDomainRegex, $dkimDomain);
	}

	/**
	 * Get and copy DKIM public key.
	 */
	function wpoven_smtp_suresend_dkim_settings()
	{
		$options = get_option(WPOVEN_SMTP_SURESEND_SLUG);

		$dkim_public_key = get_option('dkim_public_key');
		$beginPublicKey = '-----BEGIN PUBLIC KEY-----';
		$endPublicKey = '-----END PUBLIC KEY-----';
		$publicKey = str_replace([$beginPublicKey, $endPublicKey, "\r", "\n"], '', ($dkim_public_key));
		$dns_content = 'v=DKIM1; k=rsa; p=' . trim($publicKey) . ';';

		$dkim_domain = $options['dkim-domain'] ?? null;
		$dkim_selector = $options['dkim-selector'] ?? null;
		$domain = $dkim_domain != null ? $dkim_domain : 'example.com';
		$selector = $dkim_selector != null ? $dkim_selector : 'mail';
		$conn = null;
		$enable_dkim = $options['enable-dkim'] ?? false;
		$dkim_dns_record = $selector . '._domainkey.' . $dkim_domain;
		$dns_result = null;
		$ns = ['8.8.8.8', '8.8.4.4'];

		if ($this->validateDkimDomain($dkim_domain)) {
			$resolver = new Net_DNS2_Resolver(['nameservers' => $ns]);
			$result = $resolver->query($dkim_dns_record, 'TXT');
			foreach ($result->answer as $record) {
				if ($record->type === 'TXT') {
					$dns_result = implode(' ', $record->text);
				}
			}
		}

		$statusColor = "red";
		$style = 'critical';
		if (($dkim_domain == null || !$this->validateDkimDomain($dkim_domain)) && $enable_dkim == true) {
			$conn = "Please provide valid domain name.";
		}
		if ($dkim_domain != null && $enable_dkim == true && $this->validateDkimDomain($dkim_domain)) {
			$conn = 'The DKIM key is missing from DNS. Make sure to add the DKIM key to your DNS record for "' . $dkim_dns_record . '".';
		}

		$fields = array();

		if ($dns_result != null) {
			$statusColor = "green";
			$style = 'success';
			$conn = "DKIM key is added to the DNS for $dkim_dns_record.\n";
		}

		if ($conn) {
			$is_dkim_added = array(
				'id'      => 'dkim-status',
				'type'    => 'info',
				'style'     => $style,
				'desc'  => '<strong name="status" style="color:' . $statusColor . ';">' . $conn . '</strong>',
			);
			$fields[] = $is_dkim_added;
		}

		$dkim_status = array(
			'id'      => 'enable-dkim',
			'type'    => 'switch',
			'title'   => 'Enable DKIM',
			'desc'    => 'Enable DKIM for enhanced email authentication and security.',
			'default' => false,
		);

		$dkim_domain = array(
			'id'      => 'dkim-domain',
			'type'    => 'text',
			'title'   => 'Domain Name',
			'desc'    => 'Provide domain name used for DKIM validation.',
			//'validate' => 'not_empty',
			'placeholder'  => 'example.com',
			'required' => array('enable-dkim', 'equals', true),
		);

		$dkim_selector = array(
			'id'      => 'dkim-selector',
			'type'    => 'text',
			'title'   => 'DKIM selector',
			'desc'    => 'Provide DKIM selector (default value : mail).',
			'placeholder'  => 'selector',
			'required' => array('enable-dkim', 'equals', true),
		);

		$dkim_pub_key = array(
			'id'       => 'dns-content',
			'type'     => 'textarea',
			'class'    => 'dkim-sign',
			'title'    => 'DNS Content',
			'desc'     => 'Copy the DNS content and set it as a TXT record in your DNS settings for your domain.',
			'rows'     => 5,
			'readonly' => true,
			'default'  => $dns_content,
		);

		$copy = array(
			'id'         => 'dkim-copy',
			'class'    => 'dkim-copy',
			'type'       => 'button_set',
			'title'      => '&nbsp;',
			'options'    => array(
				'enabled'  => 'Click To Copy',
			),
			'default'    => 'enabled',
		);

		$info = array(
			'id'      => 'my_notice',
			'type'    => 'info',
			'title'   => 'Instructions for Adding DKIM DNS Record for Domain:',
			'style'   => 'info', // Optional: 'success', 'info', 'warning', 'error'
			'subtitle'    => '<strong>Type :</strong> TXT <br>
			<strong>Name:</strong> ' . $selector . '._domainkey.' . $domain . '<br>
            <strong>Content :</strong> DNS Content ( Get DNS content on click "Click To Copy" button ) <br>
			<strong>Proxy Status :</strong> DNS only <br>
			<strong>TTL:</strong> Auto
			'
		);

		$fields[] = $dkim_status;
		$fields[] = $dkim_domain;
		$fields[] = $dkim_selector;
		$fields[] = $dkim_pub_key;
		$fields[] = $copy;
		$fields[] = $info;

		return $fields;
	}

	function setup_gui()
	{
		if (!class_exists('Redux')) {
			return;
		}
		$options = get_option(WPOVEN_SMTP_SURESEND_SLUG);
		$opt_name = WPOVEN_SMTP_SURESEND_SLUG;

		Redux::disable_demo();

		$args = array(
			'opt_name'                  => $opt_name,
			'display_name'              => 'WPOven SMTP Suresend',
			'display_version'           => ' ',
			//'menu_type'                 => 'menu',
			'allow_sub_menu'            => true,
			//	'menu_title'                => esc_html__('WPOven Plugins', 'WPOven Suresend'),
			'page_title'                => esc_html__('WPOven SMTP Suresend', 'WPOven Suresend'),
			'disable_google_fonts_link' => false,
			'admin_bar'                 => false,
			'admin_bar_icon'            => 'dashicons-portfolio',
			'admin_bar_priority'        => 90,
			'global_variable'           => $opt_name,
			'dev_mode'                  => false,
			'customizer'                => false,
			'open_expanded'             => false,
			'disable_save_warn'         => false,
			'page_priority'             => 90,
			'page_parent'               => 'themes.php',
			'page_permissions'          => 'manage_options',
			'menu_icon'                 => plugin_dir_url(__FILE__) . '/img/logo.png',
			'last_tab'                  => '',
			'page_icon'                 => 'icon-themes',
			'page_slug'                 => $opt_name,
			'save_defaults'             => false,
			'default_show'              => false,
			'default_mark'              => '',
			'show_import_export'        => false,
			'transient_time'            => 60 * MINUTE_IN_SECONDS,
			'output'                    => false,
			'output_tag'                => false,
			//'footer_credit'             => 'Please rate WPOven SMTP Suresend ★★★★★ on WordPress.org to support us. Thank you!',
			'footer_credit'             => ' ',
			'use_cdn'                   => false,
			'admin_theme'               => 'wp',
			'flyout_submenus'           => true,
			'font_display'              => 'swap',
			'hide_reset'                => true,
			'database'                  => '',
			'network_admin'           => '',
			'search'                    => false,
			'hide_expand'            => true,
		);

		Redux::set_args($opt_name, $args);

		Redux::set_section(
			$opt_name,
			array(
				'title'      => esc_html__('SMTP Settings', 'WPOven Suresend'),
				'id'         => 'smtp-suresend',
				'subsection' => false,
				'heading'    => ' SMTP Configuration Settings',
				'desc'       => 'WPoven SMTP Suresend ensures reliable email delivery with easy SMTP setup, connection testing, debugging, and secure configuration for WordPress.',
				'fields'     => $this->wpoven_smtp_suresend_general_settings($options),
			)
		);

		Redux::set_section(
			$opt_name,
			array(
				'title'      => esc_html__('DKIM Settings', 'WPOven Suresend'),
				'id'         => 'dkim-settings',
				'subsection' => true,
				'parent'     => 'smtp-suresend',
				'heading'    => 'DKIM Public Key',
				'desc'       => 'Set up DKIM public key for domain email authentication.',
				'icon'       => 'el el-cloud',
				'fields' => $this->wpoven_smtp_suresend_dkim_settings(),
			)
		);

		Redux::set_section(
			$opt_name,
			array(
				'title'      => esc_html__('SMTP Mail Test', 'WPOven Suresend'),
				'id'         => 'smtp-test',
				'subsection' => true,
				'parent'     => 'smtp-suresend',
				'heading'    => 'Send a Test Email',
				'desc'       => 'Test SMTP mail functionality by sending a sample email.',
				'icon'       => 'el el-envelope',
				'fields' => $this->wpoven_smtp_suresend_smtp_test_settings($options),
			)
		);

		Redux::set_section(
			$opt_name,
			array(
				'title'      => '<a href="admin.php?page=wpoven-smtp-suresend-smtp-logs"  class="smtp-logs"> <span class="group_title">SMTP Logs</span></a>',
				'id'         => 'smtp-logs',
				'class'      => 'smtp-logs',
				'parent'     => 'smtp-suresend',
				'subsection' => true,
				'icon'       => '', //el el-list
			)
		);

		if (!function_exists('compiler_action')) {
			/**
			 * This is a test function that will let you see when the compiler hook occurs.
			 * It only runs if a field's value has changed and compiler=>true is set.
			 *
			 * @param array  $options        Options values.
			 * @param string $css            Compiler selector CSS values  compiler => array( CSS SELECTORS ).
			 * @param array  $changed_values Any values changed since last save.
			 */
			function compiler_action(array $options, string $css, array $changed_values)
			{
				echo '<h1>The compiler hook has run!</h1>';
				echo '<pre>';
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions
				print_r($changed_values); // Values that have changed since the last save.
				echo '<br/>';
				print_r($options); //Option values.
				echo '<br/>';
				print_r($css); // Compiler selector CSS values compiler => array( CSS SELECTORS ).
				echo '</pre>';
			}
		}

		if (!function_exists('redux_validate_callback_function')) {
			/**
			 * Custom function for the callback validation referenced above
			 *
			 * @param array $field          Field array.
			 * @param mixed $value          New value.
			 * @param mixed $existing_value Existing value.
			 *
			 * @return array
			 */
			function redux_validate_callback_function(array $field, $value, $existing_value): array
			{
				$error   = false;
				$warning = false;

				// Do your validation.
				if (1 === (int) $value) {
					$error = true;
					$value = $existing_value;
				} elseif (2 === (int) $value) {
					$warning = true;
					$value   = $existing_value;
				}

				$return['value'] = $value;

				if (true === $error) {
					$field['msg']    = 'your custom error message';
					$return['error'] = $field;
				}

				if (true === $warning) {
					$field['msg']      = 'your custom warning message';
					$return['warning'] = $field;
				}

				return $return;
			}
		}

		if (!function_exists('dynamic_section')) {
			/**
			 * Custom function for filtering the section array.
			 * Good for child themes to override or add to the sections.
			 * Simply include this function in the child themes functions.php file.
			 * NOTE: the defined constants for URLs and directories will NOT be available at this point in a child theme,
			 * so you must use get_template_directory_uri() if you want to use any of the built-in icons.
			 *
			 * @param array $sections Section array.
			 *
			 * @return array
			 */
			function dynamic_section(array $sections): array
			{
				$sections[] = array(
					'title'  => esc_html__('Section via hook', 'WPOven Suresend'),
					'desc'   => '<p class="description">' . esc_html__('This is a section created by adding a filter to the sections array. Can be used by child themes to add/remove sections from the options.', 'WPOven Suresend') . '</p>',
					'icon'   => 'el el-paper-clip',

					// Leave this as a blank section, no options just some intro text set above.
					'fields' => array(),
				);

				return $sections;
			}
		}

		if (!function_exists('change_arguments')) {
			/**
			 * Filter hook for filtering the args.
			 * Good for child themes to override or add to the args array.
			 * It can also be used in other functions.
			 *
			 * @param array $args Global arguments array.
			 *
			 * @return array
			 */
			function change_arguments(array $args): array
			{
				$args['dev_mode'] = false;

				return $args;
			}
		}

		if (!function_exists('change_defaults')) {
			/**
			 * Filter hook for filtering the default value of any given field. Very useful in development mode.
			 *
			 * @param array $defaults Default value array.
			 *
			 * @return array
			 */
			function change_defaults(array $defaults): array
			{
				$defaults['str_replace'] = esc_html__('Testing filter hook!', 'WPOven Suresend');

				return $defaults;
			}
		}

		if (!function_exists('redux_custom_sanitize')) {
			/**
			 * Function to be used if the field sanitizes argument.
			 * Return value MUST be formatted or cleaned text to display.
			 *
			 * @param string $value Value to evaluate or clean.  Required.
			 *
			 * @return string
			 */
			function redux_custom_sanitize(string $value): string
			{
				$return = '';

				foreach (explode(' ', $value) as $w) {
					foreach (str_split($w) as $k => $v) {
						if (($k + 1) % 2 !== 0 && ctype_alpha($v)) {
							$return .= mb_strtoupper($v);
						} else {
							$return .= $v;
						}
					}
					$return .= ' ';
				}

				return $return;
			}
		}
	}

	/**
	 * Create SMTP logs pages.
	 */
	function smtp_logs()
	{
		echo '<div class="wrap"><h1>WPOven SMTP Logs</h1>';
		echo '<form method="post">';

		$table = new WPOven_SMTP_Suresend_List_Table();
		$table->prepare_items();
		$table->search_box('search', 'search_id');
		$table->display();

		echo '</div></form>';
	}

	/**
	 * Add a admin menu.
	 */
	function wpoven_smtp_suresend_menu()
	{
		$to = '';
		$subject = '';
		$message = '';
		$headers = '';
		$attachments = array();
		wp_mail($to, $subject, $message, $headers, $attachments);

		add_menu_page('WPOven Plugins', 'WPOven Plugins', '', 'wpoven', 'manage_options', plugin_dir_url(__FILE__) . '/img/logo.png');
		add_submenu_page('wpoven', 'SMTP Suresend', 'SMTP Suresend', 'manage_options', 'admin.php?page=wpoven-smtp-suresend&tab=1');
		add_submenu_page('admin.php?page=wpoven-smtp-suresend&tab=1', 'SMTP Logs', 'SMTP Logs', 'manage_options', WPOVEN_SMTP_SURESEND_SLUG . '-smtp-logs', array($this, 'smtp_logs'));
	}

	/**
	 * Hook to add the admin menu.
	 */
	public function admin_main(Wpoven_Smtp_Suresend $wpoven_smtp_suresend)
	{
		$this->_wpoven_smtp_suresend = $wpoven_smtp_suresend;
		$this->createDkimPublicKeys($this->dkim_folder_path);
		add_action('admin_menu', array($this, 'wpoven_smtp_suresend_menu'));
		$this->setup_gui();
	}
}
