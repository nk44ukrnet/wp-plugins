<?php
/*
 * Plugin Name: Our Test Plugin
 * Description: A truly amazing plugin
 * Version: 1.0
 * Author: Viktor
 * Author URI: https://google.com/
 * Text Domain: wcpdomain
 * Domain Path: /languages
 */

class WordCountAndTimePlugin
{
    function __construct()
    {
        add_action('admin_menu', array($this, 'adminPage'));
        add_action('admin_init', array($this, 'settings'));
        add_filter('the_content', array($this, 'ifWrap'));
        add_action('init', array($this, 'languages'));
    }

    function languages(){
        load_plugin_textdomain('wcpdomain',false,dirname(plugin_basename(__FILE__)) . '/languages');
    }

    function ifWrap($content)
    {
        if (
            (is_main_query() && is_single())
            &&
            (
                get_option('wcp_wordcount', '1')
                || get_option('wcp_charcount', '1')
                || get_option('wcp_readtime', '1')
            )
        ) {
            return $this->createHTML($content);
        }
        return $content;
    }

    function createHTML($content)
    {
        $html = '<h3>'. esc_html(get_option('wcp_headline', 'Post Statistics')) .'</h3><p>';


        if(get_option('wcp_wordcount', '1')
        || get_option('wcp_readtime', '1')
        ) {
            $wordCount = str_word_count(strip_tags($content));
        }

        if(get_option('wcp_wordcount', '1')) {
            $html .= esc_html__('This post has', 'wcpdomain') . ' ' . $wordCount . ' ' . __('words', 'wcpdomain') . '. <br>';
        }

        if(get_option('wcp_charcount', '1')) {
            $html .= 'This post has ' . strlen(strip_tags($wordCount)) . ' characters. <br>';
        }

        if(get_option('wcp_readtime', '1')) {
            $mins = round($wordCount/225);
            if($mins == '0') {
                $mins = 1;
            }
            $html .= 'This post will take about ' . $mins . ' minute(s) to read. <br>';
        }

        $html .= '</p>';

        if(get_option('wcp_location', '0') == '0') {
            return $html . $content;
        }

        return $content . $html;
    }

    function settings()
    {
        add_settings_section('wcp_first_section', null, null, 'word-count-settings-page');

        //location
        add_settings_field('wcp_location', 'Display Location', array($this, 'locationHTML'), 'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin', 'wcp_location', array('sanitize_callback' => array($this, 'sanitizeLocation'), 'default' => '0'));

        //headline
        add_settings_field('wcp_headline', 'Headline Text', array($this, 'headlineHTML'), 'word-count-settings-page', 'wcp_first_section');
        register_setting(
            'wordcountplugin',
            'wcp_headline',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'Post Statistics'
            )
        );

        //checkbox1 word count
        add_settings_field('wcp_wordcount', 'Word Count', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_wordcount'));
        register_setting(
            'wordcountplugin',
            'wcp_wordcount',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '1'
            )
        );

        //checkbox2 Character count
        add_settings_field('wcp_charcount', 'Character Count', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_charcount'));
        register_setting(
            'wordcountplugin',
            'wcp_charcount',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '1'
            )
        );

        //checkbox3 Read time
        add_settings_field('wcp_readtime', 'Read time', array($this, 'checkboxHTML'), 'word-count-settings-page', 'wcp_first_section', array('theName' => 'wcp_readtime'));
        register_setting(
            'wordcountplugin',
            'wcp_readtime',
            array(
                'sanitize_callback' => 'sanitize_text_field',
                'default' => '1'
            )
        );
    }

    function sanitizeLocation($input) {
        if ($input != '0' AND $input != '1') {
            add_settings_error('wcp_location', 'wcp_location_error', 'Display location must be either beginning or end.');
            return get_option('wcp_location');
        }
        return $input;
    }

    function checkboxHTML($args)
    {
        ?>
        <input type="checkbox" name="<?php echo esc_attr($args['theName']) ?>"
               value="1" <?php checked(get_option($args['theName']), '1') ?>>
        <?php
    }

    function headlineHTML() { ?>
        <input type="text" name="wcp_headline" value="<?php echo esc_attr(get_option('wcp_headline')) ?>">
    <?php }

    function locationHTML() { ?>
        <select name="wcp_location">
            <option value="0" <?php selected(get_option('wcp_location'), '0') ?>>Beginning of post</option>
            <option value="1" <?php selected(get_option('wcp_location'), '1') ?>>End of post</option>
        </select>
    <?php }

    function adminPage()
    {
        add_options_page('Word Count Settings', __('Word Count', 'wcpdomain'), 'manage_options', 'word-count-settings-page', array($this, 'ourHTML'));
    }

    function ourHTML()
    {
        ?>
        <div class="wrap">
            <h1>Word Count Settings</h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('wordcountplugin');
                do_settings_sections('word-count-settings-page');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

$wordCountAndTimePlugin = new WordCountAndTimePlugin();




