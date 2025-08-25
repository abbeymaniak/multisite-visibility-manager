<?php

/**
 * Multisite Visibility Manager
 *
 * @category Plugin
 * @package  Multisite_Visibility_Manager
 * @author   Abiodun Paul Ogunnaike <primastech101@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl-2.0.txt GPLv2 or later
 * @link     https://github.com/abbeymaniak/multisite-visibility-manager
 *
 * Requires at least: 6.0
 * Requires PHP:      7.4
 */

if (!class_exists('Multisite_Visibility_Manager')) {

	/**
	 * This is the Multisite Visibility Manager class.
	 *
	 * @category Plugin
	 * @package  Multisite_Visibility_Manager
	 * @author   Abiodun Paul Ogunnaike <primastech101@gmail.com>
	 * @license  http://www.gnu.org/licenses/gpl-2.0.txt GPLv2 or later
	 * @link     https://github.com/abbeymaniak/multisite-visibility-manager
	 */
	class Multisite_Visibility_Manager
	{

		/**
		 * Constructor to initialize the plugin.
		 * 
		 * @return void
		 */
		public function __construct()
		{
			if (is_multisite() && is_main_site()) {
				add_action('network_admin_menu', [$this, 'registerMenu']);
				add_action('network_adminNotices', [$this, 'adminNotices']);

				// AJAX actions
				add_action('wp_ajaxUpdateVisibility', [$this, 'ajaxUpdateVisibility']);
				add_action('wp_ajaxBulkUpdateVisibility', [$this, 'ajaxBulkUpdateVisibility']);

				// Enqueue scripts
				add_action('admin_enqueueScripts', [$this, 'enqueueScripts']);
			}
		}

		/**
		 * Register the admin menu for the plugin.
		 *
		 * @return void
		 */
		public function registerMenu()
		{
			add_menu_page(
				'Multisite Visibility',
				'Visibility Manager',
				'manage_network_options',
				'multisite-visibility-manager',
				[$this, 'settingsPage'],
				'dashicons-visibility',
				90
			);
		}

		/**
		 * Display admin notices.
		 *
		 * @return void
		 */
		public function adminNotices()
		{
			if (isset($_GET['visibility_updated']) && $_GET['visibility_updated'] === 'true') {
				echo '<div class="notice notice-success is-dismissible"><p>Visibility settings updated successfully.</p></div>';
			}
		}

		/**
		 * Enqueue scripts and styles for the admin area.
		 *
		 * @param string $hook The current admin page hook.
		 * 
		 * @return void|null
		 */
		public function enqueueScripts($hook)
		{
			if ($hook !== 'toplevel_page_multisite-visibility-manager') {
				return;
			}


			wp_enqueue_style('multisite-visibility-style-css', plugin_dir_url(__DIR__) . 'assets/styles/multisite-visibility-manager.css', [], '3.1');

			wp_enqueue_script(
				'multisite-visibility-manager-js',
				plugin_dir_url(__DIR__) . '/assets/scripts/multisite-visibility-manager.js',
				['jquery'],
				'3.0',
				true
			);

			wp_localize_script(
				'multisite-visibility-manager-js',
				'MVM_AJAX',
				[
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce'    => wp_create_nonce('update_visibility_nonce')
				]
			);
		}

		/**
		 * Render the settings page for the plugin.
		 *
		 * @return void
		 */
		public function settingsPage()
		{
			if (!current_user_can('manage_network_options')) {
				wp_die(__('You do not have permission to access this page.', 'multisite-visibility-manager'));
			}

			$search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
			$sites = get_sites(['number' => 0]);
?>
			<div class="wrap">
				<h1>Multisite Visibility Manager</h1>
				<form method="get">
					<input type="hidden" name="page" value="multisite-visibility-manager">
					<p>
						<input type="text" name="search" value="<?php echo esc_attr($search); ?>" placeholder="Search by domain or path" />
						<?php
						if (isset($_GET['search']) && !empty($_GET['search'])) {
							$url = $_SERVER['REQUEST_URI'];
							$new_url = remove_query_arg('search', $url);
						?>
							<a href="<?php echo esc_url($new_url); ?>" class="button">
								Reset
							</a>
						<?php } else { ?>
							<button type="submit" class="button">Search</button>
						<?php
						}
						?>

					</p>
				</form>

				<div style="margin: 10px 0;">
					<select id="bulk-action">
						<option value="">Bulk Actions</option>
						<option value="discourage">Discourage All</option>
						<option value="allow">Allow All</option>
					</select>
					<button id="apply-bulk" class="button button-secondary">Apply</button>
				</div>

				<table class="widefat fixed striped" id="visibility-table">
					<thead>
						<tr>
							<th><input type="checkbox" id="select-all" /></th>
							<th>Site</th>
							<th>Domain</th>
							<th>Status</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($sites as $site) :
							if ($search && stripos($site->domain . $site->path, $search) === false) {
								continue;
							}


							switch_to_blog($site->blog_id);
							$is_discouraged = get_option('blog_public') == 0;
							restore_current_blog();
						?>
							<tr>
								<td><input type="checkbox" class="site-checkbox" data-site="<?php echo esc_attr($site->blog_id); ?>" /></td>
								<td><?php echo esc_html($site->blogname); ?></td>
								<td><?php echo esc_html($site->domain . $site->path); ?></td>
								<td class="status"><?php echo $is_discouraged ? 'Discouraged' : 'Allowed'; ?></td>
								<td>
									<label>
										<input type="checkbox" class="visibility-toggle" data-site="<?php echo esc_attr($site->blog_id); ?>" <?php checked($is_discouraged); ?> />
									</label>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<!-- Update Progress Modal -->
				<div id="progress-modal" class="progress-modal hidden">
					<div class="progress-box">
						<h2>Updating Sites...</h2>
						<div class="progress-bar">
							<div class="progress-fill"></div>
						</div>
						<p class="progress-text">0% Complete</p>
					</div>
				</div>

			</div>
<?php
		}

		/**
		 * Handle AJAX request to update visibility for a single site.
		 *
		 * @return void
		 */
		public function ajaxUpdateVisibility()
		{
			check_ajax_referer('update_visibility_nonce');

			if (!current_user_can('manage_network_options')) {
				wp_send_json_error('You are Unauthorized');
			}

			$site_id = isset($_POST['site_id']) ? intval($_POST['site_id']) : 0;
			$status  = isset($_POST['status']) && $_POST['status'] === 'true' ? 0 : 1;

			if ($site_id > 0) {
				switch_to_blog($site_id);
				update_option('blog_public', $status);
				restore_current_blog();

				wp_send_json_success(['message' => 'Visibility updated successfully']);
			}

			wp_send_json_error('Invalid site ID');
		}

		/**
		 * Handle AJAX request for bulk updating visibility.
		 *
		 * @return void
		 */
		public function ajaxBulkUpdateVisibility()
		{
			check_ajax_referer('update_visibility_nonce');

			if (!current_user_can('manage_network_options')) {
				wp_send_json_error(__('You are Unauthorized', 'multisite-visibility-manager'));
			}

			$site_ids = isset($_POST['site_ids']) ? array_map('intval', $_POST['site_ids']) : [];
			$status   = isset($_POST['status']) && $_POST['status'] === 'discourage' ? 0 : 1;

			if (!empty($site_ids)) {
				foreach ($site_ids as $site_id) {
					switch_to_blog($site_id);
					update_option('blog_public', $status);
					restore_current_blog();
				}

				wp_send_json_success(['message' => 'Bulk visibility update completed']);
			}

			wp_send_json_error('No sites selected for bulk update');
		}
	}
}
