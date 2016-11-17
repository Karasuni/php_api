# PHP API

Basic REST API using Slim framework. 

~~~
CREATE TABLE IF NOT EXISTS `students` (
  `student_id` int(10) NOT NULL auto_increment,
  `score` int(10) default '0',
  `first_name` varchar(50) default NULL,
  `last_name` varchar(50) default NULL,
  PRIMARY KEY  (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
~~~