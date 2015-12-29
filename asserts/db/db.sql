create table `users` (
	`id` bigint(20) unsigned not null auto_increment,
	`username` varchar(120) not null,
	`password` char(32) not null,
	`email` char(32) not null,
	`regip` char(15) not null,
	`regtm` int(10) unsigned not null default 0,
	`salt` char(6) not null default '',
	`userfrom` int(4) not null default 0,
	PRIMARY KEY (`id`),
	UNIQUE KEY `username` (`username`),
	KEY `email` (`email`),
	KEY `userfrom` (`userfrom`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table `article` (
	`id` int(10) not null auto_increment,
	`uid` int(10) not null default 0,
	`title` varchar(255) not null,
	`content` text,
	`comments` int(10) default 0,
	`views` int(10) default 0,
	`addtm` int(10) not null default 0,
	`hasAttach` tinyint(1) not null default 0,
	`lock` tinyint(1) not null default 0,
	`votes` int(10) default 0,
	`categoryId` int(10) not null default 0,
	`is_recommend` tinyint(1) default 0,
	PRIMARY KEY (`id`),
	KEY `uid` (`uid`),
	KEY `categoryId` (`categoryId`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `category` (
	`id` int(11) unsigned not null auto_increment,
	`title` varchar(128) default null,
	`icon` varchar(255) default null,
	`parentId` int(11) default 0,
	`sort` smallint(6) default 0,
	`url_token` varchar(32) default null,
	PRIMARY KEY (`id`),
	KEY `sort` (`sort`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

