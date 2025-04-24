ALTER TABLE `users` 
CHANGE COLUMN `role` `role` varchar(20) DEFAULT 'learner';

ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `phone` varchar(20) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `bio` text DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `avatar` varchar(255) DEFAULT NULL;

UPDATE `users` SET `role` = 'learner' WHERE `role` = 'user'; 