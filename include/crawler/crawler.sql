DROP TABLE IF EXISTS phpcrawler_links;
CREATE TABLE phpcrawler_links (
  id           int(11) NOT NULL auto_increment,
  site_id      int(11) NOT NULL default 0,
  depth        int(11) NOT NULL default 0,
  url          text NOT NULL,
  url_title    text,
  url_md5      varchar(255) NOT NULL,

  content      text NOT NULL,
  content_md5  varchar(255) NOT NULL,

  last_crawled datetime,
  crawl_now    int(11) NOT NULL default 1,
  
  PRIMARY KEY (id),
  KEY idx_site_id(site_id),
  KEY idx_url (url(255)),
  KEY idx_content_md5(content_md5),
  FULLTEXT ft_content(content),
  KEY idx_last_crawled(last_crawled)
);

DROP TABLE IF EXISTS words;
CREATE TABLE words (
  id     int(11) NOT NULL,
  word   varchar(255) NOT NULL
);
