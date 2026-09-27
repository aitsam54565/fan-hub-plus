# Fan Hub Plus

An update of the supplied PHP/MySQL project. The original folder names, database entities, logo, artwork, homepage palette, carousel and card styling are retained. Static demo screens and incomplete management pages are connected to the database through shared PHP includes.

## Start in XAMPP

1. Extract the ZIP. Copy the `fan-hub-plus` folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin/`.
4. For a **new installation**, import `database/fanhubplus.sql`. This creates the database, schema, two test accounts and a getting-started article. It does not include fabricated visitor counts or unlicensed commercial media.
5. Copy `config/local.example.php` to `config/local.php`. The defaults match a standard local XAMPP installation. Change the database password if yours is different.
6. Open `http://localhost/fan-hub-plus/`.
7. Sign in as the administrator and use **Add / manage content**. Choose a content type, complete its fields and publish it. Its public page updates automatically.

Do not open PHP files by double-clicking them. Apache must serve them through localhost.

## Updating an existing database

Export a backup of your current database and keep a copy of your project and uploads first.

1. Keep your existing database and upload files.
2. In phpMyAdmin, select that database and import **only** `database/002_upgrade.sql`.
3. Replace application files with this updated folder. Keep your own `config/local.php` values and merge your existing upload folders.
4. Open the site and sign in using your existing account.

The upgrade adds the missing carousel and release structures and extends the existing tables. It preserves existing records and passwords. A successfully applied upgrade is skipped if run again. If an import stops with an error, restore your backup before retrying: MySQL schema changes are not transactional. Do not import the fresh-install SQL over an existing database.

The supplied ZIP did not contain a live database export with your later edits. Existing records can be preserved through the migration; they cannot be recovered from image files alone. If you previously imported the original demo SQL, review or delete its demo catalog entries and inflated view counts before presenting real analytics.

## Local test credentials

| Role | Email | Password | Sign-in page |
| --- | --- | --- | --- |
| Administrator | admin@fanhubplus.local | Admin@123 | /fan-hub-plus/Admin/login.php |
| Registered member | user@fanhubplus.local | User@123 | /fan-hub-plus/User/login.php |

These credentials are for a fresh import. The migration does not change existing credentials. Change test passwords before exposing the application publicly. Public registration always creates an ordinary user. Only the admin role can use the Admin panel; legacy editor accounts do not inherit administrator privileges.

## Runtime and permissions

- PHP 8.1 or later; verified here on PHP 8.3.6.
- MySQL 8.0.16+ or MariaDB 10.6+; verified here on MariaDB 10.11.14.
- PHP extensions: mysqli/mysqlnd, mbstring, DOM/XML, fileinfo and ctype.
- PHP sessions enabled with a writable session save directory.
- Apache must be able to write to `assets/uploads/` and its subfolders. Do not use world-writable permissions unless your local setup specifically requires them.
- Set `upload_max_filesize` to at least 5M and `post_max_size` to at least 12M. Restart Apache after changing php.ini.
- Keep the included `.htaccess` files. They block direct access to configuration/includes/SQL and execution of scripts in uploads. On other servers configure equivalent rules.
- Run without npm, Composer or a frontend build step. CSS and JavaScript are local files.

The application automatically detects a subfolder such as `/fan-hub-plus`. Database settings can also be supplied through `FANHUB_DB_HOST`, `FANHUB_DB_PORT`, `FANHUB_DB_NAME`, `FANHUB_DB_USER` and `FANHUB_DB_PASS` environment variables. Local configuration takes precedence.

## Main workflows

**Visitor:** homepage, eight categories, database search and filters, published content details, character profiles, articles, media, merchandise, upcoming releases, events and FAQ assistant.

**Member:** register/sign in, edit profile and avatar, choose favorite categories and display preferences, save any supported item, add private bookmark notes, rate content/media, submit original fan work, track moderation, send feedback and read replies.

