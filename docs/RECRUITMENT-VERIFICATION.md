# Verification record — 20 November 2026

Implemented and tested on `codex/secure-recruitment-admin`. No live deployment,
production database connection, DNS change, production account creation, or main
branch modification was performed.

## Environment

- Windows, PHP 8.5.12 with PDO MySQL, mbstring and curl.
- Isolated XAMPP MariaDB instance bound to `127.0.0.1:33316`; newly created
  `mmgss_recruitment_test` database. Existing XAMPP databases were not used.
- Separate least-privilege public and admin test DB accounts.
- Public PHP server: `http://127.0.0.1:8088/recruitment`.
- Admin PHP server: `http://127.0.0.1:8089/login.php`.
- Playwright with installed Google Chrome, viewports 1440×1000 and 390×844.
- Browser plugin not available; regular Playwright used as the testing-skill fallback.
- Additional isolated Apache 2.4 server for real `.htaccess` checks, separate from
  the PHP development routers. Nested admin root was intentionally denied; the
  deployment guide places the admin document root outside the public root.

## Results

| Check | Result | Evidence |
| --- | --- | --- |
| PHP syntax | Pass | All 20 added PHP files linted with `php -l` |
| JavaScript syntax | Pass | Shared/public/admin scripts and browser test checked with `node --check` |
| Whitespace | Pass | `git diff --check` |
| HTTP/database integration | Pass | `php tests/run.php`: 52 checks |
| Additional security | Pass | `php tests/security.php`: 12 checks |
| Page identity and meaningful content | Pass | Admin headings/title, public URL/title and actual job cards |
| Rendered errors/overlays | Pass | PHP pages rendered with no app JavaScript errors or failed local requests |
| Desktop/mobile screenshots | Pass | Dashboard, login, public cards and mobile role modal inspected |
| Interaction proof | Pass | Real UI save → public cards → edit → close → reopen → archive → delete |
| Unrelated homepage | Pass | Home page loaded without JavaScript errors after shared-script changes |
| Apache route protection | Pass | Recruitment and index served; private code/SQL/scripts/tests/Git denied |

The initial data fixture has 18 unique jobs: 2 open and 16 closed. The SQL migration
was rerun to verify it does not duplicate existing records. Public results contain
only open/closed jobs and both translations; drafts and archives remain private.

The HTTP suite verifies authentication gates, CSRF on login/job actions, SQL
injection rejection, stored-XSS escaping, version-conflict rejection, account roles,
last-owner protection, account disabling, password changes, logout and persistent
login throttling. It also connects directly as the public DB user and proves that
user cannot read admin/audit records or write jobs.

The security suite verifies login session-ID rotation, idle and absolute expiry,
production seed rejection, weak bootstrap-password rejection, strong owner creation
and test-account disabling **in the isolated test database**, rejection of test
account logins/sessions even if re-enabled, and the Secure/HttpOnly/SameSite/host-only
production cookie configuration. It restores the local seed afterward.

The browser suite exercises the actual confirmation dialogs, including cancellation,
and checks public category filters, admin pagination, English/Japanese titles,
mobile layout without horizontal page overflow, literal XSS text in the role modal,
and membership in exactly one section after close/reopen. It reports no app errors
or failed requests. Screenshots are generated outside the repository under the OS
temporary directory; they are not application assets.

Key commands:

```text
php tests/run.php
php tests/security.php
node tests/browser.mjs
php -l <each added PHP file>
node --check <each changed JavaScript file>
git diff --check
```

## Release checks still required on the actual host

Actual cPanel/PHP-FPM environment propagation, DNS, AutoSSL, TLS termination, browser
Secure-cookie transmission over real HTTPS, provider-specific MySQL grants and
backup/restore procedures require staging/hosting access. Local tests do not prove
those production settings. No real production password or account is supplied;
the deployment guide creates a unique owner through the CLI and disables test users.

Chrome was tested; Safari and Firefox were not. This is an implementation test suite,
not an independent penetration test. Production deployment steps and rollback are
in `RECRUITMENT-DEPLOYMENT.md`.

Security design references: [PHP session configuration](https://www.php.net/manual/en/session.security.ini.php)
and [OWASP authentication guidance](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html).
