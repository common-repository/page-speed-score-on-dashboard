<?php
/*
Plugin Name: Page Speed Score on Dashboard
Description: This plugin fetches page speed score on dashboard and displays as a metabox. It also provides a button for images for optimization purpose.
Version: 1.4.3
Author: PL
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

//---------------------------------------Register Dashboard Widgets---------------------------------------//

function plpsp_pagespeed_dashboard_widgets()
{
    wp_add_dashboard_widget('pagespeed_dashboard_widget', 'PageSpeed Score', 'plpsp_pagespeed_dashboard_widget_display');
}
add_action('wp_dashboard_setup', 'plpsp_pagespeed_dashboard_widgets');

function plpsp_enq_custom_pagespeed_script()
{
    wp_register_script('pagespeed-script', plugins_url('assets/script.js', __FILE__), array('jquery'), '1.3.0', true);

    $pagespeed_data = array(
        'ajax_url' => esc_url(admin_url('admin-ajax.php')),
        // 'clear_cache_nonce' => wp_create_nonce('clear_cache_nonce'),
        'scan_images_nonce' => wp_create_nonce('scan_images_nonce'),
        'image_size_nonce' => wp_create_nonce('image_size_nonce'),
        'fetch_pagespeed_scores_nonce' => wp_create_nonce('fetch_pagespeed_scores_nonce')
    );
    wp_localize_script('pagespeed-script', 'pagespeedData', $pagespeed_data);
    wp_enqueue_script('pagespeed-script');
}

add_action('admin_enqueue_scripts', 'plpsp_enq_custom_pagespeed_script');

function plpsp_enq_jspdf_scripts()
{
    wp_enqueue_script('jspdf', plugin_dir_url(__FILE__) . 'assets/jspdf.umd.min.js', array(), '2.5.1', true);
    wp_enqueue_script('jspdf-autotable', plugin_dir_url(__FILE__) . 'assets/jspdf.plugin.autotable.min.js', array('jspdf'), '3.8.2', true);
}
add_action('admin_enqueue_scripts', 'plpsp_enq_jspdf_scripts');

//---------------------------------------Main Function to Fetch PageSpeed Score---------------------------------------//

function plpsp_pagespeed_ftch_scores()
{
    $home_url = esc_url(home_url());
    $api_key = 'AIzaSyBlFi-7IjCsB4D7vSPW04Ecp3tq7BYQTAI';
    $api_url = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

    // Fetch desktop score
    $desktop_request_url = $api_url . '?url=' . urlencode($home_url) . '&key=' . $api_key . '&strategy=desktop';
    $desktop_response = wp_remote_get($desktop_request_url, array('timeout' => 200));

    // Fetch mobile score
    $mobile_request_url = $api_url . '?url=' . urlencode($home_url) . '&key=' . $api_key . '&strategy=mobile';
    $mobile_response = wp_remote_get($mobile_request_url, array('timeout' => 200));

    $result = array(
        'desktop' => array(
            'score' => null,
            'error' => null
        ),
        'mobile' => array(
            'score' => null,
            'error' => null
        )
    );

    // Error handling
    if (is_wp_error($desktop_response)) {
        $result['desktop']['error'] = sanitize_text_field($desktop_response->get_error_message());
    } else {
        $desktop_response_body = wp_remote_retrieve_body($desktop_response);
        $desktop_data = json_decode($desktop_response_body, true);
        if ($desktop_data !== null) {
            $result['desktop']['score'] = isset($desktop_data['lighthouseResult']['categories']['performance']['score']) ? intval($desktop_data['lighthouseResult']['categories']['performance']['score'] * 100) : null;
        }
    }

    if (is_wp_error($mobile_response)) {
        $result['mobile']['error'] = sanitize_text_field($mobile_response->get_error_message());
    } else {
        $mobile_response_body = wp_remote_retrieve_body($mobile_response);
        $mobile_data = json_decode($mobile_response_body, true);
        if ($mobile_data !== null) {
            $result['mobile']['score'] = isset($mobile_data['lighthouseResult']['categories']['performance']['score']) ? intval($mobile_data['lighthouseResult']['categories']['performance']['score'] * 100) : null;
        }
    }

    return $result;
}

//---------------------------------------Display PageSpeed Score on Dashboard Widget---------------------------------------//

function plpsp_pagespeed_dashboard_widget_display()
{
    ?>
    <div id="pagespeed-dashboard-widget">
        <h3>PageSpeed Scores</h3>
        <h4>Url : <a href="<?php echo esc_url(home_url()); ?>" target="_blank"><?php echo esc_html(home_url()); ?></a></h4>
        <button id="fetch-pagespeed-scores" class="button button-primary">Fetch Scores</button>
        <div id="pagespeed-scores-container"></div>
    </div>

    <!-- <div id="clear-cache-widget">
        <p><b>Note:</b> You must check your site after clearing the cache.<br>
            <b>If needed:</b> Rename the cache directory in <b>wp-content/</b> or <b>wp-content/uploads</b>.
            Once deleted, there is no way to recover it, so please check your site carefully before proceeding.
        </p>
        <h3>Clear Cache</h3>
        <button id="clear-cache-button" class="button button-primary">Clear Cache</button>
        <div id="clear-cache-message"></div>
    </div> -->

    <div id="scan-images-widget">
        <p><b>Note : </b> For the pdf press "Scan Image" then Press "Download PDF" button</p>
        <h3>Scan Images</h3>
        <button id="scan-images-button" class="button button-primary">Scan for Images</button></br></br>
        <div id="images-list-container"></div>
        <button id="download-pdf-button" class="button button-primary">Download PDF</button>
    </div>
    <?php
}

// //---------------------------------------AJAX Handler to Clear Cache---------------------------------------//
// function plpsp_clr_cache_callback()
// {
//     // Check nonce for security
//     if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'clear_cache_nonce')) {
//         wp_send_json_error(array('message' => 'Invalid nonce'));
//         return;
//     }

//     // Check if the current user has the required capability
//     if (!current_user_can('manage_options')) {
//         wp_send_json_error(array('message' => 'You do not have sufficient permissions to access this action.'));
//         return;
//     }

//     // Clear object cache
//     wp_cache_flush();

//     // Rename cache directories
//     $cache_dirs = [
//         WP_CONTENT_DIR . '/cache/',
//         WP_CONTENT_DIR . '/uploads/cache/'
//     ];

//     foreach ($cache_dirs as $dir) {
//         if (file_exists($dir)) {
//             plpsp_rnm_cache_dir($dir);
//         }
//     }

//     wp_send_json_success();
// }
// add_action('wp_ajax_clear_cache', 'plpsp_clr_cache_callback');

// function plpsp_rnm_cache_dir($target)
// {
//     global $wp_filesystem;

//     // Initialize the WP_Filesystem if it hasn't been already
//     if (empty($wp_filesystem)) {
//         require_once ABSPATH . 'wp-admin/includes/file.php';
//         WP_Filesystem();
//     }

//     // Ensure $wp_filesystem is available
//     if (!empty($wp_filesystem) && $wp_filesystem->is_dir($target)) {
//         $current_date = gmdate('Y-m-d_H-i-s'); 
//         $parent_dir = dirname($target);
//         $new_name = $parent_dir . '/cache-' . $current_date;

//         if ($wp_filesystem->move($target, $new_name)) {
//             error_log('Cache directory renamed to ' . $new_name);
//         } else {
//             error_log('Failed to rename cache directory: ' . $target);
//         }
//     }
// }

//---------------------------------------AJAX Handler to Fetch Scores---------------------------------------//

function plpsp_ftc_pagespeed_scores_callback()
{
    // Check nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'fetch_pagespeed_scores_nonce')) {
        wp_send_json_error(array('message' => 'Invalid nonce'));
        return;
    }

    // Fetch scores
    $scores = plpsp_pagespeed_ftch_scores();

    if ($scores['desktop']['score'] === null && $scores['desktop']['error'] !== null) {
        wp_send_json_error(array('message' => 'Error fetching desktop score: ' . esc_html($scores['desktop']['error'])));
    } elseif ($scores['mobile']['score'] === null && $scores['mobile']['error'] !== null) {
        wp_send_json_error(array('message' => 'Error fetching mobile score: ' . esc_html($scores['mobile']['error'])));
    } else {
        // Prepare response
        $response = array(
            'desktop_score' => intval($scores['desktop']['score']),
            'mobile_score' => intval($scores['mobile']['score'])
        );

        // Return JSON response
        wp_send_json_success($response);
    }
}
add_action('wp_ajax_fetch_pagespeed_scores', 'plpsp_ftc_pagespeed_scores_callback');

function plpsp_scn_images_callback()
{
    // Check nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'scan_images_nonce')) {
        wp_send_json_error(array('message' => 'Invalid nonce'));
        return;
    }

    $home_url = esc_url(home_url());
    $response = wp_remote_get($home_url);

    if (is_wp_error($response)) {
        wp_send_json_error(array('message' => 'Failed to fetch homepage.'));
    }

    $body = wp_remote_retrieve_body($response);
    preg_match_all('/<img[^>]+src="([^">]+)"/i', $body, $matches);

    $images = isset($matches[1]) ? array_map('esc_url_raw', $matches[1]) : array();
    wp_send_json_success(array('images' => $images));
}
add_action('wp_ajax_scan_images', 'plpsp_scn_images_callback');

function plpsp_ftch_image_size_callback()
{
    // Check nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'image_size_nonce')) {
        wp_send_json_error(array('message' => 'Invalid nonce'));
        return;
    }

    $image_url = esc_url_raw($_POST['image_url']);
    $image_response = wp_remote_head($image_url);

    if (is_wp_error($image_response)) {
        wp_send_json_error(array('message' => 'Failed to fetch image size.'));
    }

    $content_length = wp_remote_retrieve_header($image_response, 'content-length');
    if ($content_length) {
        wp_send_json_success(array('size' => intval($content_length)));
    } else {
        wp_send_json_error(array('message' => 'Content length not available.'));
    }
}
add_action('wp_ajax_fetch_image_size', 'plpsp_ftch_image_size_callback');
?>