<?php
/**
 * Plugin Name: Cazalye REST API
 * Plugin URI: http://wordpress.cazalye.com
 * Description: Add functionalities to core WP API
 * Version: 1.0
 * Author: Emma Cazaly
 * Author URI: http://cazalye.com
 */
 
 function related_posts_endpoint( $request_data ) {

    $post_id = $request_data['post_id'];

    $posts = get_posts(
        array(
            'post_type' => 'post',
            'category__in'   => wp_get_post_categories($post_id),
            'posts_per_page' => 5,
            'post__not_in'   => array($post_id),
        )
    );
    
    foreach( $posts as $post)
    {
        if( has_post_thumbnail( $post->ID) ) {
            $post->feature_image_url = get_the_post_thumbnail_url($post->ID);
            $post->featureImageSizes = array(
                'thumbnail' => get_the_post_thumbnail_url($post->ID, 'thumbnail'),
                'medium' => get_the_post_thumbnail_url($post->ID, 'medium'),
                'medium_large' => get_the_post_thumbnail_url($post->ID, 'medium_large'),
                'large' => get_the_post_thumbnail_url($post->ID, 'large'),
                'full' => get_the_post_thumbnail_url($post->ID, 'full')
            );
        }
    }

    return  $posts;
}

function get_post_cat_objects( $post ) {
    $catNames = array();
    foreach ($post->categories as $catID) {
        array_push($catNames, get_cat_name($catID));
    }
    return get_the_category( $post->id );
}

add_action( 'rest_api_init', function () {

    register_rest_route( 'cazalye', '/post/related/(?P<post_id>[\d]+)', array(
            'methods' => 'GET',
            'callback' => 'related_posts_endpoint'
    ));
    
    register_rest_field( 'post', 'categoriesObjects',
            array(
                'get_callback'          => 'get_post_cat_objects' ,
                'show_in_rest'          => true,
                'schema'            => null
            )
        );

    register_rest_field( 'post', 'medias',
            array(
                'get_callback'          => 'get_post_media' ,
                'show_in_rest'          => true,
                'schema'            => null
            )
        );
    register_rest_field( 'post', 'feature_image',
            array(
                'get_callback'          => 'get_post_feature_image' ,
                'show_in_rest'          => true,
                'schema'            => null
            )
        );
    
    function get_post_media( $post ) {
        // return wp_get_attachment_metadata(10495);
        $medias = get_attached_media( 'image', $post->id );
        $feature_image_id = get_post_thumbnail_id($post->id);
        $ret = array();
        foreach ( $medias as $media) {
            if ($media->ID != $feature_image_id) {
                array_push($ret, wp_get_attachment_metadata($media->ID));
            }
        }
        
        
        return $ret;
    }
    function get_post_feature_image($post) {
        $feature_image_id = get_post_thumbnail_id($post->id);
        return wp_get_attachment_metadata($feature_image_id);
    }
});



 
 ?>
