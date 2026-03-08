# Godyar News Platform v3.0.0-newsroom-core

نظام إدارة محتوى (CMS) مبني بـ PHP مع لوحة تحكم وإدارة أخبار/تصنيفات/وسائط.

## المتطلبات
- PHP 8.1+ (يعمل على 7.4+ في بعض البيئات، لكن الموصى به 8.1+)
- MySQL/MariaDB 10.3+ (أو MySQL 5.7+)
- Extensions: PDO MySQL, mbstring, json, curl, gd, zip
- تفعيل `mod_rewrite` (Apache) أو إعدادات rewriting في Nginx

## التثبيت السريع
1. ارفع الملفات إلى مجلد الموقع.
2. أنشئ قاعدة بيانات جديدة (يفضل اسمًا مثل `example_myar` أو أي اسم تختاره).
3. افتح في المتصفح: `https://your-domain.com/install.php`
4. أكمل المعالج (إعداد قاعدة البيانات + إنشاء حساب المدير).
5. بعد النجاح:
   - سيتم إنشاء ملف `.env`
   - سيتم إنشاء ملف `install.lock`
   - احذف `install.php` ومجلد `install/` لأسباب أمنية (أو اتركهما مع وجود `install.lock`).

## التطوير
- يفضل استخدام Composer: `composer install`
- اجعل `APP_ENV=local` و `APP_DEBUG=true` داخل `.env` في بيئة التطوير.

## الأمان
- لا ترفع ملف `.env` إلى GitHub (تم إضافته في `.gitignore`).
- لا تترك `install.php` متاحًا بعد التثبيت.
- استخدم كلمة مرور قوية لحساب المدير.

### الإبلاغ عن الثغرات
يرجى قراءة `SECURITY.md`.

## التواصل
- الموقع: `example.com`
- البريد: `abdalgaderkh@gmail.com`
- الهاتف/واتساب: `+249964056666` / `+966554507127`

## الرخصة
MIT