**Administrator:** one editor handles content, characters, articles, media, events, merchandise, upcoming releases, carousel and FAQs. Each has a list and appropriate add/edit/delete/publication controls. Administrators also manage users, review submissions, reply to feedback, inspect real usage totals and change the site name/tagline.

Approving a fan submission creates a published article. Repeating approval does not duplicate it. Rejection or returning it to pending moves that article to draft. Unpublished items remain hidden from public details, searches and member bookmarks.

## Media, article and event authoring

- Upload valid JPG, PNG or WebP images, up to 5 MB and 24 megapixels. Filenames are generated on the server. SVG uploads and executable files are rejected.
- The multimedia editor accepts image URLs, direct MP4/WebM/audio URLs, or YouTube/Vimeo links. Use media you have permission to share. Supported embeds are constructed from validated IDs.
- Article bodies support a small, sanitized set of formatting elements: paragraphs, headings, lists, emphasis, links, blockquotes and images. Unsupported or unsafe HTML is removed.
- The articles page includes a chronological event highlights section with descriptions and links to event details.
- Merchandise supports category and fandom filtering and is a showcase. There is no checkout, payment gateway or order system. Add extra gallery images by editing the item again.
- Future merchandise drops require a date and also appear in Upcoming releases.
- Enter both latitude and longitude for an event to support nearby sorting and its map. City/month filtering works without location permission. Calendar downloads use the venue's local time as a floating time; check the imported time when travelling between timezones.
- External media and OpenStreetMap require an internet connection and their provider's availability. Browser geolocation requires localhost or HTTPS and user permission; coordinates stay in the browser.

## Password recovery

The included reset flow uses a random, hashed, expiring, single-use token and revokes previous account sessions after a successful reset. For localhost demonstrations, Forgot password can display the token link **only to a loopback request** (`127.0.0.1` or `::1`). It does not send email.

For a hosted deployment, add your chosen mail delivery service before enabling remote password recovery. The current interface clearly reports that email delivery is unavailable remotely. Administrators can reset an account password through User management after verifying its owner. Do not change the loopback restriction to expose tokens publicly.

## Security and implementation notes

- Prepared statements, output escaping and whitelisted module/field names.
- POST actions require CSRF tokens, including sign out and bookmark removal.
- Session ID regeneration at login, cookie protections, inactivity expiration and session revocation after password/account changes.
- Database-backed login throttling and administrator role checks.
- Private user data is filtered by its owner. Draft content is checked before details, bookmarks and ratings.
- View counts increment once per item per browser session; they are not unique-person analytics.
- The optional assistant matches administrator-maintained FAQs and stores per-user or per-session history. It is not an LLM and has no paid API dependency.
- `api/content.php` provides JSON content search/filter results; `api/bookmark.php` accepts authenticated CSRF-protected bookmark actions.

## Verification and submission boundaries

See `VERIFICATION.md` for the actual checks and their limits. This package is an application update with setup instructions, not a claim of a completed academic submission.

The SRS lists PHP with Laravel, while your explicit task asked to continue the existing plain PHP/MySQL application. Plain PHP is retained; confirm framework acceptance with your evaluator if Laravel is mandatory.

A recorded application walkthrough is included at `documentation/Fan-Hub-Plus-Demo.mp4`. It uses disposable test records and demonstrates the visitor, member and administrator workflows. The fresh SQL intentionally does not include those test records.

The SRS also requires a formal report and design diagrams. It also prohibits fully AI-written formal documentation. Prepare your own report and explanation based on your understanding; this README is an installation guide. Do not paste source code into the formal report. Acknowledge ChatGPT/Codex assistance in your submission and review/understand the changes before evaluation.

Public hosting and a 24/7 service guarantee are not included. No live email service has been configured. No claim is made that third-party media is continuously available or that Windows/XAMPP, Safari and Firefox were executed in this Linux test environment.
