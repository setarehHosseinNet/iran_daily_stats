<?php
/**
 * Plugin Name: Iran Daily Stats
 * Description: نمایش آمار بازدید و ثبت‌نام روزانه با تقویم شمسی در iframe ایزوله.
 * Version: 1.0.0
 * Author: Hossein Setareh
 */

if (!defined('ABSPATH')) exit;

/* =========== شورت‌کد [iran_daily_stats] =========== */
add_shortcode('iran_daily_stats', function () {
    $iframe_url = plugin_dir_url(__FILE__) . 'templates/iframe-app.php';
    return '<iframe src="'.$iframe_url.'" style="width:100%;min-height:400px;border:none;"></iframe>';
});
