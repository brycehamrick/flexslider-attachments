<?php
/*
 * Plugin Name: FlexSlider Attachments
 * Plugin URI: https://bhamrick.com/
 * Description: Creates a FlexSlider out of WordPress Media items based on tags
 * Author: Bryce Hamrick
 * Version: 0.0.1
 * Author URI: https://bhamrick.com/
 * License: GPL2
 * Text Domain: flexslider-attachments
 *
 * @package FlexSlider_Attachments
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'FlexSlider_Attachments' ) ) :
class FlexSlider_Attachments {
  public function __construct() {
    $this->id = 'flexslider-attachments';

    require 'plugin-update-checker/plugin-update-checker.php';
    $myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
      'https://github.com/brycehamrick/flexslider-attachments/',
      __FILE__,
      $this->id
    );

    add_action( 'init' , array( $this, 'add_taxonomy_to_media' ) );
    add_shortcode('flexslider_attachments', array( $this, 'shortcode' ) );
  }

  public function add_taxonomy_to_media() {
    register_taxonomy_for_object_type( 'post_tag', 'attachment' );
  }

  public function shortcode($atts) {
    $a = shortcode_atts( array(
      'tag' => 'flexslider',
      'animation' => 'slide',
      'animationLoop' => 'false',
      'itemWidth' => '200',
      'itemMargin' => '10',
      'controlNav' => 'true',
      'directionNav' => 'true',
      'slideshowSpeed' => '7000',
      'animationSpeed' => '500'
    ), $atts );

    // Convert bools
    $a['animationLoop'] = $this->boolean($a['animationLoop']);
    $a['controlNav'] = $this->boolean($a['controlNav']);
    $a['directionNav'] = $this->boolean($a['directionNav']);

    // Convert ints
    $a['itemWidth'] = intval($a['itemWidth']);
    $a['itemMargin'] = intval($a['itemMargin']);
    $a['slideshowSpeed'] = intval($a['slideshowSpeed']);
    $a['animationSpeed'] = intval($a['animationSpeed']);

    $tag = $a['tag'];
    unset($a['tag']);

    $args = array(
      'post_type' => 'attachment',
      'tag' => $tag,
      'post_status' => 'inherit'
    );
    $the_query = new WP_Query($args);
    $output = "";
    if ( $the_query->have_posts() ) {
      $output .= '<div class="' . $this->id . ' flexslider carousel"><ul class="slides">';
      while ( $the_query->have_posts() ) {
        $the_query->the_post();
        $output .= '<li>' . wp_get_attachment_image( get_the_ID(), "medium", false, ["class" => "no-lazy"] ) . '</li>';
      }
      $output .= '</ul></div><script>jQuery(window).load(function() { jQuery(".' . $this->id . '").flexslider(' . json_encode($a) . ');});</script>';
    }
    wp_reset_postdata();

    return $output;

  }

  private function boolean($str) {
    return (!$str || $str == 'false') ? false : true;
  }
}
endif;
