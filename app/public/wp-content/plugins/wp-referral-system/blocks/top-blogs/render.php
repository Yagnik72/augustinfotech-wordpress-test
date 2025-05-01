<?php
/**
 * Server-side rendering of the top blogs block
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function render_top_blogs_block($attributes) {
    $orderBy = isset($attributes['orderBy']) ? $attributes['orderBy'] : 'DESC';
    $order = isset($attributes['order']) ? $attributes['order'] : 'publishDate';
    $numberOfPosts = isset($attributes['numberOfPosts']) ? $attributes['numberOfPosts'] : 5;

    $args = array(
        'post_type' => 'post',
        'posts_per_page' => $numberOfPosts,
        'order' => $orderBy,
        'orderby' => $order === 'name' ? 'title' : 'date'
    );

    $query = new WP_Query($args);
    $output = '<div class="wp-referral-system-top-blogs">';
    $output .= '<div class="blogs-grid">';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            
            // Get featured image
            $image = get_the_post_thumbnail_url(get_the_ID(), array(300, 240));
            if (!$image) {
                $image = plugins_url('assets/images/default-blog.jpeg', dirname(dirname(__FILE__)));
            }

            $output .= '<div class="blog-item">';
            
            // Blog Image
            $output .= '<div class="blog-image">';
            $output .= '<a href="' . esc_url(get_permalink()) . '">';
            $output .= '<img src="' . esc_url($image) . '" alt="' . esc_attr(get_the_title()) . '" width="300" height="240">';
            $output .= '</a>';
            $output .= '</div>';
            
            // Blog Title
            $output .= '<h3 class="blog-title">';
            $output .= '<a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a>';
            $output .= '</h3>';
            
            // Blog Description
            $output .= '<div class="blog-description">';
            $output .= wp_trim_words(get_the_excerpt(), 20);
            $output .= '</div>';
            
            $output .= '</div>'; // .blog-item
        }
    } else {
        $output .= '<p>No posts found.</p>';
    }

    $output .= '</div>'; // .blogs-grid
    $output .= '</div>'; // .wp-referral-system-top-blogs

    wp_reset_postdata();

    return $output;
} 