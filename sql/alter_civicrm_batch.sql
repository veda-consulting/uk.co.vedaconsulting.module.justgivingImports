ALTER TABLE `civicrm_batch` 
ADD COLUMN  `payment_instrument_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Payment Method';
ALTER TABLE `civicrm_batch` 
ADD COLUMN  `expected_posting_date` datetime DEFAULT NULL COMMENT 'Expected Posting Date';
ALTER TABLE `civicrm_batch` 
ADD COLUMN  `campaign_id` int(10) unsigned DEFAULT NULL COMMENT 'Campaign Id';
ALTER TABLE `civicrm_batch` 
ADD COLUMN  `banking_date` date DEFAULT NULL COMMENT 'Banking Date';
ALTER TABLE `civicrm_batch` 
ADD COLUMN  `banking_account` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Banking Account';
ALTER TABLE `civicrm_batch` 
ADD COLUMN  `contribution_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Contribution Type Id';
ALTER TABLE `civicrm_batch` 
ADD COLUMN  `exclude_from_posting` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Exclude from posting';
ALTER TABLE `civicrm_batch` 
ADD COLUMN  `post_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Contact ID who do post';
ALTER TABLE `civicrm_batch` 
ADD COLUMN  `entity_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL;
ALTER TABLE `civicrm_batch` 
ADD COLUMN  `import_id` int(10) DEFAULT NULL;

ALTER TABLE `log_civicrm_batch` 
ADD COLUMN  `payment_instrument_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Payment Method';
ALTER TABLE `log_civicrm_batch` 
ADD COLUMN  `expected_posting_date` datetime DEFAULT NULL COMMENT 'Expected Posting Date';
ALTER TABLE `log_civicrm_batch` 
ADD COLUMN  `campaign_id` int(10) unsigned DEFAULT NULL COMMENT 'Campaign Id';
ALTER TABLE `log_civicrm_batch` 
ADD COLUMN  `banking_date` date DEFAULT NULL COMMENT 'Banking Date';
ALTER TABLE `log_civicrm_batch` 
ADD COLUMN  `banking_account` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Banking Account';
ALTER TABLE `log_civicrm_batch` 
ADD COLUMN  `contribution_type_id` int(10) unsigned DEFAULT NULL COMMENT 'Contribution Type Id';
ALTER TABLE `log_civicrm_batch` 
ADD COLUMN  `exclude_from_posting` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Exclude from posting';
ALTER TABLE `log_civicrm_batch` 
ADD COLUMN  `post_id` int(10) unsigned NOT NULL DEFAULT 0 COMMENT 'Contact ID who do post';
ALTER TABLE `log_civicrm_batch` 
ADD COLUMN  `entity_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL;
ALTER TABLE `log_civicrm_batch` 
ADD COLUMN  `import_id` int(10) DEFAULT NULL;

