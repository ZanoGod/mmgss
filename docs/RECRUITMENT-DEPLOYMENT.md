# Recruitment administration

PHP 8.2+ with PDO MySQL, mbstring, sessions and JSON; MySQL 8.0+ or MariaDB 10.4+;
Apache 2.4 with mod_rewrite and AllowOverride All.
Bootstrap 5.3.3 is vendored, with its MIT notice, so the admin has no CDN dependency.
No Composer or JavaScript framework dependency was added.

## Layout and behavior

- `admin/public/`: the admin subdomain document root, with its own assets.
- `recruitment-app/`: shared private PHP code. Keep outside both production roots.
- `api/jobs.php`: read-only, same-origin public listing endpoint. It can use a
  separate DB user or the shared admin database credentials.
- `recruitment.html` and `js/recruitment.js`: existing public design, database-backed
  cards, separate category filters, bilingual detail modal and language switcher.
- `database/schema.sql`: initial schema, no default users.
- `database/existing-jobs.sql`: snapshot of the repository's 18 roles at commit
  `812616b`: 2 open and 16 closed, with English/Japanese text. Full original role
  details are retained instead of the old generic closed-intake detail text.
- `scripts/`: CLI-only provisioning and counter cleanup; never expose over HTTP.

One job has one status and two translation rows. Open/closed rows are public;
draft/archived rows are private. Close/reopen changes the status of the same row.
Deletion requires archiving first. Form saves use versions to reject concurrent
edits. Owners manage accounts; editors manage jobs and their own password. At least
one active owner must remain. Disabling an account or changing its password
invalidates existing sessions. No public account registration exists.

Job content is plain text, escaped on admin output and inserted with `textContent`
on the public page. There is no HTML editor, public write API, CORS sharing, or
browser-side database credential. Empty Japanese fields fall back to English;
both titles are required. Job data has been removed from the locale JSON files;
those files now contain interface translations only.

The public page requests current data on load, when the tab becomes visible, and
every 60 seconds while visible. Reload for an immediate update after an admin save.
Skeleton cards appear during the initial load and respect reduced-motion preferences.
Failures show an availability message and keep any already-loaded listings. Bypass caching
for `/api/jobs.php` and all admin paths at any CDN/reverse proxy.

## Using one database user

To configure credentials once, set `database.admin_user` and
`database.admin_password`. Omit `public_user`, `public_password`, `cli_user` and
`cli_password`, or leave their usernames blank. Public requests and CLI commands
then use the admin database credentials automatically. Existing installations
with explicitly configured public/CLI usernames continue to use those accounts.

The shared account needs the admin DML permissions in `permissions.example.sql`.
Both website connections then have the same database permissions, including write
access. Separate public credentials provide database-level read-only isolation.
The public HTTP endpoint itself continues to accept GET requests only.

Upload the updated `recruitment-app/bootstrap.php` to the shared private
application folder before using this configuration. Keep the PHP configuration in
the private `config.php`; `.htaccess` contains Apache directives such as `SetEnv`,
not PHP code. Database passwords belong only in the private configuration.

## XAMPP on Windows

1. Keep the source on the feature branch. Start XAMPP Apache and MySQL. Enable
   `pdo_mysql` and `mbstring` in the PHP version used by Apache and CLI. Verify
   `php -v` and `php -m`; they can differ from Apache's selected version.
2. In phpMyAdmin, create `mmgss_recruitment` with `utf8mb4_unicode_ci`. Import
   `database/schema.sql`, then `database/existing-jobs.sql` with UTF-8 selected.
   The schema is for an empty database; do not rerun it on populated tables.
   The existing-job import can be retried without overwriting records, but is an
   initial migration, not a synchronization task. Do not run it after deleting jobs.
3. Create separate local users and apply the privileges in
   `database/permissions.example.sql`, replacing names/passwords. Use `localhost`
   in both DSN and grants, or grant for `127.0.0.1` if using that DSN. Avoid `%`.
4. Copy `recruitment-app/config.example.php` to the ignored
   `recruitment-app/config.local.php`. Keep `environment` as `local`, fill in the
   real DB credentials, and set `public_url` to `http://mmgss.local`. The CLI user
   can use the admin DML account after the DBA imports the schema/migration.
