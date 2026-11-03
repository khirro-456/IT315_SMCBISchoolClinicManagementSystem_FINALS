SMCBI SCHOOL CLINIC MANAGEMENT SYSTEM
=====================================

FOLDER STRUCTURE
school-clinic/
  index.php              front controller (every request enters here)
  config/
    config.php           database settings (XAMPP defaults)  <- edit for your server
    config.infinityfree.php   template for InfinityFree
  includes/              core logic: bootstrap, helpers, auth, validation, inventory rules, schema, themes
  actions/               form handlers (POST): auth, users, patients, inventory, visits, notifications, settings
  pages/                 screens: login, dashboard, patients, inventory, visits, reports, users, appearance
  partials/
    header.php footer.php
    modals/              every form/viewer is a modal:
                         patient_form, patient_view, inventory_form, supply_transaction,
                         visit_form, visit_view, user_form (+ status confirm), password_form
  assets/
    css/style.css (Classic theme)  css/themes.css (other themes)  js/app.js  js/dashboard.js  img/clinic-login.jpg
  database/schema.sql    reference copy of the tables (created automatically)
  database/clinicdb.sql  full SQL export: all tables + admin account + 40 sample medicines/supplies

LIVE SYSTEM
  https://cliniccals.infinityfreeapp.com
  My Clinic Record: https://cliniccals.infinityfreeapp.com/?page=student

MODALS PER SIDEBAR ITEM
  Dashboard ........ Record Visit, Add Patient, Add Item (quick actions)
  Patients ......... Add/Edit Patient, View Profile + History, Record Visit, Visit Details
  Inventory ........ Add/Edit Medicine/Supply, Supply Transaction (delivery/damage/expired/lost/correction)
  Clinic Visits .... Record Visit, View Full Record
  Reports .......... Visits, Complaints, Duty Staff, Inventory Status, Medicine Usage (each printable)
  User Management .. Add User (school email), Change Login Email, Activate/Deactivate, Change Password
  Appearance ....... (admins) pick the system theme: Classic, Glassy, Minimalist, Soft UI,
                     Emerald, Ocean, Sunset, Royal, Blossom. Click to preview, Save applies it
                     for every user. Light / dark mode stays a per-user choice (moon button).

MY CLINIC RECORD (students and personnel)
  Address .......... http://localhost/school-clinic/?page=student  (also linked on the staff sign-in page)
  Sign-in .......... School / Employee ID + the patient's 6-digit access code. No staff account needed.
  Access code ...... Created automatically for every patient. Staff see it under Patients -> View
                     profile, and can press "Reset code" when it is forgotten or someone else saw it
                     (the old code stops working at once).
  What it shows .... The patient's own profile, allergies, conditions and visit history (read-only).
                     Staff-only visit notes are not shown.
  Security ......... 5 wrong codes lock that ID for 15 minutes (students never lock out staff sign-in);
                     the record closes after 10 minutes; every sign-in is in the activity log
                     (access codes are never written to the log).

RUN ON XAMPP
1. Extract the school-clinic folder to  C:\xampp\htdocs\school-clinic
2. XAMPP Control Panel -> Start Apache and MySQL
3. http://localhost/phpmyadmin -> Databases -> create a database named  clinicdb
   Then either import database/clinicdb.sql (Import tab) to get the sample inventory,
   or leave it empty: the tables are created automatically on first load.
4. Open http://localhost/school-clinic/
5. Login: clinic@smcbi.edu.ph / Admin@12345  (you must set a new password right after the first sign-in)
   Every account signs in with a school email ending in @smcbi.edu.ph. An older system that
   still had the "admin" username is renamed to clinic@smcbi.edu.ph automatically (same password).
6. Empty database only: Inventory & Supplies -> "Load 30 Clinic Medicines + Supplies"

UPLOAD TO INFINITYFREE
1. Upload everything inside school-clinic into htdocs (keep the folders).
2. Edit config/config.infinityfree.php (host, database, user, password) and rename it to config.php
   (replace the XAMPP config.php).
3. Never share config.php.

LOGIN BANNER
Put your own picture in assets/img/ (any .jpg/.png/.webp; clinic-login.jpg is preferred),
or paste a web image link in config/config.php -> 'login_image_url'.
It is shown in full across the top of the sign-in page, so a wide, sharp banner
(about 2000 x 750 pixels, like the SMCBI College Department banner) looks best.

NOTES
- Tables are created automatically on first load (and upgraded once after an update).
- Security: 5 wrong passwords lock that account for 15 minutes; sessions end after
  30 minutes of inactivity; new accounts must set their own password at first sign-in.
- Medicine list is demo data; the school nurse/physician must approve what is stocked and issued.
- includes/, config/, actions/, pages/, partials/ are blocked from direct web access by .htaccess.

SECURITY FEATURES
  - Passwords hashed (bcrypt); at least 8 characters with letters and numbers, not containing the email name
  - Default admin password must be changed at first sign-in
  - Sign-in lockout after repeated failures (per user and per IP)
  - Sign out after 30 minutes idle, and after 10 hours in any case
  - Session cookie is HttpOnly/SameSite, Secure on HTTPS, and tied to the browser that signed in
  - CSRF token on every form; prepared SQL statements everywhere; all output escaped
  - Security headers (CSP, no framing, no-sniff) and no caching of patient pages
  - .htaccess blocks folder listings, this README, SQL files, hidden folders and direct access to PHP files
  - Activity log (User Management page, admins only): sign-ins, sign-outs, failed attempts, every saved change
  - PHP errors are shown only on localhost, never to online visitors
  - After SSL is active on InfinityFree, enable the HTTPS redirect at the bottom of .htaccess
