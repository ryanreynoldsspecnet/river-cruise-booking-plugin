<?php
/*
Plugin Name: River Cruise Booking Form
Description: Custom booking form for Inspiration River Cruises.
Version: 1.0
Author: Your Name
*/

// Enqueue scripts and styles
function river_cruise_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('river-cruise-scripts', plugin_dir_url(__FILE__) . 'scripts.js', array('jquery'), '1.0', true);
    wp_enqueue_style('river-cruise-styles', plugin_dir_url(__FILE__) . 'styles.css');
}
add_action('wp_enqueue_scripts', 'river_cruise_enqueue_scripts');

// Shortcode for the booking form
function river_cruise_booking_form_shortcode() {
    ob_start();
    ?>
    <form id="river-cruise-booking-form">
        <label for="name">Full Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="phone">Cell Number:</label>
        <input type="text" id="phone" name="phone" required>

        <label for="cruise_date">Cruise Date:</label>
        <input type="date" id="cruise_date" name="cruise_date" required>

        <label for="seats">Number of Seats:</label>
        <input type="number" id="seats" name="seats" min="1" required>

        <div id="pricing-info"></div>

        <button type="submit">Submit</button>
    </form>
    <div id="form-response"></div>
    <?php
    return ob_get_clean();
}
add_shortcode('river_cruise_form', 'river_cruise_booking_form_shortcode');

// Handle form submission
function river_cruise_handle_form_submission() {
    if (!isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['phone']) || !isset($_POST['cruise_date']) || !isset($_POST['seats'])) {
        wp_send_json_error('Incomplete form submission.');
    }

    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $cruise_date = sanitize_text_field($_POST['cruise_date']);
    $seats = intval($_POST['seats']);

    $price_per_seat = 200;
    $minimum_charge = 1000;
    $total_cost = $seats * $price_per_seat;

    if ($total_cost < $minimum_charge) {
        $total_cost = $minimum_charge;
    }

    // Save booking to database
    global $wpdb;
    $table_name = $wpdb->prefix . 'river_cruise_bookings';
    $wpdb->insert($table_name, [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'cruise_date' => $cruise_date,
        'seats' => $seats,
        'total_cost' => $total_cost,
        'created_at' => current_time('mysql'),
    ]);

    // Send email confirmation
    wp_mail($email, 'Booking Confirmation', "Thank you for booking. Your total cost is R$total_cost.");

    wp_send_json_success("Booking confirmed. Total cost: R$total_cost.");
}
add_action('wp_ajax_river_cruise_submit', 'river_cruise_handle_form_submission');
add_action('wp_ajax_nopriv_river_cruise_submit', 'river_cruise_handle_form_submission');

// Create database table on plugin activation
function river_cruise_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'river_cruise_bookings';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email text NOT NULL,
        phone text NOT NULL,
        cruise_date date NOT NULL,
        seats smallint NOT NULL,
        total_cost float NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'river_cruise_activate');
