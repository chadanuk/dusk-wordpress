<?php
namespace Chadanuk\DuskWordpressTests\Traits;

/**
 * Wordpress post creation trit
 */
trait WordpressPost
{
    public function createPost(String $name = '', Array $extraData = [], String $type = 'post')
    {
        $now = current_time( 'mysql' );
	    $now_gmt = current_time( 'mysql', 1 );
        $first_post_guid = get_option( $name ) . '/?p=1';

        $data = array_merge([
            'post_author' => 1,
            'post_date' => $now,
            'post_date_gmt' => $now_gmt,
            'post_content' => '<p>content</p>',
            'post_excerpt' => '',
            'post_type' => $type,
            'post_title' => __('Home'),
            /* translators: Default post slug */
            'post_name' => sanitize_title( _x($name, 'Default post slug') ),
            'post_modified' => $now,
            'post_modified_gmt' => $now_gmt,
            'guid' => $first_post_guid,
            'comment_count' => 1,
            'to_ping' => '',
            'pinged' => '',
            'post_content_filtered' => ''
        ], $extraData);

        $this->wpdb->insert($this->wpdb->posts, $data);
    }
}
