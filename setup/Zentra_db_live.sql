CREATE DATABASE IF NOT EXISTS `zentra_db_live` DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
USE `zentra_db_live`;

DROP TABLE IF EXISTS `install_log`;
CREATE TABLE IF NOT EXISTS `install_log` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `action_type` VARCHAR(50) DEFAULT NULL,
    `table_name` VARCHAR(255) DEFAULT NULL,
    `status` VARCHAR(20) DEFAULT NULL,
    `message` TEXT DEFAULT NULL,
    `query_text` LONGTEXT DEFAULT NULL,
    `utc_time` DATETIME DEFAULT NULL,
    `local_time` DATETIME DEFAULT NULL,
    `timezone` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_action_type` (`action_type`),
    INDEX `idx_table_name` (`table_name`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `zentra_users`;
CREATE TABLE IF NOT EXISTS `zentra_users`(
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `default_tenant_id` BIGINT UNSIGNED NOT NULL,   -- NEW COLUMN
  `empid` VARCHAR(10) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `middle_name` VARCHAR(50) DEFAULT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `gender` VARCHAR(10) DEFAULT NULL,
  `birthday` DATE DEFAULT NULL,
  `healthcard` VARCHAR(50) DEFAULT NULL,
  `job_company` VARCHAR(500) NOT NULL,
  `user_name` VARCHAR(200) NOT NULL DEFAULT '',
  `user_email` VARCHAR(220) NOT NULL DEFAULT '',
  `user_level` INT UNSIGNED NOT NULL DEFAULT 1,
  `pwd` VARCHAR(255) NOT NULL,
  `address` VARCHAR(500) DEFAULT NULL,
  `street` VARCHAR(500) DEFAULT NULL,
  `pic` VARCHAR(900) NOT NULL DEFAULT 'images/avatar.png',
  `city` VARCHAR(100) DEFAULT NULL,
  `zipcode` VARCHAR(10) DEFAULT NULL,
  `province` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(200) NOT NULL DEFAULT 'CA',
  `cellphone` VARCHAR(16) DEFAULT NULL,
  `job_title` VARCHAR(500) DEFAULT NULL,
  `start_date` DATE DEFAULT NULL,
  `tel` VARCHAR(200) NOT NULL DEFAULT '',
  `jobcompany` VARCHAR(200) NOT NULL,
  `job_department` VARCHAR(50) DEFAULT NULL,
  `termination_date` DATE DEFAULT NULL,
  `termination_reason` VARCHAR(500) DEFAULT NULL,
  `website` VARCHAR(500) NOT NULL,
  `date_created` DATETIME NOT NULL,
  `users_ip` VARCHAR(200) NOT NULL DEFAULT '',
  `approved` INT UNSIGNED NOT NULL DEFAULT 0,
  `email_verify` VARCHAR(20) DEFAULT NULL,
  `email_verified_on` DATETIME NOT NULL,
  `verification_email_sent` DATETIME NOT NULL DEFAULT current_timestamp(),
  `activation_code` INT UNSIGNED NOT NULL DEFAULT 0,
  `banned` INT UNSIGNED NOT NULL DEFAULT 0,
  `ckey` VARCHAR(220) NOT NULL DEFAULT '',
  `ctime` VARCHAR(220) NOT NULL DEFAULT '',
  `reset_token` VARCHAR(255) DEFAULT NULL,
  `reset_expires` DATETIME DEFAULT NULL,
  `geo_raw` JSON NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_email` (`user_email`),
  UNIQUE KEY `user_name` (`user_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

TRUNCATE TABLE `zentra_users`;

INSERT INTO `zentra_users` VALUES (1,'19910509','','Sai Deepak',NULL,'C',NULL,NULL,NULL,'','sai@livewd.ca','sai@livewd.ca',9,'$2y$10$8cgXF8pQ8oYRcqiu1xIGTu22HUbjEhINHgdit.pv.OAGeHdBv4thC',NULL,NULL,'images/avatar.png',NULL,NULL,NULL,'CA',NULL,NULL,NULL,'','',NULL,NULL,'y8j~m2VKif3l%BaN]UJls','','2026-02-20 06:03:41','209.121.189.20',1,NULL,'2026-02-20 06:03:41','2026-02-20 06:03:41',0,0,'','',NULL,NULL,NULL);

DROP TABLE IF EXISTS `zentra_tenants`;
CREATE TABLE IF NOT EXISTS `zentra_tenants` (
  `tenant_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  -- Core Identity
  `tenant_name` VARCHAR(255) NOT NULL,
  `tenant_code` VARCHAR(100) NOT NULL UNIQUE,
  `industry` VARCHAR(150) DEFAULT NULL,
  -- Contact & Ownership
  `owner_user_id` BIGINT UNSIGNED DEFAULT NULL,
  `contact_email` VARCHAR(255) DEFAULT NULL,
  `contact_phone` VARCHAR(50) DEFAULT NULL,
  -- Address
  `address_line1` VARCHAR(255) DEFAULT NULL,
  `address_line2` VARCHAR(255) DEFAULT NULL,
  `city` VARCHAR(150) DEFAULT NULL,
  `province` VARCHAR(150) DEFAULT NULL,
  `postal_code` VARCHAR(20) DEFAULT NULL,
  `country` VARCHAR(150) DEFAULT NULL,
  -- Currency
  `currency_code` VARCHAR(10) NOT NULL DEFAULT 'USD',   -- e.g., USD, CAD, EUR
  -- Branding
  `logo_url` VARCHAR(500) DEFAULT NULL,
  `primary_color` VARCHAR(20) DEFAULT NULL,
  `secondary_color` VARCHAR(20) DEFAULT NULL,
  -- Domain / URL
  `custom_domain` VARCHAR(255) DEFAULT NULL,
  `subdomain` VARCHAR(255) DEFAULT NULL,
  -- Subscription & Lifecycle
  `subscription_plan` VARCHAR(100) NOT NULL DEFAULT 'FREE',
  `subscription_status` VARCHAR(50) NOT NULL DEFAULT 'ACTIVE',
  `subscription_start` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `trial_start` DATETIME DEFAULT NULL,
  `trial_end` DATETIME DEFAULT NULL,
  -- Audit
  `created_on` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `updated_on` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` BIGINT UNSIGNED DEFAULT NULL,
  `deleted_on` DATETIME DEFAULT NULL,
  `deleted_by` BIGINT UNSIGNED DEFAULT NULL,
  `geo_raw` JSON NULL,
  PRIMARY KEY (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO zentra_tenants (
    tenant_id,
    tenant_name,
    tenant_code,
    contact_email,
    subscription_plan,
    subscription_status
) VALUES (
    19910509,
    'Zentra HQ',
    'FLEETHQ',
    'sai@livewd.ca',
    'INTERNAL',
    'ACTIVE'
);

UPDATE zentra_tenants SET tenant_id = 20000000 WHERE tenant_id = 1 AND tenant_id < 20000001;

ALTER TABLE zentra_users
ADD CONSTRAINT fk_users_default_tenant
FOREIGN KEY (default_tenant_id)
REFERENCES zentra_tenants(tenant_id)
ON DELETE RESTRICT;

DROP TABLE IF EXISTS `zentra_user_tenants`;
CREATE TABLE IF NOT EXISTS `zentra_user_tenants` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  -- Foreign keys
  `user_id` BIGINT UNSIGNED NOT NULL,
  `tenant_id` BIGINT UNSIGNED NOT NULL,
  `role_id` BIGINT UNSIGNED NULL,
  -- Role within this tenant
  `role` VARCHAR(100) NOT NULL DEFAULT 'user',
  -- Lifecycle
  `assigned_on` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  `geo_raw` JSON NULL,
  -- Prevent duplicate assignments
  UNIQUE KEY `unique_user_tenant` (`user_id`, `tenant_id`),
  -- Foreign key constraints
  CONSTRAINT `fk_ut_user`
    FOREIGN KEY (`user_id`) REFERENCES `zentra_users`(`id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_ut_tenant`
    FOREIGN KEY (`tenant_id`) REFERENCES `zentra_tenants`(`tenant_id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `zentra_user_roles`;
CREATE TABLE IF NOT EXISTS `zentra_user_roles` (
  `role_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_key` VARCHAR(100) NOT NULL UNIQUE,
  `role_name` VARCHAR(150) NOT NULL,
  `role_type` ENUM('system','tenant') NOT NULL DEFAULT 'tenant',
  `is_system_default` TINYINT(1) NOT NULL DEFAULT 0,
  `description` VARCHAR(500) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_on` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_on` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `geo_raw` JSON NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE zentra_user_tenants
ADD CONSTRAINT fk_ut_role
FOREIGN KEY (role_id)
REFERENCES zentra_user_roles(role_id)
ON DELETE RESTRICT;

INSERT INTO zentra_user_tenants (user_id, tenant_id, role)
VALUES (1, 19910509, 'MASTERSUPERADMIN');

INSERT INTO zentra_user_roles 
(role_key, role_name, role_type, is_system_default, description)
VALUES
('MASTERSUPERADMIN', 'Master Super Admin', 'system', 1, 'Full platform access'),
('SUPPORT', 'Support Staff', 'system', 1, 'Internal support team'),
('DEVELOPER', 'Developer', 'system', 1, 'Internal technical role'),
('OWNER', 'Tenant Owner', 'tenant', 1, 'Creator of the tenant'),
('ADMIN', 'Administrator', 'tenant', 1, 'Full access to tenant resources'),
('MANAGER', 'Manager', 'tenant', 1, 'Manages teams and resources'),
('USER', 'Standard User', 'tenant', 1, 'Basic access'),
('READONLY', 'Read Only', 'tenant', 1, 'View-only access');


UPDATE zentra_user_tenants
SET role_id = (
    SELECT role_id 
    FROM zentra_user_roles 
    WHERE role_key = 'MASTERSUPERADMIN'
)
WHERE user_id = 1 AND tenant_id = 19910509;


DROP TABLE IF EXISTS `zentra_activityaudit`;
CREATE TABLE IF NOT EXISTS `zentra_activityaudit` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tbl_name` VARCHAR(100) DEFAULT NULL,
  `reference_key` TEXT DEFAULT NULL,
  `activity` LONGTEXT DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT current_timestamp(),
  `ipaddress` VARCHAR(225) DEFAULT NULL,
  `instance_url` TEXT DEFAULT NULL,
  `username` VARCHAR(225) DEFAULT NULL,
  `geo_raw` JSON NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

TRUNCATE TABLE `zentra_activityaudit`;

DROP TABLE IF EXISTS `zentra_emailtemplates`;
CREATE TABLE IF NOT EXISTS `zentra_emailtemplates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `index_key` VARCHAR(100) NOT NULL,
  `template_code` LONGTEXT DEFAULT NULL,
  `active` INT UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_key_UNIQUE` (`index_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

TRUNCATE TABLE `zentra_emailtemplates`;

INSERT IGNORE INTO `zentra_emailtemplates` (`id`, `index_key`, `template_code`, `active`) VALUES
(1, 'login-credentials', '<!DOCTYPE html PUBLIC \'-//W3C//DTD XHTML 1.0 Transitional //EN\' \'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\'><html xmlns=\'http://www.w3.org/1999/xhtml\' xmlns:o=\'urn:schemas-microsoft-com:office:office\' xmlns:v=\'urn:schemas-microsoft-com:vml\'><head><meta content=\'TEXT/html; charset=utf-8\' http-equiv=\'Content-Type\'/><meta content=\'width=device-width\' name=\'viewport\'/><meta content=\'IE=edge\' http-equiv=\'X-UA-Compatible\'/><title></title><style type=\'TEXT/css\'>body{margin: 0;padding: 0;}table,td,tr{vertical-align: top;border-collapse: collapse;}*{line-height: inherit;}a[x-apple-data-detectors=true]{color: inherit !important;TEXT-decoration: none !important;}.ie-browser table{table-layout: fixed;}[owa] .img-container div,[owa] .img-container button{display: block !important;}[owa] .fullwidth button{width: 100% !important;}[owa] .block-grid .col{display: table-cell;float: none !important;vertical-align: top;}.ie-browser .block-grid,.ie-browser .num12,[owa] .num12,[owa] .block-grid{width: 650px !important;}.ie-browser .mixed-two-up .num4,[owa] .mixed-two-up .num4{width: 216px !important;}.ie-browser .mixed-two-up .num8,[owa] .mixed-two-up .num8{width: 432px !important;}.ie-browser .block-grid.two-up .col,[owa] .block-grid.two-up .col{width: 324px !important;}.ie-browser .block-grid.three-up .col,[owa] .block-grid.three-up .col{width: 324px !important;}.ie-browser .block-grid.four-up .col [owa] .block-grid.four-up .col{width: 162px !important;}.ie-browser .block-grid.five-up .col [owa] .block-grid.five-up .col{width: 130px !important;}.ie-browser .block-grid.six-up .col,[owa] .block-grid.six-up .col{width: 108px !important;}.ie-browser .block-grid.seven-up .col,[owa] .block-grid.seven-up .col{width: 92px !important;}.ie-browser .block-grid.eight-up .col,[owa] .block-grid.eight-up .col{width: 81px !important;}.ie-browser .block-grid.nine-up .col,[owa] .block-grid.nine-up .col{width: 72px !important;}.ie-browser .block-grid.ten-up .col,[owa] .block-grid.ten-up .col{width: 60px !important;}.ie-browser .block-grid.eleven-up .col,[owa] .block-grid.eleven-up .col{width: 54px !important;}.ie-browser .block-grid.twelve-up .col,[owa] .block-grid.twelve-up .col{width: 50px !important;}</style><style id=\'media-query\' type=\'TEXT/css\'>@media only screen and (min-width: 670px){.block-grid{width: 650px !important;}.block-grid .col{vertical-align: top;}.block-grid .col.num12{width: 650px !important;}.block-grid.mixed-two-up .col.num3{width: 162px !important;}.block-grid.mixed-two-up .col.num4{width: 216px !important;}.block-grid.mixed-two-up .col.num8{width: 432px !important;}.block-grid.mixed-two-up .col.num9{width: 486px !important;}.block-grid.two-up .col{width: 325px !important;}.block-grid.three-up .col{width: 216px !important;}.block-grid.four-up .col{width: 162px !important;}.block-grid.five-up .col{width: 130px !important;}.block-grid.six-up .col{width: 108px !important;}.block-grid.seven-up .col{width: 92px !important;}.block-grid.eight-up .col{width: 81px !important;}.block-grid.nine-up .col{width: 72px !important;}.block-grid.ten-up .col{width: 65px !important;}.block-grid.eleven-up .col{width: 59px !important;}.block-grid.twelve-up .col{width: 54px !important;}}@media (max-width: 670px){.block-grid,.col{min-width: 320px !important;max-width: 100% !important;display: block !important;}.block-grid{width: 100% !important;}.col{width: 100% !important;}.col>div{margin: 0 auto;}img.fullwidth,img.fullwidthOnMobile{max-width: 100% !important;}.no-stack .col{min-width: 0 !important;display: table-cell !important;}.no-stack.two-up .col{width: 50% !important;}.no-stack .col.num4{width: 33% !important;}.no-stack .col.num8{width: 66% !important;}.no-stack .col.num4{width: 33% !important;}.no-stack .col.num3{width: 25% !important;}.no-stack .col.num6{width: 50% !important;}.no-stack .col.num9{width: 75% !important;}.video-block{max-width: none !important;}.mobile_hide{min-height: 0px;max-height: 0px;max-width: 0px;display: none;overflow: hidden;font-size: 0px;}.desktop_hide{display: block !important;max-height: none !important;}}</style></head><body class=\'clean-body\' style=\'margin: 0; padding: 0; -webkit-TEXT-size-adjust: 100%; background-color: #F1F3F3;\'><style id=\'media-query-bodytag\' type=\'TEXT/css\'>@media (max-width: 670px){.block-grid{min-width: 320px!important; max-width: 100%!important; width: 100%!important; display: block!important;}.col{min-width: 320px!important; max-width: 100%!important; width: 100%!important; display: block!important;}.col > div{margin: 0 auto;}img.fullwidth{max-width: 100%!important; height: auto!important;}img.fullwidthOnMobile{max-width: 100%!important; height: auto!important;}.no-stack .col{min-width: 0!important; display: table-cell!important;}.no-stack.two-up .col{width: 50%!important;}.no-stack.mixed-two-up .col.num4{width: 33%!important;}.no-stack.mixed-two-up .col.num8{width: 66%!important;}.no-stack.three-up .col.num4{width: 33%!important}.no-stack.four-up .col.num3{width: 25%!important}}</style><table bgcolor=\'#F1F3F3\' cellpadding=\'0\' cellspacing=\'0\' class=\'nl-container\' role=\'presentation\' style=\'table-layout: fixed; vertical-align: top; min-width: 320px; Margin: 0 auto; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #F1F3F3; width: 100%;\' valign=\'top\' width=\'100%\'><tbody><tr style=\'vertical-align: top;\' valign=\'top\'><td style=\'word-break: break-word; vertical-align: top; border-collapse: collapse;\' valign=\'top\'><div style=\'background-color:transparent;\'><div class=\'block-grid\' style=\'Margin: 0 auto; min-width: 320px; max-width: 650px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;;\'><div style=\'border-collapse: collapse;display: table;width: 100%;background-color:transparent;\'><div class=\'col num12\' style=\'min-width: 320px; max-width: 650px; display: table-cell; vertical-align: top;;\'><div style=\'width:100% !important;\'><div style=\'border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;\'><table border=\'0\' cellpadding=\'0\' cellspacing=\'0\' class=\'divider\' role=\'presentation\' style=\'table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-TEXT-size-adjust: 100%; -webkit-TEXT-size-adjust: 100%;\' valign=\'top\' width=\'100%\'><tbody><tr style=\'vertical-align: top;\' valign=\'top\'><td class=\'divider_inner\' style=\'word-break: break-word; vertical-align: top; min-width: 100%; -ms-TEXT-size-adjust: 100%; -webkit-TEXT-size-adjust: 100%; padding-top: 10px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px; border-collapse: collapse;\' valign=\'top\'><table align=\'center\' border=\'0\' cellpadding=\'0\' cellspacing=\'0\' class=\'divider_content\' height=\'15\' role=\'presentation\' style=\'table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; border-top: 0px solid transparent; height: 15px;\' valign=\'top\' width=\'100%\'><tbody><tr style=\'vertical-align: top;\' valign=\'top\'><td height=\'15\' style=\'word-break: break-word; vertical-align: top; -ms-TEXT-size-adjust: 100%; -webkit-TEXT-size-adjust: 100%; border-collapse: collapse;\' valign=\'top\'><span></span></td></tr></tbody></table></td></tr></tbody></table></div></div></div></div></div></div><div style=\'background-color:transparent;\'><div class=\'block-grid\' style=\'Margin: 0 auto; min-width: 320px; max-width: 650px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #FFFFFF;;\'><div style=\'border-collapse: collapse;display: table;width: 100%;background-color:#FFFFFF;\'><div class=\'col num12\' style=\'min-width: 320px; max-width: 650px; display: table-cell; vertical-align: top;;\'><div style=\'background-color:#FFFFFF;width:100% !important;\'><div style=\'border-top:0px solid transparent; border-left:8px solid #F1F3F3; border-bottom:0px solid transparent; border-right:8px solid #F1F3F3; padding-top:50px; padding-bottom:5px; padding-right: 50px; padding-left: 50px;\'><div style=\'font-size:16px;TEXT-align:center;font-family:Arial, \'Helvetica Neue\', Helvetica, sans-serif\'><img alt=\'$businessname\' src=\'$logo_color_large\' title=\'$businessname\' width=\'80%\'/></div><div class=\'desktop_hide\' style=\'mso-hide: all; display: none; max-height: 0px; overflow: hidden;\'><div style=\'color:#000041;font-family:\'Helvetica Neue\', Helvetica, Arial, sans-serif;line-height:120%;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;\'><div style=\'line-height: 14px; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 12px; color: #000041;\'><p style=\'line-height: 14px; font-size: 12px; TEXT-align: center; margin: 0;\'><strong><span style=\'font-size: 15px; line-height: 18px;\'>WELCOME TO $businessname</span></strong></p></div></div></div></div></div></div></div></div></div><div style=\'background-color:transparent;\'><div class=\'block-grid\' style=\'Margin: 0 auto; min-width: 320px; max-width: 650px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #FFFFFF;;\'><div style=\'border-collapse: collapse;display: table;width: 100%;background-color:#FFFFFF;\'><div class=\'col num12\' style=\'min-width: 320px; max-width: 650px; display: table-cell; vertical-align: top;;\'><div style=\'background-color:#FFFFFF;width:100% !important;\'><div style=\'border-top:0px solid transparent; border-left:8px solid #F1F3F3; border-bottom:0px solid transparent; border-right:8px solid #F1F3F3; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\'><div align=\'center\' class=\'img-container center autowidth fullwidth\' style=\'padding-right: 0px;padding-left: 0px;\'><div style=\'font-size:1px;line-height:10px\'> </div><img align=\'center\' alt=\'Image\' border=\'0\' class=\'center autowidth fullwidth\' src=\'images/divider.png\' style=\'outline: none; TEXT-decoration: none; -ms-interpolation-mode: bicubic; clear: both; border: 0; height: auto; float: none; width: 100%; max-width: 634px; display: block;\' title=\'Image\' width=\'634\'/></div><table border=\'0\' cellpadding=\'0\' cellspacing=\'0\' class=\'divider\' role=\'presentation\' style=\'table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-TEXT-size-adjust: 100%; -webkit-TEXT-size-adjust: 100%;\' valign=\'top\' width=\'100%\'><tbody><tr style=\'vertical-align: top;\' valign=\'top\'><td class=\'divider_inner\' style=\'word-break: break-word; vertical-align: top; min-width: 100%; -ms-TEXT-size-adjust: 100%; -webkit-TEXT-size-adjust: 100%; padding-top: 10px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px; border-collapse: collapse;\' valign=\'top\'><table align=\'center\' border=\'0\' cellpadding=\'0\' cellspacing=\'0\' class=\'divider_content\' role=\'presentation\' style=\'table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; border-top: 1px solid #BBBBBB;\' valign=\'top\' width=\'100%\'><tbody><tr style=\'vertical-align: top;\' valign=\'top\'><td style=\'word-break: break-word; vertical-align: top; -ms-TEXT-size-adjust: 100%; -webkit-TEXT-size-adjust: 100%; border-collapse: collapse;\' valign=\'top\'><span></span></td></tr></tbody></table></td></tr></tbody></table><div style=\'color:#3764D3;font-family:Arial, \'Helvetica Neue\', Helvetica, sans-serif;line-height:120%;padding-top:15px;padding-right:10px;padding-bottom:10px;padding-left:10px;\'><div style=\'font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; font-size: 12px; line-height: 14px; color: #3764D3;\'><p style=\'font-size: 14px; line-height: 19px; TEXT-align: center; margin: 0;\'><span style=\'font-size: 16px;\'><strong>ADMIN PANEL LOGIN CREDENTIALS</strong></span></p></div></div><table border=\'0\' cellpadding=\'0\' cellspacing=\'0\' class=\'divider\' role=\'presentation\' style=\'table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-TEXT-size-adjust: 100%; -webkit-TEXT-size-adjust: 100%;\' valign=\'top\' width=\'100%\'><tbody><tr style=\'vertical-align: top;\' valign=\'top\'><td class=\'divider_inner\' style=\'word-break: break-word; vertical-align: top; min-width: 100%; -ms-TEXT-size-adjust: 100%; -webkit-TEXT-size-adjust: 100%; padding-top: 10px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px; border-collapse: collapse;\' valign=\'top\'><table align=\'center\' border=\'0\' cellpadding=\'0\' cellspacing=\'0\' class=\'divider_content\' role=\'presentation\' style=\'table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; border-top: 1px solid #BBBBBB;\' valign=\'top\' width=\'100%\'><tbody><tr style=\'vertical-align: top;\' valign=\'top\'><td style=\'word-break: break-word; vertical-align: top; -ms-TEXT-size-adjust: 100%; -webkit-TEXT-size-adjust: 100%; border-collapse: collapse;\' valign=\'top\'><span></span></td></tr></tbody></table></td></tr></tbody></table></div></div></div></div></div></div><div style=\'background-color:transparent;\'><div class=\'block-grid\' style=\'Margin: 0 auto; min-width: 320px; max-width: 650px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #FFFFFF;;\'><div style=\'border-collapse: collapse;display: table;width: 100%;background-color:#FFFFFF;\'><div class=\'col num12\' style=\'min-width: 320px; max-width: 650px; display: table-cell; vertical-align: top;;\'><div style=\'background-color:#FFFFFF;width:100% !important;\'><div style=\'border-top:0px solid transparent; border-left:8px solid #F1F3F3; border-bottom:0px solid transparent; border-right:8px solid #F1F3F3; padding-top:35px; padding-bottom:5px; padding-right: 50px; padding-left: 50px;\'><div style=\'color:#555555;font-family:Arial, \'Helvetica Neue\', Helvetica, sans-serif;line-height:150%;padding-top:15px;padding-right:10px;padding-bottom:10px;padding-left:10px;\'><div style=\'line-height: 18px; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; font-size: 12px; color: #555555;\'><p style=\'line-height: 25px; TEXT-align: left; font-size: 12px; margin: 0;\'><span style=\'font-size: 17px; mso-ansi-font-size: 18px;\'>Hey $user_firstname,</span></p><p style=\'line-height: 18px; TEXT-align: left; font-size: 12px; margin: 0;\'> </p><p style=\'line-height: 25px; TEXT-align: left; font-size: 12px; margin: 0;\'><span style=\'font-size: 17px; mso-ansi-font-size: 18px;\'>You have been now setup to access the back end administration panel (CMS) for $businessname. Please click on the activation link below to activate your account.</span></p><p style=\'line-height: 18px; TEXT-align: left; font-size: 12px; margin: 0;\'> </p><p style=\'line-height: 18px; TEXT-align: left; font-size: 12px; margin: 0;\'><strong><span style=\'font-size: 14px; line-height: 21px; color: #800000;\'>$a_link</span></strong></p><p style=\'font-size: 14px; line-height: 21px; TEXT-align: left; margin: 0;\'>(Click on the link to activate. If you cannot click on the link, copy and paste it in browser)</p><p style=\'font-size: 14px; line-height: 21px; TEXT-align: left; margin: 0;\'> </p><p style=\'font-size: 14px; line-height: 25px; TEXT-align: left; margin: 0;\'><span style=\'font-size: 17px; mso-ansi-font-size: 18px;\'>After successful activation, please use the following credentials to access the portal.</span></p><p style=\'font-size: 14px; line-height: 21px; TEXT-align: left; margin: 0;\'> </p><p style=\'font-size: 14px; line-height: 21px; TEXT-align: left; margin: 0;\'><strong><span style=\'font-size: 17px; line-height: 25px; mso-ansi-font-size: 18px;\'>Username: <span style=\'color: #ff0000; font-size: 17px; line-height: 25px; mso-ansi-font-size: 18px;\'>$user_name</span></span></strong></p><p style=\'font-size: 14px; line-height: 21px; TEXT-align: left; margin: 0;\'><strong><span style=\'font-size: 17px; line-height: 25px; mso-ansi-font-size: 18px;\'>Password: <span style=\'color: #ff0000; font-size: 17px; line-height: 25px; mso-ansi-font-size: 18px;\'>$password</span></span></strong><span style=\'font-size: 14px; line-height: 21px; color: #000000;\'><span style=\'line-height: 21px; font-size: 14px;\'> (you can change it after initial login)</span></span></p><p style=\'font-size: 14px; line-height: 21px; TEXT-align: left; margin: 0;\'> </p><p style=\'font-size: 14px; line-height: 21px; TEXT-align: left; margin: 0;\'><em><span style=\'font-size: 12px; line-height: 18px; color: #800000;\'>*** Please keep the above credentials confidential. DO NOT SHARE WITH ANYONE ***</span></em></p><p style=\'font-size: 14px; line-height: 21px; TEXT-align: left; margin: 0;\'> </p><p style=\'font-size: 14px; line-height: 21px; TEXT-align: left; margin: 0;\'><span style=\'font-size: 14px; line-height: 21px;\'>If you have any issues activating your account or signing in, please email info@livewd.ca. </span></p><p style=\'font-size: 14px; line-height: 21px; TEXT-align: left; margin: 0;\'> </p><p style=\'font-size: 14px; line-height: 21px; TEXT-align: left; margin: 0;\'><strong><span style=\'font-size: 14px; line-height: 21px;\'>Cheers</span></strong></p><p style=\'font-size: 14px; line-height: 21px; TEXT-align: left; margin: 0;\'>$businessname</p></div></div></div></div></div></div></div></div><div style=\'background-color:transparent;\'><div class=\'block-grid\' style=\'Margin: 0 auto; min-width: 320px; max-width: 650px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #FFFFFF;;\'><div style=\'border-collapse: collapse;display: table;width: 100%;background-color:#FFFFFF;\'><div class=\'col num12\' style=\'min-width: 320px; max-width: 650px; display: table-cell; vertical-align: top;;\'><div style=\'width:100% !important;\'><div style=\'border-top:0px solid transparent; border-left:8px solid #F1F3F3; border-bottom:0px solid transparent; border-right:8px solid #F1F3F3; padding-top:0px; padding-bottom:0px; padding-right: 0px; padding-left: 0px;\'><table border=\'0\' cellpadding=\'0\' cellspacing=\'0\' class=\'divider\' role=\'presentation\' style=\'table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-TEXT-size-adjust: 100%; -webkit-TEXT-size-adjust: 100%;\' valign=\'top\' width=\'100%\'><tbody><tr style=\'vertical-align: top;\' valign=\'top\'><td class=\'divider_inner\' style=\'word-break: break-word; vertical-align: top; min-width: 100%; -ms-TEXT-size-adjust: 100%; -webkit-TEXT-size-adjust: 100%; padding-top: 10px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px; border-collapse: collapse;\' valign=\'top\'><table align=\'center\' border=\'0\' cellpadding=\'0\' cellspacing=\'0\' class=\'divider_content\' role=\'presentation\' style=\'table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; border-top: 1px solid #BBBBBB;\' valign=\'top\' width=\'100%\'><tbody><tr style=\'vertical-align: top;\' valign=\'top\'><td style=\'word-break: break-word; vertical-align: top; -ms-TEXT-size-adjust: 100%; -webkit-TEXT-size-adjust: 100%; border-collapse: collapse;\' valign=\'top\'><span></span></td></tr></tbody></table></td></tr></tbody></table></div></div></div></div></div></div><div style=\'background-color:transparent;\'><div class=\'block-grid\' style=\'Margin: 0 auto; min-width: 320px; max-width: 650px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #E3FAFF;;\'><div style=\'border-collapse: collapse;display: table;width: 100%;background-color:#E3FAFF;\'><div class=\'col num12\' style=\'min-width: 320px; max-width: 650px; display: table-cell; vertical-align: top;;\'><div style=\'width:100% !important;\'><div style=\'border-top:0px solid transparent; border-left:8px solid #F1F3F3; border-bottom:0px solid transparent; border-right:8px solid #F1F3F3; padding-top:20px; padding-bottom:25px; padding-right: 0px; padding-left: 0px;\'><div style=\'color:#555555;font-family:Arial, \'Helvetica Neue\', Helvetica, sans-serif;line-height:120%;padding-top:10px;padding-right:10px;padding-bottom:10px;padding-left:10px;\'><div style=\'font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; font-size: 12px; line-height: 14px; color: #555555;\'><p style=\'font-size: 14px; line-height: 12px; TEXT-align: center; margin: 0;\'><span style=\'font-size: 10px;\'>POWERED BY</span></p></div></div><div align=\'center\' class=\'img-container center fixedwidth\' style=\'padding-right: 0px;padding-left: 0px;\'><a href=\'https://www.livewd.ca\' target=\'_blank\'> <img align=\'center\' alt=\'Live Web Designing\' border=\'0\' class=\'center fixedwidth\' src=\'https://www.livewd.ca/img/logo.png\' style=\'outline: none; TEXT-decoration: none; -ms-interpolation-mode: bicubic; clear: both; height: auto; float: none; border: none; width: 100%; max-width: 158px; display: block;\' title=\'Live Web Designing\' width=\'158\'/></a></div><div style=\'color:#353535;font-family:Arial, \'Helvetica Neue\', Helvetica, sans-serif;line-height:120%;padding-top:10px;padding-right:10px;padding-bottom:0px;padding-left:10px;\'><div style=\'font-size: 12px; line-height: 14px; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif; color: #353535;\'><p style=\'font-size: 14px; line-height: 16px; TEXT-align: center; margin: 0;\'><strong>Experts in Web &amp; Software Applications Developments</strong></p></div></div><table cellpadding=\'0\' cellspacing=\'0\' class=\'social_icons\' role=\'presentation\' style=\'table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt;\' valign=\'top\' width=\'100%\'><tbody><tr style=\'vertical-align: top;\' valign=\'top\'><td style=\'word-break: break-word; vertical-align: top; padding-top: 5px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px; border-collapse: collapse;\' valign=\'top\'><table activate=\'activate\' align=\'center\' alignment=\'alignment\' cellpadding=\'0\' cellspacing=\'0\' class=\'social_table\' role=\'presentation\' style=\'table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: undefined; mso-table-tspace: 0; mso-table-rspace: 0; mso-table-bspace: 0; mso-table-lspace: 0;\' to=\'to\' valign=\'top\'><tbody><tr align=\'center\' style=\'vertical-align: top; display: inline-block; TEXT-align: center;\' valign=\'top\'><td style=\'word-break: break-word; vertical-align: top; padding-bottom: 5px; padding-right: 5px; padding-left: 5px; border-collapse: collapse;\' valign=\'top\'><a href=\'https://www.facebook.com/livewebdesigning\' target=\'_blank\'><img alt=\'Facebook\' height=\'32\' src=\'images/facebook@2x.png\' style=\'outline: none; TEXT-decoration: none; -ms-interpolation-mode: bicubic; clear: both; height: auto; float: none; border: none; display: block;\' title=\'Facebook\' width=\'32\'/></a></td><td style=\'word-break: break-word; vertical-align: top; padding-bottom: 5px; padding-right: 5px; padding-left: 5px; border-collapse: collapse;\' valign=\'top\'><a href=\'https://twitter.com/livewd\' target=\'_blank\'><img alt=\'Twitter\' height=\'32\' src=\'images/twitter@2x.png\' style=\'outline: none; TEXT-decoration: none; -ms-interpolation-mode: bicubic; clear: both; height: auto; float: none; border: none; display: block;\' title=\'Twitter\' width=\'32\'/></a></td><td style=\'word-break: break-word; vertical-align: top; padding-bottom: 5px; padding-right: 5px; padding-left: 5px; border-collapse: collapse;\' valign=\'top\'><a href=\'https://instagram.com/livewebdesigning\' target=\'_blank\'><img alt=\'Instagram\' height=\'32\' src=\'images/instagram@2x.png\' style=\'outline: none; TEXT-decoration: none; -ms-interpolation-mode: bicubic; clear: both; height: auto; float: none; border: none; display: block;\' title=\'Instagram\' width=\'32\'/></a></td><td style=\'word-break: break-word; vertical-align: top; padding-bottom: 5px; padding-right: 5px; padding-left: 5px; border-collapse: collapse;\' valign=\'top\'><a href=\'mailto:mailto:info@livewd.ca\' target=\'_blank\'><img alt=\'E-Mail\' height=\'32\' src=\'images/mail@2x.png\' style=\'outline: none; TEXT-decoration: none; -ms-interpolation-mode: bicubic; clear: both; height: auto; float: none; border: none; display: block;\' title=\'E-Mail\' width=\'32\'/></a></td><td style=\'word-break: break-word; vertical-align: top; padding-bottom: 5px; padding-right: 5px; padding-left: 5px; border-collapse: collapse;\' valign=\'top\'><a href=\'https://www.livewd.ca\' target=\'_blank\'><img alt=\'Web Site\' height=\'32\' src=\'images/website@2x.png\' style=\'outline: none; TEXT-decoration: none; -ms-interpolation-mode: bicubic; clear: both; height: auto; float: none; border: none; display: block;\' title=\'Web Site\' width=\'32\'/></a></td></tr></tbody></table></td></tr></tbody></table></div></div></div></div></div></div><div style=\'background-color:transparent;\'><div class=\'block-grid\' style=\'Margin: 0 auto; min-width: 320px; max-width: 650px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: #FFFFFF;;\'><div style=\'border-collapse: collapse;display: table;width: 100%;background-color:#FFFFFF;\'><div class=\'col num12\' style=\'min-width: 320px; max-width: 650px; display: table-cell; vertical-align: top;;\'><div style=\'background-color:#FFFFFF;width:100% !important;\'><div style=\'border-top:0px solid transparent; border-left:8px solid #F1F3F3; border-bottom:0px solid transparent; border-right:8px solid #F1F3F3; padding-top:30px; padding-bottom:30px; padding-right: 50px; padding-left: 50px;\'><div style=\'color:#555555;font-family:Arial, \'Helvetica Neue\', Helvetica, sans-serif;line-height:120%;padding-top:5px;padding-right:5px;padding-bottom:5px;padding-left:5px;\'><div style=\'font-size: 12px; line-height: 14px; color: #555555; font-family: Arial, \'Helvetica Neue\', Helvetica, sans-serif;\'><p dir=\'ltr\' style=\'font-size: 12px; line-height: 14px; TEXT-align: justify; margin: 0;\'><span style=\'color: #999999; font-size: 12px; line-height: 14px;\'>This email and any files transmitted with it are confidential and intended solely for the use of the individual or entity to whom they are addressed. If you have received in error, please let us know by email reply and delete it from your system. Please be advised that this e-mail and any attachments are the intellectual property of $businessname. and may not be misused in any way other than its original intention.</span></p></div></div></div></div></div></div></div></div><div style=\'background-color:transparent;\'><div class=\'block-grid\' style=\'Margin: 0 auto; min-width: 320px; max-width: 650px; overflow-wrap: break-word; word-wrap: break-word; word-break: break-word; background-color: transparent;;\'><div style=\'border-collapse: collapse;display: table;width: 100%;background-color:transparent;\'><div class=\'col num12\' style=\'min-width: 320px; max-width: 650px; display: table-cell; vertical-align: top;;\'><div style=\'width:100% !important;\'><div style=\'border-top:0px solid transparent; border-left:0px solid transparent; border-bottom:0px solid transparent; border-right:0px solid transparent; padding-top:5px; padding-bottom:5px; padding-right: 0px; padding-left: 0px;\'><table border=\'0\' cellpadding=\'0\' cellspacing=\'0\' class=\'divider\' role=\'presentation\' style=\'table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; min-width: 100%; -ms-TEXT-size-adjust: 100%; -webkit-TEXT-size-adjust: 100%;\' valign=\'top\' width=\'100%\'><tbody><tr style=\'vertical-align: top;\' valign=\'top\'><td class=\'divider_inner\' style=\'word-break: break-word; vertical-align: top; min-width: 100%; -ms-TEXT-size-adjust: 100%; -webkit-TEXT-size-adjust: 100%; padding-top: 10px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px; border-collapse: collapse;\' valign=\'top\'><table align=\'center\' border=\'0\' cellpadding=\'0\' cellspacing=\'0\' class=\'divider_content\' height=\'15\' role=\'presentation\' style=\'table-layout: fixed; vertical-align: top; border-spacing: 0; border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; border-top: 0px solid transparent; height: 15px;\' valign=\'top\' width=\'100%\'><tbody><tr style=\'vertical-align: top;\' valign=\'top\'><td height=\'15\' style=\'word-break: break-word; vertical-align: top; -ms-TEXT-size-adjust: 100%; -webkit-TEXT-size-adjust: 100%; border-collapse: collapse;\' valign=\'top\'><span></span></td></tr></tbody></table></td></tr></tbody></table></div></div></div></div></div></div></td></tr></tbody></table></body></html>', 1);

DROP TABLE IF EXISTS `zentra_useractivityaudit`;
CREATE TABLE IF NOT EXISTS `zentra_useractivityaudit` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT 0,
  `action` VARCHAR(100) DEFAULT NULL,
  `field_changed` VARCHAR(100) DEFAULT NULL,
  `old_value` TEXT DEFAULT NULL,
  `new_value` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT current_timestamp(),
  `session_id` VARCHAR(225) DEFAULT NULL,
  `activity_TEXT` TEXT DEFAULT NULL,
  `geo_raw` JSON NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

TRUNCATE TABLE `zentra_useractivityaudit`;

DROP TABLE IF EXISTS `zentra_permissions`;
CREATE TABLE IF NOT EXISTS `zentra_permissions` (
  `permission_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permission_key` VARCHAR(150) NOT NULL UNIQUE,   -- e.g., VEHICLE_CREATE
  `permission_name` VARCHAR(255) NOT NULL,         -- e.g., Create Vehicle
  `module` VARCHAR(100) DEFAULT NULL,              -- e.g., Vehicles, Drivers, Billing
  `description` VARCHAR(500) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `is_system_default` TINYINT(1) NOT NULL DEFAULT 1,
  `geo_raw` JSON NULL,
  PRIMARY KEY (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


TRUNCATE TABLE `zentra_permissions`;

INSERT INTO `zentra_permissions` 
(permission_key, permission_name, module, description, is_system_default)
VALUES
-- Tenant Management
('TENANT_MANAGE', 'Manage Tenant Settings', 'Tenant', 'Full control of tenant configuration', 1),
-- User Management
('USER_CREATE', 'Create Users', 'Users', 'Add new users to the tenant', 1),
('USER_EDIT', 'Edit Users', 'Users', 'Modify user details', 1),
('USER_DELETE', 'Delete Users', 'Users', 'Remove users from the tenant', 1),
-- Vehicle Management
('VEHICLE_CREATE', 'Create Vehicles', 'Vehicles', 'Add new vehicles', 1),
('VEHICLE_EDIT', 'Edit Vehicles', 'Vehicles', 'Modify vehicle details', 1),
('VEHICLE_DELETE', 'Delete Vehicles', 'Vehicles', 'Remove vehicles', 1),
-- Driver Management
('DRIVER_CREATE', 'Create Drivers', 'Drivers', 'Add new drivers', 1),
('DRIVER_EDIT', 'Edit Drivers', 'Drivers', 'Modify driver details', 1),
('DRIVER_DELETE', 'Delete Drivers', 'Drivers', 'Remove drivers', 1),
-- Reporting
('REPORTS_VIEW', 'View Reports', 'Reports', 'Access reporting dashboards', 1);

DROP TABLE IF EXISTS `zentra_role_permissions`;
CREATE TABLE IF NOT EXISTS `zentra_role_permissions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  -- Foreign keys
  `role_id` BIGINT UNSIGNED NOT NULL,
  `permission_id` BIGINT UNSIGNED NOT NULL,
  -- Audit fields
  `created_on` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` BIGINT UNSIGNED DEFAULT NULL,
  `updated_on` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` BIGINT UNSIGNED DEFAULT NULL,
  `deleted_on` DATETIME DEFAULT NULL,
  `deleted_by` BIGINT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  -- Prevent duplicate mappings
  UNIQUE KEY `unique_role_permission` (`role_id`, `permission_id`),
  -- Foreign key constraints
  CONSTRAINT `fk_rp_role`
    FOREIGN KEY (`role_id`) REFERENCES `zentra_user_roles`(`role_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_rp_permission`
    FOREIGN KEY (`permission_id`) REFERENCES `zentra_permissions`(`permission_id`)
    ON DELETE CASCADE,
  -- Optional: track who made changes
  CONSTRAINT `fk_rp_created_by`
    FOREIGN KEY (`created_by`) REFERENCES `zentra_users`(`id`)
    ON DELETE SET NULL,
  CONSTRAINT `fk_rp_updated_by`
    FOREIGN KEY (`updated_by`) REFERENCES `zentra_users`(`id`)
    ON DELETE SET NULL,
  CONSTRAINT `fk_rp_deleted_by`
    FOREIGN KEY (`deleted_by`) REFERENCES `zentra_users`(`id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `zentra_audit_logs`;
CREATE TABLE IF NOT EXISTS `zentra_audit_logs` (
  audit_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  -- Who performed the action
  user_id BIGINT UNSIGNED DEFAULT NULL,
  tenant_id BIGINT UNSIGNED DEFAULT NULL,
  -- What entity was affected
  entity_type VARCHAR(100) NOT NULL,     -- e.g., 'ROLE_PERMISSION', 'USER', 'TENANT'
  entity_id BIGINT UNSIGNED DEFAULT NULL,
  -- What action was taken
  action VARCHAR(50) NOT NULL,           -- e.g., 'CREATE', 'UPDATE', 'DELETE', 'ASSIGN', 'REVOKE'
  -- Before/after snapshots
  old_value JSON DEFAULT NULL,
  new_value JSON DEFAULT NULL,
  -- Metadata
  ip_address VARCHAR(100) DEFAULT NULL,
  user_agent VARCHAR(500) DEFAULT NULL,
  -- When it happened
  created_on DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (audit_id),
  -- Optional foreign keys
  CONSTRAINT fk_audit_user
    FOREIGN KEY (user_id) REFERENCES zentra_users(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_audit_tenant
    FOREIGN KEY (tenant_id) REFERENCES zentra_tenants(tenant_id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `zentra_audit_settings`;
CREATE TABLE IF NOT EXISTS `zentra_audit_settings` (
  setting_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  -- Global or tenant-specific
  tenant_id BIGINT UNSIGNED DEFAULT NULL,
  -- Controls
  logging_enabled TINYINT(1) NOT NULL DEFAULT 1,
  retention_days INT UNSIGNED NOT NULL DEFAULT 365,   -- 1 year default
  log_role_changes TINYINT(1) NOT NULL DEFAULT 1,
  log_permission_changes TINYINT(1) NOT NULL DEFAULT 1,
  log_user_changes TINYINT(1) NOT NULL DEFAULT 1,
  log_tenant_changes TINYINT(1) NOT NULL DEFAULT 1,
  updated_on DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  updated_by BIGINT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (setting_id),
  CONSTRAINT fk_audit_settings_tenant
    FOREIGN KEY (tenant_id) REFERENCES zentra_tenants(tenant_id)
    ON DELETE CASCADE,
  CONSTRAINT fk_audit_settings_user
    FOREIGN KEY (updated_by) REFERENCES zentra_users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* 
---------------------------------------------------------------------------
When a permission is assigned to a role:
---------------------------------------------------------------------------
INSERT INTO zentra_audit_logs
(user_id, tenant_id, entity_type, entity_id, action, new_value, ip_address)
VALUES
(?, ?, 'ROLE_PERMISSION', ?, 'ASSIGN', JSON_OBJECT('permission_id', ?), ?);


---------------------------------------------------------------------------
When a permission is revoked:
---------------------------------------------------------------------------
INSERT INTO zentra_audit_logs
(user_id, tenant_id, entity_type, entity_id, action, old_value, ip_address)
VALUES
(?, ?, 'ROLE_PERMISSION', ?, 'REVOKE', JSON_OBJECT('permission_id', ?), ?);

---------------------------------------------------------------------------
When a role is updated:
---------------------------------------------------------------------------
INSERT INTO zentra_audit_logs
(user_id, tenant_id, entity_type, entity_id, action, old_value, new_value)
VALUES
(?, ?, 'ROLE', ?, 'UPDATE', ?, ?);
---------------------------------------------------------------------------
Step 4 — Log Retention (Automatic Cleanup)
---------------------------------------------------------------------------
You can run a daily cron job:

sql
DELETE FROM zentra_audit_logs
WHERE created_on < NOW() - INTERVAL (
    SELECT retention_days FROM zentra_audit_settings WHERE tenant_id IS NULL
) DAY;
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Or tenant‑specific retention:
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
sql
DELETE l
FROM zentra_audit_logs l
JOIN zentra_audit_settings s ON s.tenant_id = l.tenant_id
WHERE l.created_on < NOW() - INTERVAL s.retention_days DAY;
*/

DROP TABLE IF EXISTS `zentra_countries`;
CREATE TABLE IF NOT EXISTS `zentra_countries` (
  country_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  country_code CHAR(2) NOT NULL UNIQUE,      -- ISO 3166-1 alpha-2
  country_name VARCHAR(150) NOT NULL,
  phone_code VARCHAR(10) DEFAULT NULL,       -- +1, +44, etc.
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (country_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
TRUNCATE TABLE `zentra_countries`;

INSERT INTO zentra_countries (country_code, country_name, phone_code) VALUES
('US','United States','+1'),
('CA','Canada','+1'),
('MX','Mexico','+52'),
('GB','United Kingdom','+44'),
('DE','Germany','+49'),
('FR','France','+33'),
('IT','Italy','+39'),
('ES','Spain','+34'),
('NL','Netherlands','+31'),
('SE','Sweden','+46'),
('NO','Norway','+47'),
('DK','Denmark','+45'),
('FI','Finland','+358'),
('AU','Australia','+61'),
('NZ','New Zealand','+64'),
('JP','Japan','+81'),
('CN','China','+86'),
('IN','India','+91'),
('SG','Singapore','+65'),
('HK','Hong Kong','+852'),
('KR','South Korea','+82'),
('BR','Brazil','+55'),
('AR','Argentina','+54'),
('CL','Chile','+56'),
('CO','Colombia','+57'),
('ZA','South Africa','+27'),
('NG','Nigeria','+234'),
('EG','Egypt','+20'),
('SA','Saudi Arabia','+966'),
('AE','United Arab Emirates','+971'),
('TR','Turkey','+90'),
('CH','Switzerland','+41'),
('AT','Austria','+43'),
('BE','Belgium','+32'),
('IE','Ireland','+353'),
('PT','Portugal','+351'),
('PL','Poland','+48'),
('CZ','Czech Republic','+420'),
('HU','Hungary','+36'),
('RO','Romania','+40'),
('GR','Greece','+30'),
('TH','Thailand','+66'),
('MY','Malaysia','+60'),
('PH','Philippines','+63'),
('ID','Indonesia','+62'),
('VN','Vietnam','+84'),
('IL','Israel','+972'),
('KE','Kenya','+254'),
('PK','Pakistan','+92');

DROP TABLE IF EXISTS `zentra_currencies`;
CREATE TABLE IF NOT EXISTS `zentra_currencies` (
  currency_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  currency_code CHAR(3) NOT NULL UNIQUE,     -- ISO 4217 (USD, CAD, EUR)
  currency_name VARCHAR(100) NOT NULL,
  symbol VARCHAR(10) DEFAULT NULL,           -- $, €, £
  decimal_places INT NOT NULL DEFAULT 2,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (currency_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
TRUNCATE TABLE `zentra_currencies`;

INSERT INTO zentra_currencies (currency_code, currency_name, symbol) VALUES
('USD','US Dollar','$'),
('CAD','Canadian Dollar','$'),
('MXN','Mexican Peso','$'),
('EUR','Euro','€'),
('GBP','British Pound','£'),
('JPY','Japanese Yen','¥'),
('CNY','Chinese Yuan','¥'),
('INR','Indian Rupee','₹'),
('AUD','Australian Dollar','$'),
('NZD','New Zealand Dollar','$'),
('CHF','Swiss Franc','CHF'),
('SEK','Swedish Krona','kr'),
('NOK','Norwegian Krone','kr'),
('DKK','Danish Krone','kr'),
('BRL','Brazilian Real','R$'),
('ZAR','South African Rand','R'),
('SGD','Singapore Dollar','$'),
('HKD','Hong Kong Dollar','$'),
('KRW','South Korean Won','₩'),
('TRY','Turkish Lira','₺'),
('AED','UAE Dirham','د.إ'),
('SAR','Saudi Riyal','﷼'),
('PLN','Polish Zloty','zł'),
('CZK','Czech Koruna','Kč'),
('HUF','Hungarian Forint','Ft'),
('ILS','Israeli Shekel','₪');


DROP TABLE IF EXISTS `zentra_timezones`;
CREATE TABLE IF NOT EXISTS `zentra_timezones` (
  timezone_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  timezone_name VARCHAR(100) NOT NULL UNIQUE,   -- e.g., America/Toronto
  utc_offset VARCHAR(10) NOT NULL,              -- e.g., UTC-05:00
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (timezone_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
TRUNCATE TABLE `zentra_timezones`;

INSERT INTO zentra_timezones (timezone_name, utc_offset) VALUES
('America/St_Johns','UTC-02:30'),
('America/Halifax','UTC-04:00'),
('America/Glace_Bay','UTC-04:00'),
('America/Moncton','UTC-04:00'),
('America/Goose_Bay','UTC-04:00'),
('America/Toronto','UTC-05:00'),
('America/Montreal','UTC-05:00'),
('America/Ottawa','UTC-05:00'),
('America/Thunder_Bay','UTC-05:00'),
('America/Nipigon','UTC-05:00'),
('America/Rainy_River','UTC-06:00'),
('America/Winnipeg','UTC-06:00'),
('America/Regina','UTC-06:00'),
('America/Swift_Current','UTC-06:00'),
('America/Edmonton','UTC-07:00'),
('America/Calgary','UTC-07:00'),
('America/Vancouver','UTC-08:00'),
('America/Whitehorse','UTC-07:00'),
('America/Dawson','UTC-07:00'),
('America/Inuvik','UTC-07:00'),
('America/Yellowknife','UTC-07:00'),
('America/Iqaluit','UTC-05:00'),
('America/Rankin_Inlet','UTC-06:00'),
('America/Cambridge_Bay','UTC-07:00'),
('America/New_York','UTC-05:00'),
('America/Detroit','UTC-05:00'),
('America/Kentucky/Louisville','UTC-05:00'),
('America/Chicago','UTC-06:00'),
('America/Indiana/Indianapolis','UTC-05:00'),
('America/Denver','UTC-07:00'),
('America/Phoenix','UTC-07:00'),
('America/Los_Angeles','UTC-08:00'),
('America/Anchorage','UTC-09:00'),
('America/Adak','UTC-10:00'),
('Pacific/Honolulu','UTC-10:00'),
('America/Mexico_City','UTC-06:00'),
('America/Cancun','UTC-05:00'),
('America/Merida','UTC-06:00'),
('America/Monterrey','UTC-06:00'),
('America/Matamoros','UTC-06:00'),
('America/Chihuahua','UTC-07:00'),
('America/Hermosillo','UTC-07:00'),
('America/Tijuana','UTC-08:00'),
('America/Bahia_Banderas','UTC-06:00');

DROP TABLE IF EXISTS `zentra_languages`;
CREATE TABLE IF NOT EXISTS `zentra_languages` (
  language_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  language_code VARCHAR(10) NOT NULL UNIQUE,   -- en-US, fr-CA, etc.
  language_name VARCHAR(100) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (language_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
TRUNCATE TABLE `zentra_languages`;

DROP TABLE IF EXISTS `zentra_system_settings`;
CREATE TABLE IF NOT EXISTS `zentra_system_settings` (
  setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
  setting_value VARCHAR(500) NOT NULL,
  updated_on DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  updated_by BIGINT UNSIGNED DEFAULT NULL,
  `geo_raw` JSON NULL,
  CONSTRAINT fk_system_settings_user
    FOREIGN KEY (updated_by) REFERENCES zentra_users(id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
TRUNCATE TABLE `zentra_system_settings`;

INSERT IGNORE INTO `zentra_system_settings` (`setting_key`) VALUES
('ACTIVE_CDN'),
('APP_NAME'),
('AZURE_STORAGE_CONNECTION_STRING'),
('CDN_AWS_URL'),
('CDN_AZURE_URL'),
('CDN_GCP_URL'),
('DB_CONNECTION'),
('DB_HOST'),
('DB_NAME'),
('DB_PASS'),
('DB_PORT'),
('DB_USER'),
('DEFAULT_COUNTRY'),
('GOOGLE_CALENDAR_CLIENT_ID'),
('GOOGLE_CALENDAR_CLIENT_SECRET'),
('GOOGLE_CALENDAR_REDIRECT_URI'),
('GOOGLE_MAPS_API_KEY'),
('GOOGLE_RECAPTCHA_SECRET_KEY'),
('GOOGLE_RECAPTCHA_SITE_KEY'),
('MAILJET_API_KEY'),
('MAILJET_FROM_EMAIL'),
('MAILJET_FROM_NAME'),
('MAILJET_SECRET_KEY'),
('MYSQL_ATTR_SSL_CA'),
('PASSWORD_MAX_LENGTH'),
('PASSWORD_MIN_LENGTH'),
('REMEMBER_ME_EXPIRY_DAYS'),
('SCM_DO_BUILD_DURING_DEPLOYMENT'),
('SENDGRID_API_KEY'),
('SENDGRID_SENDER_EMAIL'),
('SENDGRID_SENDER_NAME'),
('SENDGRID_SMTP_EMAIL_FROM'),
('SENDGRID_SMTP_EMAIL_NAME'),
('SENDGRID_SMTP_HOST'),
('SENDGRID_SMTP_PASS'),
('SENDGRID_SMTP_PORT'),
('SENDGRID_SMTP_USER'),
('SMTP2GO_API_KEY'),
('TOKEN_EXPIRY_MINUTES'),
('TWILIO_ACCOUNT_SID'),
('TWILIO_AUTH_TOKEN'),
('TWILIO_SMS_FROM'),
('TWILIO_WHATSAPP_FROM'),
('USE_CDN'),
('WEBSITE_SKIP_RUNNING_KUDUAGENT');


DROP TABLE IF EXISTS `zentra_password_resets`;
CREATE TABLE IF NOT EXISTS `zentra_password_resets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `reset_token` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT current_timestamp(),
  `status` VARCHAR(20) DEFAULT 'active',
  `geo_raw` JSON NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

TRUNCATE TABLE `zentra_password_resets`;

DROP TABLE IF EXISTS `zentra_config`;
CREATE TABLE IF NOT EXISTS `zentra_config` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id_counter` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `tenant_last_updated` DATETIME DEFAULT CURRENT_TIMESTAMP(),
  `geo_raw` JSON NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

TRUNCATE TABLE `zentra_config`;

INSERT INTO `zentra_config` (`id`, `tenant_id_counter`) VALUES (1, 90000) ON DUPLICATE KEY UPDATE `tenant_id_counter` = `tenant_id_counter`;

DROP TABLE IF EXISTS `zentra_schedule_seasons`;
CREATE TABLE `zentra_schedule_seasons` (
    season_id INT AUTO_INCREMENT PRIMARY KEY,
    season_name VARCHAR(50) NOT NULL,   -- Summer, Winter, Spring, etc.
    start_date DATE NULL,               -- optional (if seasons are date-based)
    end_date DATE NULL,                 -- optional
    is_active TINYINT(1) DEFAULT 1,     -- enable/disable season
    created_on DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_on DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
TRUNCATE TABLE `zentra_schedule_seasons`;

DROP TABLE IF EXISTS `zentra_business_schedules`;
CREATE TABLE `zentra_business_schedules` (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    season_id INT NOT NULL,
    day_of_week ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
    open_time TIME NOT NULL,
    close_time TIME NOT NULL,
    created_on DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_on DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (season_id) REFERENCES zentra_schedule_seasons(season_id)
);
TRUNCATE TABLE `zentra_business_schedules`;

DROP TABLE IF EXISTS `zentra_business_special_schedules`;
CREATE TABLE `zentra_business_special_schedules` (
    special_id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_date DATE NOT NULL,
    open_time TIME NOT NULL,
    close_time TIME NOT NULL,
    description VARCHAR(255) NULL,   -- e.g., "Diwali Special Hours"
    created_on DATETIME DEFAULT CURRENT_TIMESTAMP
);
TRUNCATE TABLE `zentra_business_special_schedules`;