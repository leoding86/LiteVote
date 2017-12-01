CREATE TABLE `sandbox_litevote`.`ls_attachment` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `unid` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `path` TEXT NOT NULL,
  `create_time` INT(11) NOT NULL,
  PRIMARY KEY (`id`));