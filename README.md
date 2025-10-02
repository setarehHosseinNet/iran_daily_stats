# Iran Daily Stats (Isolated) — گزارش بازدید ایزوله

**افزونه‌ی وردپرس برای ثبت و گزارش بازدیدها** با این ویژگی‌ها:
- ثبت همهٔ بازدیدها در دیتابیس اختصاصی (با IP واقعی، User-Agent، Referrer، URL، و مکان جغرافیایی).
- پنل گزارش‌گیری ایزوله داخل `iframe` با `srcdoc` (بدون آلودگی استایل/اسکریپت قالب).
- دو اندپوینت REST عمومی برای **لیست بازدیدها** و **آمار روز/امروز/کل**.
- نمایش آمار در سه کارت (کل/امروز/روز انتخابی) + جدول روز انتخابی (IP یکتا/همه).
- معماری ماژولار با کلاس‌های جدا (قابل ایمپورت در پروژه‌های دیگر).

---

## فهرست مطالب
- [نصب](#نصب)
- [حداقل پیش‌نیازها](#حداقل-پیشنیازها)
- [ساختار پوشه‌ها](#ساختار-پوشهها)
- [شورتکدها](#شورتکدها)
- [اندپوینت‌های REST](#اندپوینتهای-rest)
- [ساختار دیتابیس](#ساختار-دیتابیس)
- [نحوهٔ ثبت بازدید](#نحوهٔ-ثبت-بازدید)
- [پیکربندی Geo-IP](#پیکربندی-geoip)
- [رفع اشکال](#رفع-اشکال)
- [توسعه‌دهی](#توسعهدهی)
- [امنیت و حریم خصوصی](#امنیت-و-حریم-خصوصی)
- [مجوز](#مجوز)
- [تغییرات](#تغییرات)

---

## نصب

1) مخزن را در پوشه‌ی افزونه‌ها کپی/کلون کنید:
wp-content/plugins/iran-daily-stats/

2) در پیشخوان وردپرس افزونه را **فعال** کنید.  
3) در برگه/برگه‌ای که می‌خواهید گزارش نمایش داده شود، یکی از شورتکدهای زیر را قرار دهید:

```text
[iran_daily_stats]
حداقل پیش‌نیازها

WordPress 5.8+

PHP 7.4+

دسترسی HTTP خروجی برای سرویس Geo-IP (ip-api.com) — اختیاری ولی توصیه‌شده

(اختیاری) استفاده از Cloudflare برای بهبود تشخیص کشور (هدر CF-IPCountry)

ساختار پوشه‌ها
iran-daily-stats/
├─ iran-daily-stats.php           # فایل بوت افزونه (Bootstrap)
├─ src/
│  ├─ Autoloader.php              # اتولودر سبک (PSR-4 ساده)
│  ├─ Installer.php               # نصب/به‌روزرسانی DB
│  ├─ Geo/
│  │  └─ Resolver.php             # سرویس Geo-IP
│  ├─ Logger.php                  # ثبت بازدید
│  ├─ Rest/
│  │  ├─ VisitsController.php     # REST: GET /ids/v1/visits
│  │  └─ StatsController.php      # REST: GET /ids/v1/stats
│  └─ Shortcode/
│     └─ ReportIframe.php         # شورتکد و UI ایزوله (iframe srcdoc)

شورتکدها

سه نام معادل ثبت شده‌اند و همگی یک خروجی دارند:
[iran_daily_stats]
[iran_daily_stats_Table]
[iran_daily_stats_table]
رابط کاربری داخل iframe ساخته می‌شود تا با CSS/JS قالب یا سایر افزونه‌ها تداخل نکند.

کارت‌ها: (۱) مجموع کل، (۲) امروز، (۳) روز انتخابی

جدول روز انتخابی: حالت پیش‌فرض IP یکتا (آخرین بازدید هر IP). دکمه‌ی «نمایش همهٔ بازدیدها» هم دارد.

اندپوینت‌های REST

پیشوند: ids/v1

1) لیست بازدیدها
GET /wp-json/ids/v1/visits
پارامترها:

date : YYYY-MM-DD (تایم‌زون تهران)

from, to : بازه‌ی تاریخی (هر دو YYYY-MM-DD)

unique: ip (پیش‌فرض) یا none

ip: فقط آخرین بازدید هر IP در قیود انتخاب‌شده

none: همهٔ رکوردها

page: پیش‌فرض 1

per_page: پیش‌فرض 100 (حداکثر 500)

خروجی نمونه:
{
  "page": 1,
  "per_page": 100,
  "total": 37,
  "unique": "ip",
  "rows": [
    {
      "id": 123,
      "visit_dt": "2025-10-01 11:40:00",
      "user_id": 0,
      "user_login": null,
      "user_display": null,
      "ip": "1.2.3.4",
      "geo_city": "Tehran",
      "geo_region": "Tehran",
      "geo_country": "Iran",
      "referrer": "https://example.com/",
      "url": "https://yoursite.com/page",
      "ua": "Mozilla/5.0 ..."
    }
  ]
}
2) آمار
GET /wp-json/ids/v1/stats?day=2025-10-01
خروجی نمونه:
{
  "all":   {"visits": 436, "unique": 150, "registrations": 10},
  "today": {"date": "2025-10-01", "visits": 137, "unique": 55, "registrations": 0},
  "day":   {"date": "2025-10-01", "visits": 137, "unique": 55, "registrations": 0}
}
ضدکش: روی پاسخ‌های REST هدرهای no-cache ست می‌شود.
ساختار دیتابیس

نام جدول (با پیشوند وردپرس): {prefix}_ids_visit_logs

ستون	نوع	توضیح
id	BIGINT UNSIGNED (PK)	شناسه
visit_dt	DATETIME	زمان بازدید (تایم‌زون تهران)
user_id	BIGINT UNSIGNED	شناسه کاربر (در صورت ورود)
user_login	VARCHAR(191)	نام کاربری
user_display	VARCHAR(191)	نام نمایشی
ip	VARCHAR(100)	آی‌پی
geo_city	VARCHAR(100)	شهر
geo_region	VARCHAR(100)	استان/منطقه
geo_country	VARCHAR(100)	کشور
ua	TEXT	User-Agent
referrer	TEXT	مرجع
url	TEXT	آدرس صفحه
/wp-json/ids/v1/stats
/wp-json/ids/v1/visits?date=YYYY-MM-DD
اگر 403/401 گرفتید، بررسی کنید: افزونه‌های امنیتی، فایروال سرور، یا تغییر مسیرهای خاص.

تاریخ شمسی/میلادی اشتباه
زمان‌ها با Asia/Tehran ذخیره می‌شوند. در UI برای تبدیل، از Intl استفاده شده (UTC-safe). مرورگر قدیمی ممکن است Intl ناقص داشته باشد.

Geo پر نمی‌شود
خروجی ip-api مسدود/Rate-Limit شده یا سرور بدون دسترسی اینترنت است. سربرگ Cloudflare برای Country کمک می‌کند.

برخورد CSS/JS
خروجی داخل iframe با srcdoc ایزوله است؛ تداخل بیرونی نباید رخ دهد.

توسعه‌دهی

معماری ماژولار و قابل استفاده‌ی مجدد:

ثبت بازدید: IDS\Logger

Geo-IP: IDS\Geo\Resolver

نصب DB: IDS\Installer

REST: IDS\Rest\VisitsController, IDS\Rest\StatsController

UI: IDS\Shortcode\ReportIframe

اتولودر سبک (بدون نیاز به Composer). اگر مایلید، می‌توانید همین فضای‌نام را با PSR-4 واقعی به Composer متصل کنید.

تست سریع اندپوینت‌ها:
curl -s https://yoursite.com/wp-json/ids/v1/stats
curl -s "https://yoursite.com/wp-json/ids/v1/visits?date=2025-10-01&unique=ip"
امنیت و حریم خصوصی

لاگ‌ها شامل IP و UA هستند؛ قبل از فعال‌سازی، سیاست حریم خصوصی سایت را به‌روز کنید.

اندپوینت‌ها عمومی‌اند؛ اگر در محیطی محدود می‌خواهید، روی permission_callback سیاست دسترسی بگذارید.

روی پاسخ‌های REST هدرهای no-cache اعمال می‌شود.

مجوز

MIT (یا مجوز دلخواه شما). لطفاً مجوز انتخابی خود را در فایل LICENSE درج کنید.

تغییرات
v4.1.2

بازطراحی کامل معماری به کلاس‌های مستقل (قابل ایمپورت).

بهبود پایدارسازی تاریخ (تهران) و تبدیل شمسی در UI.

ضدکش روی پاسخ‌های REST و فراخوانی‌های fetch در UI.

کدنویسی تمیز، کامنت‌های جامع و ساختار پوشه‌ی تفکیک‌شده.

