<?php

	/*
	 * Plugin Name: rapidmail newsletter marketing
	 * Description: Widget für die Integration eines rapidmail Anmeldeformulars in der Sidebar sowie ein Plugin für die Gewinnung von Abonnenten über die Kommentarfunktion.
	 * Author: rapidmail GmbH
	 * Version: 1.0
	 * Author URI: http://www.rapidmail.de
	 * License: GPLv2 or later
	 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
	 * Min WP Version: 3.0
	 */

	$domain_url = get_option('siteurl');

	$rm_plugin_url = plugin_dir_url( __FILE__ );
	$rm_source_name = 'WordPress comment';

	include('rapidmail.class.php');

	/* ----------- Administration part ------------ */

	function rm_menu() {
		add_options_page('Rapidmail Options', 'Rapidmail', 8, __FILE__, 'rm_options');
	}

	function rm_options() {

		global $rm_plugin_url, $rm_prefix, $api, $domain_url;

		$rm_options = get_option('rm_options');
		$rapidmail_authenticated = false;
		$error_msg = array();

		if (!$rm_options['rbc_text']) {
			$rm_options['rbc_text'] = 'Newsletter abonnieren (Jederzeit wieder abbestellbar)';
		}

		if ($_POST['key_save']) {

			$rm_options['api_key'] = sanitize_text_field($_POST['api_key']);
			$rm_options['recipient_list_id'] = (int) sanitize_text_field($_POST['recipient_list_id']);
			$rm_options['node_id'] = (int) sanitize_text_field($_POST['node_id']);

			if ((int)$rm_options['node_id'] == 0) {
				$error_msg[] = 'Node-ID muss ausgefüllt und gr&ouml;&szlig;er als 0 sein.';
			}

			if ((int)$rm_options['recipient_list_id'] == 0) {
				$error_msg[] = 'ID der Empfängerliste muss ausgefüllt und gr&ouml;&szlig;er als 0 sein.';
			}

			if (empty($rm_options['api_key'])) {
				$error_msg[] = 'API-Schlüssel muss ausgefüllt sein.';
			}

			if (count($error_msg) == 0) {

				$rapidmail = new rapidmail($rm_options['node_id'], $rm_options['recipient_list_id'], $rm_options['api_key']);

				try {

					$meta_data = $rapidmail->get_metadata();

					if (isset($meta_data['api_data']['metadata']['name'])) {

						$recipient_list_name = $meta_data['api_data']['metadata']['name'];
						$rapidmail_authenticated = true;

						$rm_options['subscription_form_url'] = $meta_data['api_data']['metadata']['subscription_form_url'];

					}

				} catch (Exception $e) {
					$error_msg[] = 'API antwortet mit einem Fehler: "' . $e->getMessage() . '"';
				}

			}

			if ($rapidmail_authenticated) {
				update_option('rm_options', $rm_options);
			}

			if ($_POST['config_save']) {

				$rm_options['rbc'] = $_POST['rbc'];
				$rm_options['rbc_text'] = sanitize_text_field($_POST['rbc_text']);

				update_option('rm_options', $rm_options);

				// Update widget form on admin changes

				ob_start();

				include('subscription_form.php');
				$form_data = ob_get_contents();

				ob_end_clean();

				$form = '<aside id="rapidmail-form" class="widget widget_rapidmail"><h1 class="widget-title">' . $rm_options["widget_title"] . '</h1>' . $form_data . '</aside>';

				update_option('rm_form', $form);

			}

		} else {

			if ((int)$rm_options['node_id'] > 0 && (int)$rm_options['recipient_list_id'] > 0 && !empty($rm_options['api_key'])) {

				$rapidmail = new rapidmail((int)$rm_options['node_id'], (int)$rm_options['recipient_list_id'], $rm_options['api_key']);

				try {

					$meta_data = $rapidmail->get_metadata();

					if (isset($meta_data['api_data']['metadata']['name'])) {
						$recipient_list_name = $meta_data['api_data']['metadata']['name'];
						$rapidmail_authenticated = true;
					}

				} catch (Exception $e) {
					$error_msg[] = 'API antwortet mit einem Fehler: "' . $e->getMessage() . '"';
				}

			}

		}

		?>

		<div class="wrap">
			<h2>Einstellungen › rapidmail</h2>
			<p>Bitte hinterlegen Sie hier Ihre rapidmail API Zugangsdaten. <br />Wenn Sie noch kein Kunde bei rapidmail sind, können Sie sich hier kostenlos anmelden: <a href="https://www.rapidmail.de/anmelden?pid=125&utm_source=wp-plugin&utm_medium=Plugin&utm_campaign=Wordpress" target="_blank">Jetzt kostenlos bei rapidmail anmelden!</a></p>
		</div>

		<h3>Zugangsdaten hinterlegen</h3>

		<form id="rm_form" name="rm_form" enctype="application/x-www-form-urlencoded"  method="post" action="<?php str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
			<table width="100%" class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="api_key">API-Schlüssel:</label></th>
						<td>
							<input type="text" class="regular-text code" value="<?php _e($rm_options['api_key']); ?>" id="api_key" name="api_key">
							<?php if ($rapidmail_authenticated) { ?>
								<img src="<?php _e($rm_plugin_url); ?>/images/ok.png" />
							<?php } ?>
						</td>
					</tr>

					<tr>
						<th scope="row"><label for="recipient_list_id">ID der Empfängerliste:</label></th>
						<td>
							<input type="text" class="regular-text code" value="<?php _e($rm_options['recipient_list_id']); ?>" id="recipient_list_id" name="recipient_list_id">
							<?php if ($rapidmail_authenticated) { ?>
								<img src="<?php _e($rm_plugin_url); ?>/images/ok.png" />
							<?php } ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="node_id">Node ID:</label></th>
						<td>
							<input type="text" class="regular-text code" value="<?php _e($rm_options['node_id']); ?>" id="node_id" name="node_id">
							<?php if ($rapidmail_authenticated) { ?>
								<img src="<?php _e($rm_plugin_url); ?>/images/ok.png" />
							<?php } ?>
						</td>
					</tr>

				</tbody>
			</table>

			<?php if (!$rapidmail_authenticated) { ?>

				<p class="submit">
					<input type="submit" value="Prüfen und Speichern" class="button button-primary" id="submit" name="submit">
				</p>

				<?php

					if (count($error_msg) > 0) {
						echo '<p><span style="font-weight: bold; color: #cc2222;">' . implode('<br />- ', $error_msg) . '</span></p>';
					}

				?>

				<small>Den API Key, die ID der Empfängerliste und Node-ID finden Sie in Ihrem rapidmail Kundencenter unter Account &gt; API &gt; Einstellungen</small>

			<?php } ?>

			<input type="hidden" name="key_save" value="1" />

			<?php

				if ($rapidmail_authenticated) {

					echo '
							<h3>Abonnentengewinnung &uuml;ber Kommentare</h3>
							<p>
								Durch Aktivierung dieser Funktion wird das Kommentarformular in Ihrem Blog mit einer Newsletter-Bestellm&ouml;glichkeit erweitert.<br />
								Setzt der Benutzer beim Kommentieren einen Haken erhält er eine Bestätigungs-E-Mail (Double-Opt-In).
								Nach einem durch Klick auf den Best&auml;tigungslink ist er als aktiver Empfänger in der Empf&auml;ngerliste eingetragen.*
							</p>
							<table class="form-table">
								<tbody>
									<tr>
										<th scope="row"><label for="rbc">Abonenntengewinnung &uuml;ber Kommentare aktivieren:</label></th>
										<td><input type="checkbox" value="checked" ' . $rm_options['rbc'] . ' id="rbc" name="rbc" /> Ja</td>
									</tr>
									<tr>
										<th scope="row"><label for="rbc_text">Beschreibung:</label></th>
										<td><input type="text" class="regular-text code" value="' . $rm_options['rbc_text'] . '" id="rbc_text" name="rbc_text"></td>
									</tr>
								</tbody>
							</table>

							<p class="submit">
								<input type="submit" value="Änderungen übernehmen" class="button button-primary" id="submit" name="submit">
							</p>

							<p>
								*Wird das Anmeldeh&auml;ckchen nicht automatisch in das Kommentarformular eingef&uuml;gt, platzieren Sie bitte den folgenden Code innerhalb des "&lt;FORM&gt;"-Tags der Datei "comments.php" in Ihrem aktiven Theme: <i>&lt;?php rm_add_checkbox();?&gt;</i>
							</p>

							<input type="hidden" name="config_save" value="1" />
					';

				}

			?>

		</form>

		<?php
	}

	function rm_add_checkbox() {
		$rm_options = get_option('rm_options');
		echo '<input type="checkbox" name="rm_rbc_subscribe" id="rm_rbc_subscribe" value="yes" />&nbsp;&nbsp;' . $rm_options['rbc_text'];
	}

	function rm_rbc_subscribe() {

		global $_POST, $user_email, $rm_source_name, $current_user; get_currentuserinfo();

		if (sanitize_text_field($_POST['comment']) && isset($_POST['rm_rbc_subscribe']) && $_POST['rm_rbc_subscribe'] == 'yes') {

			$rm_options = get_option('rm_options');

			if (!$name = sanitize_text_field($_POST['author'])) {
				$firstName = $current_user->user_firstname;
				$lastName = $current_user->user_lastname;
			} else {

		        $nameArray = explode(' ', $name);
				$firstName = $nameArray[0];

				if (isset($nameArray[1])) {
				    $lastName = $nameArray[1];
				} else {
				    $lastName = '';
				}
			}

			if (!$email = sanitize_text_field($_POST['email'])) {
				$email = $user_email;
			}

			$rm_receiver = array(
				'firstname' => $firstName,
				'lastname' => $lastName,
				'status' => 'new',
				'created_ip' => $_SERVER['REMOTE_ADDR'],
				'created_host' => $_SERVER['REMOTE_HOST'],
				'activationmail' => 'yes'
			);

			if ($rm_options['node_id'] > 0 && $rm_options['recipient_list_id'] > 0 && !empty($rm_options['api_key'])) {

				$rapidmail = new rapidmail((int)$rm_options['node_id'], (int)$rm_options['recipient_list_id'], $rm_options['api_key']);

				try {

					$recipient_data = $rapidmail->get_recipient($email);

					if (isset($recipient_data['api_data']['recipient']['status']) && $recipient_data['api_data']['recipient']['status'] === 'active') {
						unset($rm_receiver['status'], $rm_receiver['activationmail']);
					}

					$rapidmail->edit_recipient($email, $rm_receiver);

				} catch (Exception $e) {

					if ($e->getMessage() === '(551) Recipient with given e-mail not found.') {
						$rapidmail->add_recipient($email, $rm_receiver);
					}

				}

			}

		}

	}

	/* ------------------ Widget part ---------------- */

	function rm_widget_init() {

		if (!function_exists('register_sidebar_widget')) {
			return;
		}

		function rm_widget() {
			echo get_option('rm_form');
		}

		function rm_widget_options() {

			$rm_options = get_option('rm_options');
			$rapidmail_authenticated = false;
			$error_msg = '';

			if ((int)$rm_options['node_id'] > 0 && (int)$rm_options['recipient_list_id'] > 0 && !empty($rm_options['api_key'])) {

				$rapidmail = new rapidmail((int)$rm_options['node_id'], (int)$rm_options['recipient_list_id'], $rm_options['api_key']);

				try {

					$meta_data = $rapidmail->get_metadata();

					if (isset($meta_data['api_data']['metadata']['name'])) {
						$recipient_list_name = $meta_data['api_data']['metadata']['name'];
						$rapidmail_authenticated = true;
					}

				} catch (Exception $e) {
					$error_msg = $e->getMessage();
				}

			}

			if (!$rm_options['widget_init']) {

				$rm_options['widget_title'] = 'Newsletter Anmeldung';
				$rm_options['widget_text'] = 'Tragen Sie sich hier in unseren Newsletter ein';
				$rm_options['widget_submit_text'] = 'Eintragen';
				$rm_options['widget_init'] = $_POST['rm_widget_submit'];

			}

			if ($_POST['rm_widget_submit']) {

				$rm_options['widget_title'] = sanitize_text_field($_POST['rm_widget_title']);
				$rm_options['widget_text'] = sanitize_text_field($_POST['rm_widget_text']);
				$rm_options['widget_submit_text'] = sanitize_text_field($_POST['rm_submit_text']);
				$rm_options['widget_init'] = $_POST['rm_widget_submit'];

				update_option('rm_options', $rm_options);

				ob_start();

				include('subscription_form.php');
				$form_data = ob_get_contents();

				ob_end_clean();

				$form = '<aside id="rapidmail-form" class="widget widget_rapidmail"><h1 class="widget-title">' . $rm_options["widget_title"] . '</h1>' . $form_data . '</aside>';

				update_option('rm_form', $form);
			}

			$rm_display = 'none';

			?>

			<label for="rm_widget_title"><strong><?php _e('&Uuml;berschrift:'); ?></strong></label>
			<input style="width: 100%; margin-bottom:1em;" id="rm_widget_title" name="rm_widget_title" type="text" value="<?php _e(htmlspecialchars(stripslashes($rm_options['widget_title']))); ?>" />

			<br/>

			<?php

				if (!$rapidmail_authenticated) {

					$rm_display = 'block';

					?>

					<span style="color:#ff2222;">
						<b>Status: </b>Plugin nicht konfiguriert<br />
						<?php echo $error_msg; ?>
						<a href="options-general.php?page=rapidmail-newsletter-marketing/rapidmail_plugin.php">rapidmail Plugin konfigurieren</a>
					</span>
					<hr />

					<?php
				}

			?>

			<input type="hidden" name="rm_widget_submit" value="true">

			<?php
		}

		register_sidebar_widget(array('rapidmail', 'rapidmail'), 'rm_widget');
		register_widget_control(array('rapidmail', 'rapidmail'), 'rm_widget_options');

	}

	/* ------------------ hooks ---------------- */

	add_action('widgets_init', 'rm_widget_init');
	add_action('admin_menu', 'rm_menu');

	$rm_options = get_option('rm_options');

	if ($rm_options['rbc']) {
		add_action('comment_form', 'rm_add_checkbox');
		add_action('comment_post', 'rm_rbc_subscribe', 50);
	}

?>
