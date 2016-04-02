CREATE TABLE `track` (
  `id` int(11) NOT NULL,
  `room` char(10) NOT NULL,
  `count` int(6) NOT NULL,
  `grow` int(6) NOT NULL,
  `stay` int(6) NOT NULL,
  `abandon` int(6) NOT NULL,
  `novote` int(6) NOT NULL,
  `formation` int(11) NOT NULL,
  `reap` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `track`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room` (`room`),
  ADD KEY `count` (`count`);

ALTER TABLE `track`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
