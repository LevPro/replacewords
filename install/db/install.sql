CREATE TABLE `levpro_replacewords` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `URL` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `FROM` text COLLATE utf8_unicode_ci NOT NULL,
  `TO` text COLLATE utf8_unicode_ci NOT NULL,
  `QUANTITY` bigint(20) DEFAULT NULL,
  `GET_PARAMS` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
