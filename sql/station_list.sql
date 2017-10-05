CREATE TABLE IF NOT EXISTS `station_list` (
`id` int(11) AUTO_INCREMENT PRIMARY KEY,
`name` varchar(255) NOT NULL,
`active_flag` tinyint(4) NOT NULL,
`update_time` timestamp NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
`create_time` timestamp NOT NULL default '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
