<?php
/**
 * Plugin Name: Kuku Pine Cone of the Day
 * Description: Fetches a random pine cone image daily and exposes it via a custom REST API endpoint.
 * Version: 0.1
 * Author: Max Liao
 */

if (!defined('ABSPATH')) exit;

// Register REST endpoint
add_action('rest_api_init', function () {
  register_rest_route('kuku/v1', '/pinecone', [
    'methods' => 'GET',
    'callback' => function () {
      return get_option('kuku_daily_pinecone') ?: 'https://via.placeholder.com/300?text=Pine+Cone';
    },
  ]);
});

/**
 * Fetches a random pine cone image from the local assets folder.
 * If no images are found, returns a default image URL.
 *
 * @return string URL of the selected pine cone image.
 */
function kuku_fetch_random_pinecone() {
    // Directory containing pine cone images in the active child theme
    $image_dir = get_stylesheet_directory() . '/assets/pinecones/';
    $uri = get_stylesheet_directory_uri();
    if (!is_ssl()) {
        $uri = str_replace('https://', 'http://', $uri);
    }
    $image_url_base = $uri . '/assets/pinecones/';


    // Get all image files with the specified extensions
    $images = glob($image_dir . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    // If no images are found, return a default image URL
    if (!$images) {
        return 'https://media.istockphoto.com/id/505688040/photo/beautiful-fir-cone-isolated.jpg?s=612x612&w=0&k=20&c=g4SXX83g7pDO1792fXln9w3ypmOtG1a_B2ywpUDhxYo=';
    }

    // Randomly select an image from the list
    $selected = basename($images[array_rand($images)]);
    // Construct the URL for the selected image
    return $image_url_base . $selected;
}

// Save to option table daily
add_action('kuku_daily_event', function () {
  $pinecone_url = kuku_fetch_random_pinecone();
  update_option('kuku_daily_pinecone', $pinecone_url);
});

// Schedule cron events *after WP is fully loaded*
add_action('init', function () {
  if (!wp_next_scheduled('kuku_daily_event')) {
    wp_schedule_event(time(), 'daily', 'kuku_daily_event');
  }
});

// Force update on activation
register_activation_hook(__FILE__, function () {
  do_action('kuku_daily_event');
});