5. Put the admin document root outside the public source directory. In PowerShell:

   ```powershell
   New-Item -ItemType Directory -Force C:/xampp/htdocs/mmgss-admin
   Copy-Item D:/Code/mmgss/admin/public/* C:/xampp/htdocs/mmgss-admin -Recurse -Force
   ```

   Include `.htaccess` and `assets`. Repeat the copy after admin changes. Do not
   serve the admin as `/admin/public` under the public website; that path is denied.
6. Enable `mod_rewrite` and `mod_env` in Apache. Add to
   `C:/xampp/apache/conf/extra/httpd-vhosts.conf`:

   ```apache
   <VirtualHost *:80>
     ServerName mmgss.local
     DocumentRoot "D:/Code/mmgss"
     SetEnv MMGSS_APP_PATH "D:/Code/mmgss/recruitment-app"
     SetEnv MMGSS_CONFIG_FILE "D:/Code/mmgss/recruitment-app/config.local.php"
     <Directory "D:/Code/mmgss">
       AllowOverride All
       Require all granted
     </Directory>
   </VirtualHost>
   <VirtualHost *:80>
     ServerName admin.mmgss.local
     DocumentRoot "C:/xampp/htdocs/mmgss-admin"
     SetEnv MMGSS_APP_PATH "D:/Code/mmgss/recruitment-app"
     SetEnv MMGSS_CONFIG_FILE "D:/Code/mmgss/recruitment-app/config.local.php"
     <Directory "C:/xampp/htdocs/mmgss-admin">
       AllowOverride All
       Require all granted
     </Directory>
   </VirtualHost>
   ```

   Add `127.0.0.1 mmgss.local admin.mmgss.local` to the Windows hosts file, using an
   elevated editor. Restart Apache. Keep the admin root outside the public root:
   ancestor `.htaccess` protections intentionally deny the nested source folder.
7. From the source directory run:

   ```powershell
   php scripts/seed-local.php
   ```

   Sign in at `http://admin.mmgss.local/login.php` using `admin` / `admin123`.
   This seed works only with `environment=local` and a loopback MySQL DSN. It never
   overwrites an existing account. Do not point local config at production data.
8. Open `http://mmgss.local/recruitment`. Create an open job, reload the page,
   edit it, close it, then reopen it. It should appear exactly once in the matching
   section. Check JP and EN and open the role detail modal in each language.

## cPanel production deployment

Perform a staging deployment and backup the existing files and database first.
This branch has not changed production DNS, hosting, data, or accounts.

1. In cPanel Domains create `admin.myanmargss.com` with a document root outside
   `public_html`, e.g. `/home/ACCOUNT/admin_public`. Keep the main document root
   `/home/ACCOUNT/public_html`. Point the admin DNS A/AAAA records to the hosting
   server. Do not use GitHub Pages: it cannot execute PHP or connect to MySQL.
2. Create `/home/ACCOUNT/mmgss-private`. Upload `recruitment-app/`, `database/` and
   `scripts/` together there, preserving this structure:

   ```text
   /home/ACCOUNT/mmgss-private/recruitment-app/*.php
   /home/ACCOUNT/mmgss-private/database/*.sql
   /home/ACCOUNT/mmgss-private/scripts/*.php
   /home/ACCOUNT/mmgss-private/config.php
   /home/ACCOUNT/admin_public/          (contents of admin/public)
   /home/ACCOUNT/public_html/api/jobs.php
   ```

   Upload the changed public recruitment files and locale files to the existing
   public root. Preserve unrelated files. Include the public `.htaccess` rules,
   merging them with any hosting-specific HTTPS/domain rules. Do not upload `.git`,
   local config, tests, development routers, screenshots, or test data. Purge any
   cached recruitment HTML, JavaScript, CSS and locale files after uploading.
3. In MySQL Databases create the database and two different web users using unique
   random passwords. cPanel adds account prefixes; use those exact names in config
   and grants. Import `schema.sql` and `existing-jobs.sql` via phpMyAdmin as the DBA.
   Apply the table grants in `permissions.example.sql` if your host supports them.
   Public user: SELECT on jobs and job_translations only. Admin user: DML privileges
   as listed, no CREATE, DROP, GRANT, FILE or global privileges. If cPanel cannot
   grant per table, ask the provider to apply those grants; do not give the public
   user SELECT on the entire database. Keep the DBA credential out of web config.
