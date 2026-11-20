-- EXAMPLE ONLY: replace EVERY password and database/user name before executing.
-- cPanel normally creates users/passwords in MySQL Databases; apply equivalent
-- table privileges here through phpMyAdmin/DBA after that. Never grant remotely (%).
CREATE USER 'mmgss_admin'@'localhost' IDENTIFIED BY 'REPLACE_WITH_UNIQUE_RANDOM_ADMIN_PASSWORD';
CREATE USER 'mmgss_public'@'localhost' IDENTIFIED BY 'REPLACE_WITH_DIFFERENT_RANDOM_PUBLIC_PASSWORD';
GRANT SELECT, INSERT, UPDATE, DELETE ON mmgss_recruitment.jobs TO 'mmgss_admin'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON mmgss_recruitment.job_translations TO 'mmgss_admin'@'localhost';
GRANT SELECT, INSERT, UPDATE ON mmgss_recruitment.admins TO 'mmgss_admin'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON mmgss_recruitment.login_limits TO 'mmgss_admin'@'localhost';
GRANT SELECT, INSERT ON mmgss_recruitment.audit_log TO 'mmgss_admin'@'localhost';
GRANT SELECT ON mmgss_recruitment.jobs TO 'mmgss_public'@'localhost';
GRANT SELECT ON mmgss_recruitment.job_translations TO 'mmgss_public'@'localhost';
-- The schema/migration user is temporary, separate from web-serving users.
-- Public users must never receive access to admins, audit_log or login_limits.
