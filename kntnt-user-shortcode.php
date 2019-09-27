<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Kntnt User Shortcode
 * Description:       Provides the shortcode [user field='…' where_XXX='…'], where XXX is slug, email, login or id, that displays the value of the provided field — which can be built-in (e.g. display_name, email and description) or an ACF-field — of the user matching the provided criteria.
 * Version:           1.0.2
 * Author:            Thomas Barregren
 * Author URI:        https://www.kntnt.com/
 * GitHub Plugin URI: https://github.com/Kntnt/kntnt-user-shortcode
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Kntnt\User_Shortcode;

defined('WPINC') && new Plugin;

class Plugin {

    // Allowed arguments
    private static $defaults = [
        'field'       => 'display_name',
        'where_slug'  => '',
        'where_email' => '',
        'where_login' => '',
        'where_id'    => '',
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

        $output = '';

        $atts = $this->shortcode_atts(self::$defaults, $atts);

        if ($user_id = $this->get_user_id($atts)) {

            if ('posts_url' == $atts['field']) {
                $output = get_author_posts_url($user_id);
            }
            elseif ('posts_count' == $atts['field']) {
                $output = count_user_posts($user_id, 'post', true);
            }
            elseif (in_array($atts['field'], self::$fields)) {
                $output = get_the_author_meta($atts['field'], $user_id);
            }
            elseif(is_callable('get_field')) {
                $output = get_field($atts['field'], "user_{$user_id}");
            }

        }

        return $output;

    }

    private function get_user_id($atts) {
        if ($atts['where_id']) {
            $user = get_user_by('id', $atts['where_id']);
        }
        elseif ($atts['where_slug']) {
            $user = get_user_by('slug', $atts['where_slug']);
        }
        elseif ($atts['where_email']) {
            $user = get_user_by('email', $atts['where_email']);
        }
        elseif ($atts['where_login']) {
            $user = get_user_by('login', $atts['where_login']);
        }
        else {
            $user = get_user_by('id', get_the_author_meta('ID'));
        }
        return $user ? $user->ID : false;
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
