<?php

class UrlHelper{

	private $urls;
	private $content;
	private $file;

	function __construct()
	{		
		$this->file = 'urls.csv';
	}

	private function parseFile()
	{
		$row = 1;
		if (($handle = fopen($this->file, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {				
				$num = count($data);				
				$row++;				
				if($row > 2) {
					for ($c=0; $c < $num; $c++) {
						$this->content[] = $data[$c];
					}
				}
			}
			fclose($handle);
		}
	}

	private function isUrl($val) {
		return filter_var($val, FILTER_VALIDATE_URL);
	}

	private function isDuplicate($val)
	{
		return (! empty($this->urls)) ? in_array($val, $this->urls) : false;
	}

	private function checkContent()
	{
		foreach($this->content as $c){
			if($this->isUrl($c) && ! $this->isDuplicate($c)) {
				$this->urls[] = ["url" => $c];
			}
		}
	}

	private function runCurl($url)
	{
		$ch = curl_init($url["url"]);
		curl_setopt($ch, CURLOPT_HEADER, true);    // we want headers
		curl_setopt($ch, CURLOPT_NOBODY, true);    // we don't need body
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_TIMEOUT,10);
		$output = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return $httpcode;
	}

	private function checkUrlStatus()
	{
		foreach($this->urls as $k => $url){
			$this->urls[$k]["status"] = $this->runCurl($url);
		}
	}

	private function output()
	{
		$fp = fopen('output-' . date('Y-m-d-H-i-s') . '.csv', 'w');
		fputcsv($fp, array('url', 'status'));		
		foreach($this->urls as $url) {		
			fputcsv($fp, $url);	
		}	
		fclose($fp);
	}

	public function init()
	{
		$this->parseFile();
		$this->checkContent();
		$this->checkUrlStatus();
		$this->output();		
	}

}

$helper = new UrlHelper;
$helper->init();