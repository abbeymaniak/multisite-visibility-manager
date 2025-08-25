<?php

/**
 *
 */



if (!class_exists('Multisite_Visibility_Manager')){


class Multisite_Visibility_Manager
{

	public function __construct() {
		if (is_multisite() && is_main_site()) {
			add_action('network_admin_menu', [$this, 'register_menu']);
			add_action('network_admin_notices', [$this, 'admin_notices']);

			// AJAX actions
			add_action('wp_ajax_update_visibility', [$this, 'ajax_update_visibility']);
			add_action('wp_ajax_bulk_update_visibility', [$this, 'ajax_bulk_update_visibility']);

			// Enqueue scripts
			add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
		}
	}

	public function register_menu() {
		add_menu_page(
			'Multisite Visibility',
			'Visibility Manager',
			'manage_network_options',
			'multisite-visibility-manager',
			[$this, 'settings_page'],
			'dashicons-visibility',
			90
		);
	}


	public function admin_notices() {
		if (isset($_GET['visibility_updated']) && $_GET['visibility_updated'] === 'true') {
			echo '<div class="notice notice-success is-dismissible"><p>Visibility settings updated successfully.</p></div>';
		}
	}

	public function enqueue_scripts($hook) {
		if ($hook !== 'toplevel_page_multisite-visibility-manager') return;

		wp_enqueue_style('multisite-visibility-style-css', plugin_dir_url(__DIR__) . 'assets/styles/multisite-visibility-manager.css', [], '3.1');

		wp_enqueue_script(
			'multisite-visibility-manager-js',
			plugin_dir_url(__DIR__) . '/assets/scripts/multisite-visibility-manager.js',
			['jquery'],
			'3.0',
			true
		);

		wp_localize_script('multisite-visibility-manager-js', 'MVM_AJAX', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce'    => wp_create_nonce('update_visibility_nonce')
		]);
	}


	public function settings_page() {
		if (!current_user_can('manage_network_options')) {
			wp_die(__('You do not have permission to access this page.','multisite-visibility-manager'));
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
					if(isset($_GET['search']) && !empty($_GET['search'])) {
						$url = $_SERVER['REQUEST_URI'];
						$new_url = remove_query_arg('search', $url);
					?>
					<a href="<?php echo esc_url($new_url); ?>" class="button">
						Reset
					</a>
						<?php
						}else{
					?>
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
					if ($search && stripos($site->domain . $site->path, $search) === false) continue;

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


	public function ajax_update_visibility() {
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

	public function ajax_bulk_update_visibility() {
		check_ajax_referer('update_visibility_nonce');

		if (!current_user_can('manage_network_options')) {
			wp_send_json_error(__('You are Unauthorized','multisite-visibility-manager'));
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
