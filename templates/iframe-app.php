<?php
// ایزوله کردن محیط
header("X-Frame-Options: SAMEORIGIN");
header("Content-Type: text/html; charset=utf-8");
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

// 📅 گرفتن روز انتخابی (پیشفرض: امروز)
$day = isset($_GET['day']) ? sanitize_text_field($_GET['day']) : date("Y-m-d");

// تبدیل تاریخ جلالی به میلادی اگر داده شد
if (function_exists('jdate_to_gregorian')) {
    // این تابع باید از کتابخانه جلالی استفاده کند (مثلا jdf.php)
    $day_greg = jdate_to_gregorian($day);
} else {
    $day_greg = $day;
}

// گرفتن آمار
global $wpdb;

// تعداد ثبت‌نام‌های روز
$reg_count = $wpdb->get_var($wpdb->prepare("
    SELECT COUNT(ID) FROM {$wpdb->users}
    WHERE DATE(user_registered) = %s
", $day_greg));

// تعداد بازدیدهای روز (نیازمند پلاگین آمار مثل WP-Statistics یا جدول اختصاصی)
$visit_count = get_option("iran_daily_visits_$day_greg", 0);

?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="../assets/iframe-style.css">
<script src="../assets/iframe-app.js"></script>
</head>
<body>
  <h2>آمار روزانه (<?php echo esc_html($day); ?>)</h2>

  <label>انتخاب روز:</label>
  <input type="date" id="dayPicker" value="<?php echo esc_attr($day); ?>">

  <div class="stats-box">
    <p>👥 تعداد بازدیدها: <strong><?php echo intval($visit_count); ?></strong></p>
    <p>📝 تعداد ثبت‌نام: <strong><?php echo intval($reg_count); ?></strong></p>
  </div>
</body>
</html>
