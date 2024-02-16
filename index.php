<?php
/*
Plugin Name: Bulk Page & Posts Creator
Plugin URI: https://github.com/brettnzl/Bulk-Page-Posts-Creator
Description: This plugin allows you to bulk create pages or posts.
Version: 1.0
Author: Brett Ransley
Author URI: https://revibedigital.co.nz/
*/

function bppc_admin_menu() {
    add_menu_page( 'Bulk Page & Posts Creator', 'Bulk Page & Posts Creator', 'manage_options', 'bppc-admin-page', 'bppc_admin_page_display', 'dashicons-admin-page', 6 );
}
add_action( 'admin_menu', 'bppc_admin_menu' );

function bppc_admin_scripts() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_style( 'bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.min.css', array(), '5.3.3', 'all' );
    wp_enqueue_script( 'bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), '5.3.3', true );
    wp_enqueue_script( 'bppc-js', plugin_dir_url( __FILE__ ) . 'bppc.js', array( 'jquery' ), '1.0', true );
    wp_localize_script( 'bppc-js', 'bppc_vars', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'admin_enqueue_scripts', 'bppc_admin_scripts' );

function bppc_create_page() {
    $title = sanitize_text_field($_POST['title']);
    $postType = sanitize_text_field($_POST['post_type']);
    $parentId = intval($_POST['parent_id']);
    
    $post = array(
        'post_title'    => $title,
        'post_type'     => $postType,
        'post_parent'   => $parentId,
        'post_status'   => 'publish'
    );
    $post_id = wp_insert_post($post);
    if ($post_id) {
        wp_send_json_success($post_id);
    } else {
        wp_send_json_error();
    }
    wp_die();
}
add_action( 'wp_ajax_bppc_create_page', 'bppc_create_page' );

function bppc_get_pages() {
    $postType = $_POST['post_type'];
    
    $pages = get_posts(array(
        'post_type' => $postType,
        'post_status' => 'publish',
        'orderby' => 'menu_order title',
        'order' => 'ASC',
        'hierarchical' => 1,
        'exclude' => ''
    ));

    $output = '<option value="0">(no parent)</option>';
    foreach ($pages as $page) {
        $output .= '<option value="' . $page->ID . '">' . $page->post_title . '</option>';
    }
    echo $output;
    wp_die();
}
add_action( 'wp_ajax_bppc_get_pages', 'bppc_get_pages' );

function bppc_admin_page_display() {
    ?>
<div class="container-fluid">
  <div class="d-flex justify-content-center align-items-center p-5">
    <div class="card w-50">
      <div class="card-header text-center">
        <h5 class="card-title">Bulk Page & Posts Creator</h5>
      </div>
      <div class="card-body">
        <form id="bppc-form">
          <div class="form-group mb-3 w-100">
            <label for="page-input">Page Input:</label>
            <input type="text" class="form-control w-100 " id="page-input" name="page-input">
          </div>
          <div class="form-group mb-3 w-100">
            <label for="post-type">Post Type:</label>
            <select id="post-type" name="post-type" class="form-control w-100 mb-3">
                <?php 
                $post_types = get_post_types( array( 'public' => true ), 'names' );
                $post_types = array_diff( $post_types, array( 'attachment' ) );

                foreach ( $post_types as $post_type ) {
                    echo '<option value="' . $post_type . '">' . ucfirst( $post_type ) . '</option>';
                }
                ?>
            </select>
          </div>
          <div class="form-group mb-3 w-100">
            <label for="parent">Parent:</label>
            <select id="parent" name="parent" class="form-control w-100 mb-3">
            </select>
          </div>
          <input type="submit" class="btn btn-primary btn-block" value="Create Pages Now">
        </form>
        <div id="bppc-loader" class="my-3"></div>
        <div id="bppc-message" class="my-3"></div>
      </div>
    </div>
  </div>
</div>
    <?php
}
