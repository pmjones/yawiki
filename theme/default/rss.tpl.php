<?php

/**
* 
* RSS template for a single area (1 or more pages).
* 
*/

// output the header this way so as not to let the XML
// tags interfere with PHP
header('Content-Type: text/xml; charset=iso-8859-1');
echo '<?xml version="1.0" encoding="iso-8859-1" ?>' . "\n";

?>
<rss version="2.0">
	<channel>
		<title><?php $this->_($this->info['title']) ?></title>
		<link><?php $this->_($this->link) ?></link>
		<description><?php $this->_($this->info['tagline']) ?></description>
		<pubDate><?php $this->_(date('r')) ?></pubDate>
		<webMaster><?php $this->_($this->info['email']) ?></webMaster>
<?php foreach ($this->list as $key => $val): ?>
		<item>
			<category><?php $this->_($val['area']) ?></category>
			<title><?php $this->_($val['page']) ?></title>
			<pubDate><?php $this->_(date('r', strtotime($val['dt']))) ?></pubDate>
			<description><?php $this->_(
				$val['title'] . ': ' .
				$val['note']  .
				' (' . $val['username'] . ')'
				) ?> update to page: <?php $this->_($val['area'].':'.$val['page']) ?>
			</description>
			<link><?php $this->_(
				$this->link . 'index.php' . 
				'?area=' . $val['area'] .
				'&page=' . $val['page'] . 
				'&view=diff&to=' . $val['dt'] . 
				'&from=' . $val['from']
			) ?></link>
		</item>
<?php endforeach; ?>
	</channel>
</rss>