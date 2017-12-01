CREATE TABLE `sandbox_litevote`.`ls_vote_item` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `thumb` TEXT NULL DEFAULT NULL,
  `votes` INT(11) NOT NULL DEFAULT 0,
  `summary` TEXT NULL DEFAULT NULL,
  `content` TEXT NULL DEFAULT NULL,
  `content_type` TINYINT(1) NOT NULL,
  `redirect_url` TEXT NULL DEFAULT NULL,
  `create_time` INT(11) UNSIGNED NOT NULL,
  `update_time` INT(11) UNSIGNED NOT NULL,
  `delete_flg` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`));