4. Copy the example config to `/home/ACCOUNT/mmgss-private/config.php`. Set
   `environment` to `production`, `public_url` to `https://myanmargss.com`, and the
   production DB credentials. Keep MySQL on localhost with no public port exposure.
   For a remote database, require verified TLS with a provider CA in `ssl_ca`, and
   restrict the database firewall to the hosting server. Keep CLI credentials to
   the admin DML user except during explicitly supervised schema maintenance.
5. Set both `MMGSS_APP_PATH=/home/ACCOUNT/mmgss-private/recruitment-app` and
   `MMGSS_CONFIG_FILE=/home/ACCOUNT/mmgss-private/config.php` in each site's PHP
   environment. On Apache hosts that pass `SetEnv` to PHP, add these lines to each
   document root's `.htaccess`:

   ```apache
   SetEnv MMGSS_APP_PATH /home/ACCOUNT/mmgss-private/recruitment-app
   SetEnv MMGSS_CONFIG_FILE /home/ACCOUNT/mmgss-private/config.php
   ```

   If your PHP-FPM host does not propagate `SetEnv`, have the provider set these
   variables in the pool configuration. Verify them through application behavior;
   do not publish a phpinfo page. The app fails closed when config is missing.
6. Enable AutoSSL for both domains. Force HTTPS using cPanel. PHP must receive
   `HTTPS=on` from Apache; the app refuses HTTP in production and does not trust
   client-supplied `X-Forwarded-Proto`. A TLS-offloading proxy must be configured
   by the host to set the real server HTTPS state after verifying the proxy.
   Admin cookies are host-only, `Secure`, `HttpOnly`, `SameSite=Strict`, and use the
   `__Host-` prefix. The public site never receives or reads the admin session.
7. Set private directories to 750 and config to 600 (or 640 only when required by
   the PHP execution group); public directories 755 and files 644. Both applications
   must be able to read shared code/config. Do not make it world-writable. Configure
   a private per-account PHP session directory and error log outside the web roots.
   In cPanel MultiPHP INI set `display_errors=Off`, `log_errors=On`,
   `session.use_strict_mode=1`, `session.use_only_cookies=1`, and reasonable request
   size limits (e.g. `post_max_size=1M`, `max_input_vars=100`). No admin uploads exist.
8. Create the real owner via cPanel Terminal/SSH. Use a password manager-generated
   unique passphrase, at least 14 bytes and at most 72 bytes. This Bash sequence
   reads it without echoing or putting it in command history:

   ```bash
   cd /home/ACCOUNT/mmgss-private
   export MMGSS_CONFIG_FILE=/home/ACCOUNT/mmgss-private/config.php
   read -r -s -p 'New owner password: ' MMGSS_ADMIN_PASSWORD; echo
   export MMGSS_ADMIN_PASSWORD
   php scripts/create-admin.php YOUR_UNIQUE_OWNER_USERNAME
   unset MMGSS_ADMIN_PASSWORD
   ```

   This creates a hashed non-test owner and disables every seeded test account.
   The application also refuses test-account login/sessions in production even if
   an operator accidentally re-enables the row. Do not run the local seed against
   production. No known production password is shipped in this repository.
9. Log in over HTTPS, create a second owner for recovery, and verify create, edit,
   close, reopen and Japanese translations with a staging job before removing it.
   Confirm `admin123` fails, the public API cannot mutate records, account disabling
   signs out the disabled admin, and private paths return 403/404. Confirm browser
   cookie flags, CSP, HSTS, no-store headers and all assets on the actual host.
10. Set a daily cPanel cron to run `scripts/cleanup-login-limits.php` with
    `MMGSS_CONFIG_FILE` set and a private PHP log. Example:

    ```cron
    17 3 * * * MMGSS_CONFIG_FILE=/home/ACCOUNT/mmgss-private/config.php /usr/local/bin/php /home/ACCOUNT/mmgss-private/scripts/cleanup-login-limits.php >> /home/ACCOUNT/mmgss-private/maintenance.log 2>&1
    ```

    Adjust the PHP binary to the selected MultiPHP version. Keep backups of the
    database and audit trail, monitor private error logs, and limit recovery CLI
    access to trusted operators. To rotate/recover a lost owner, run create-admin
    with a fresh username, sign in, and disable the old account.

## Rollback

Keep both document-root backups and a database backup from before deployment.
For an application rollback restore the public/admin files and disable access to
the admin subdomain. Do not drop the database or roll back data automatically:
new posts and account changes may have been made since release. Restore data only
through a reviewed recovery plan. The legacy static recruitment page requires the
matching original locale/script files if reverting fully to the previous version.
