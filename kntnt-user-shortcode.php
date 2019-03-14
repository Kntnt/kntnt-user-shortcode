<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Kntnt User Shortcode
 * Description:       Provides the shortcode [user field='…' user_id='…'] to display user information.
 * Version:           1.0.0
 * Author:            Thomas Barregren
 * Author URI:        https://www.kntnt.com/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Kntnt\User_Shortcode;

defined('WPINC') && new Plugin;

class Plugin {

    // Allowed arguments
    private static $defaults = [
        'field'   => 'display_name',
        'user_id' => false,
    ];

    // Allowed fields
    private static $fields = [
        'ID',
        'first_name',
        'last_name',
        'display_name',
        'nicename',
        'nickname',
        'url',
        'email',
        'description',
        'posts_url',
        'posts_count',
    ];

    public function __construct() {
        add_shortcode('user', [$this, 'user_shortcode']);
    }

    public function user_shortcode($atts) {

        $atts = $this->shortcode_atts(self::$defaults, $atts);

        if ('posts_url' == $atts['field']) {
            $user_id = $atts['user_id'] ? $atts['user_id'] : get_the_author_meta('ID');
            $output = get_author_posts_url($user_id);
        }
        elseif ('posts_count' == $atts['field']) {
            $user_id = $atts['user_id'] ? $atts['user_id'] : get_the_author_meta('ID');
            $output = count_user_posts($user_id, 'post', true);
        }
        elseif (in_array($atts['field'], self::$fields)) {
            $output = get_the_author_meta($atts['field'], $atts['user_id']);
        }
        else {
            $output = '';
        }

        return $output;

    }

    // A more forgiving version of WP's shortcode_atts().
    private function shortcode_atts($pairs, $atts, $shortcode = '') {

        $atts = (array)$atts;
        $out = [];
        $pos = 0;
        while ($name = key($pairs)) {
            $default = array_shift($pairs);
            if (array_key_exists($name, $atts)) {
                $out[$name] = $atts[$name];
            }
            elseif (array_key_exists($pos, $atts)) {
                $out[$name] = $atts[$pos];
                ++$pos;
            }
            else {
                $out[$name] = $default;
            }
        }

        if ($shortcode) {
            $out = apply_filters("shortcode_atts_{$shortcode}", $out, $pairs, $atts, $shortcode);
        }

        return $out;

    }

}
