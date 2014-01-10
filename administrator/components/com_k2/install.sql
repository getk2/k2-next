-- --------------------------------------------------------

--
-- Table structure for table `#__k2_attachments`
--

CREATE TABLE IF NOT EXISTS `#__k2_attachments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemId` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `downloads` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `itemId` (`itemId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__k2_categories`
--

CREATE TABLE IF NOT EXISTS `#__k2_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `lft` int(11) NOT NULL DEFAULT '0',
  `rgt` int(11) NOT NULL DEFAULT '0',
  `path` varchar(255) NOT NULL,
  `level` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL DEFAULT '',
  `state` tinyint(1) NOT NULL,
  `access` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `image` text NOT NULL,
  `extra_fields` longtext NOT NULL,
  `template` varchar(255) NOT NULL,
  `inheritance` int(10) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_alias` varchar(255) NOT NULL,
  `modified` datetime NOT NULL,
  `modified_by` int(10) unsigned NOT NULL,
  `checked_out` int(10) unsigned NOT NULL,
  `checked_out_time` datetime NOT NULL,
  `metadata` text NOT NULL,
  `plugins` text NOT NULL,
  `params` text NOT NULL,
  `language` varchar(7) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alias` (`alias`),
  KEY `idx_left_right` (`lft`,`rgt`),
  KEY `parent_id` (`parent_id`),
  KEY `level` (`level`),
  KEY `published` (`state`),
  KEY `access` (`access`),
  KEY `created_by` (`created_by`),
  KEY `modified_by` (`modified_by`),
  KEY `checked_out` (`checked_out`),
  KEY `language` (`language`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


INSERT INTO `#__k2_categories` (`id`, `asset_id`, `parent_id`, `lft`, `rgt`, `path`, `level`, `title`, `alias`, `state`, `access`, `description`, `image`, `extra_fields`, `template`, `inheritance`, `created`, `created_by`, `created_by_alias`, `modified`, `modified_by`, `checked_out`, `checked_out_time`, `metadata`, `plugins`, `params`, `language`) VALUES
(1, 0, 0, 0, 4, '', 0, 'ROOT', 'root', 0, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, '', '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00', '', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `#__k2_comments`
--

CREATE TABLE IF NOT EXISTS `#__k2_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `email` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `hostname` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `state` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `itemId` (`itemId`),
  KEY `userId` (`userId`),
  KEY `state` (`state`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__k2_extra_fields`
--

CREATE TABLE IF NOT EXISTS `#__k2_extra_fields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `required` tinyint(1) NOT NULL,
  `type` varchar(255) NOT NULL,
  `group` int(10) unsigned NOT NULL,
  `state` tinyint(1) NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alias` (`alias`),
  KEY `type` (`type`),
  KEY `group` (`group`),
  KEY `published` (`state`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__k2_extra_fields_groups`
--

CREATE TABLE IF NOT EXISTS `#__k2_extra_fields_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `scope` varchar(255) NOT NULL,
  `assignments` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `scope` (`scope`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__k2_items`
--

CREATE TABLE IF NOT EXISTS `#__k2_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `featured` tinyint(1) NOT NULL,
  `access` tinyint(3) unsigned NOT NULL,
  `catid` int(10) unsigned NOT NULL,
  `introtext` mediumtext NOT NULL,
  `fulltext` mediumtext NOT NULL,
  `image` text NOT NULL,
  `media` text NOT NULL,
  `tags` text NOT NULL,
  `attachments` text NOT NULL,
  `galleries` text NOT NULL,
  `extra_fields` longtext NOT NULL,
  `ordering` int(11) NOT NULL,
  `featured_ordering` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_by_alias` varchar(255) NOT NULL,
  `modified` datetime NOT NULL,
  `modified_by` int(10) unsigned NOT NULL,
  `checked_out` int(10) unsigned NOT NULL,
  `checked_out_time` datetime NOT NULL,
  `publish_up` datetime NOT NULL,
  `publish_down` datetime NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `metadata` text NOT NULL,
  `plugins` text NOT NULL,
  `params` text NOT NULL,
  `language` char(7) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alias` (`alias`),
  KEY `published` (`state`),
  KEY `catid` (`catid`),
  KEY `language` (`language`),
  KEY `access` (`access`),
  KEY `created_by` (`created_by`),
  KEY `modified_by` (`modified_by`),
  KEY `checked_out` (`checked_out`),
  KEY `ordering` (`ordering`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__k2_items_stats`
--

CREATE TABLE IF NOT EXISTS `#__k2_items_stats` (
  `itemId` int(10) unsigned NOT NULL,
  `hits` bigint(10) unsigned NOT NULL,
  `comments` bigint(10) unsigned NOT NULL,
  PRIMARY KEY (`itemId`),
  KEY `hits` (`hits`),
  KEY `comments` (`comments`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__k2_tags`
--

CREATE TABLE IF NOT EXISTS `#__k2_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `extra_fields` longtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alias` (`alias`),
  KEY `published` (`state`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__k2_tags_xref`
--

CREATE TABLE IF NOT EXISTS `#__k2_tags_xref` (
  `tagId` int(10) unsigned NOT NULL,
  `itemId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tagId`,`itemId`),
  KEY `tagId` (`tagId`),
  KEY `itemId` (`itemId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__k2_users`
--

CREATE TABLE IF NOT EXISTS `#__k2_users` (
  `id` int(10) unsigned NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `site` varchar(255) NOT NULL,
  `gender` varchar(255) NOT NULL,
  `notes` text NOT NULL,
  `extra_fields` longtext NOT NULL,
  `items` bigint(20) unsigned NOT NULL,
  `comments` bigint(20) unsigned NOT NULL,
  `ip` varchar(255) NOT NULL,
  `hostname` varchar(255) NOT NULL,
  `plugins` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `items` (`items`),
  KEY `comments` (`comments`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__k2_users_stats`
--

CREATE TABLE IF NOT EXISTS `#__k2_users_stats` (
  `userId` int(10) unsigned NOT NULL,
  `items` bigint(20) unsigned NOT NULL,
  `comments` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`userId`),
  KEY `items` (`items`),
  KEY `comments` (`comments`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
