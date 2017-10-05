CREATE TABLE IF NOT EXISTS `program_elements` (
`id` int(11) AUTO_INCREMENT PRIMARY KEY,
`station_id` int(11) NOT NULL,
`title` varchar(255) NOT NULL,
`infomation` varchar(1023) NOT NULL,
`program_date` date NOT NULL,
`program_time` time NOT NULL,
`update_time` timestamp NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
`create_time` timestamp NOT NULL default '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
