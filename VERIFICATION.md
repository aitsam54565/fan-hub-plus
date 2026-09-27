# Verification record

Verified on 26 September 2026 in an isolated Linux environment using PHP 8.3.6, MariaDB 10.11.14 and headless Chromium. Tests used a real PHP server and database, not mocked responses.

## Results

| Check | Result |
| --- | --- |
| PHP syntax checks | All 85 PHP files passed |
| Fresh installation SQL | Imported successfully and supported the application workflows |
| Upgrade from the supplied original SQL | Existing 5 content records and 2 users preserved |
| Repeating a successful upgrade | Skipped safely; one migration version recorded |
| HTTP and workflow assertions | 929 passed, 0 failed |
| Local link and asset targets visited | 110 passed |
| Desktop/mobile browser checks | 30 passed, 0 JavaScript errors or detected layout failures |

The assertion count includes repeated response, CSRF and redirect checks; it is not a count of separate features. Automated checks reduce risk but do not establish that software has no defects.

## Workflows exercised

- All public categories, database searches and advanced filters, content API, details and empty/error states.
- Administrator create/edit/delete across content, characters, articles, media, events, merchandise, upcoming releases, carousel and FAQs.
- Publication changes reflected on public pages. Draft details, API results and bookmark requests protected.
- Merchandise fandom filtering, event story highlights and chronological calendar discovery.
- Registration, sign-in, sign-out, protected routes and user/admin role separation.
- Profile editing, actual avatar upload, stored favorite categories and display preferences. Forged image content rejected.
- Bookmarks for content, articles, characters, media and merchandise; deduplication, private notes and owner isolation.
- Content and media ratings; sanitized article formatting with unsafe script markup removed.
- Submission approval, rejection and repeat approval without duplicate articles.
- Feedback submission, administrator response and status changes.
- Local reset token expiry checks in application validation, single use and invalidation of existing sessions after a reset. The automated workflow exercised use/reuse and session invalidation; it did not wait 30 minutes to test natural expiry.
- FAQ matching and chat history separation; event city/month filters and calendar export; active/inactive homepage carousel.
- CSRF rejection and protection against accessing another member's bookmark by ID.

Browser captures covered the homepage, category, search, content, articles, media, events, sign-in, user dashboard, profile, bookmarks, admin dashboard, editor and event administration at 1440px and 390px widths. Mobile navigation and visitor display preference persistence were exercised. Visual review caught and corrected reduced-motion hero text, card image sizing and mobile panel overflow.

## Test data and package boundaries

Test records were created through application forms/API requests in a disposable database. Records named `QA ...`, test feedback, reset tokens, browser cookies and generated test uploads are not shipped in the final SQL or project archive. The original supplied assets are retained. The fresh SQL contains categories, local test accounts, a getting-started article and helpful FAQs; it intentionally has no invented engagement metrics or commercial media catalog.

The separate demonstration MP4 records the running application with test data. It is a walkthrough, not proof that every browser or external provider works. The final fresh installation starts with the clean seed described above.

## Limits and deployment work

- Linux PHP/MariaDB and Chromium were executed. Windows/XAMPP, MySQL itself, Safari, Firefox, real mobile devices and assistive technologies were not executed.
- Apache `.htaccess` restrictions are supplied but the test server was PHP's built-in server; verify the restrictions on the intended Apache deployment.
- External video/audio providers, third-party ticket links, real GPS accuracy and production email delivery were not verified. Geolocation is optional and location coordinates remain in the browser.
- Remote password-reset email needs a configured mail provider. The included token-link demonstration is restricted to localhost.
- No load/penetration test, production hosting or availability guarantee is included. Change the documented local test passwords before public deployment.
- Plain PHP is retained as requested. The SRS also names Laravel; obtain evaluator acceptance if that framework is strictly required.
- The SRS's formal report and design diagrams remain student-authored deliverables. This installation guide and verification record are not a substitute for that report or your own explanation of the project.
