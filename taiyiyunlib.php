<?php
define('TY_API_SERVER', 'http://api.taiyiyun.com/copyright/');	//this is Taiyi Api,to post data and check status
class taiyiyunlib{
	/**
	 * to Sign 
	 * @param  [type] $para [description]
	 * @return [type]       [description]
	 */
	function getSign($para){
		while (list ($key, $val) = each ($para)) {    
        	$arg.=$key."=".$val;
        }
      	return strtoupper(md5($arg));
	}

	/**
	 * get servers token , use api 
	 * @param  [type] $post [description]
	 * @return [type]       [description]
	 */
	function getToken($post ){
		$post['sign'] = $this->getSign($post);
		unset($post['key']);
		$url = TY_API_SERVER.'?taiyiyun.api=api.common.token';
		$result = $this->send_post($url,$post);
		return json_decode($result,true);
	}

	/**
	 * send data to server
	 * @param  [type] $url       [description]
	 * @param  [type] $post_data [description]
	 * @return [type]            [description]
	 */
	public function send_post($url, $post_data) {
	    $postdata = http_build_query($post_data);
	    $options = array(
	      'http' => array(
	        'method' => 'POST',
	        'header' => 'Content-type:application/x-www-form-urlencoded',
	        'content' => $postdata,
	        'timeout' => 30 // expire time
	      )
	    );
	    $context = stream_context_create($options);
	    $result = file_get_contents($url, false, $context);
	    return $result;
    }

    /**
     * char function
     * @param  [type] $content [description]
     * @param  [type] $url     [description]
     * @param  [type] $length  [description]
     * @param  string $num     [description]
     * @return [type]          [description]
     */
    function ty_status($content, $url, $length, $num = '') {
    	$content = strip_tags($content);
		$temp_length = (mb_strlen($content, 'utf-8')) + (mb_strlen($url, 'utf-8'));
		if ($num) {
			$temp_length = (wp_strlen($content)) + (wp_strlen($url));
		} 
		if ($url) {
			$length = $length - 4; // ' - '
			$url = ' ' . $url;
		} 
		if ($temp_length > $length) {
			$chars = $length - 3 - mb_strlen($url, 'utf-8'); // '...'
			if ($num) {
				$chars = $length - wp_strlen($url);
				$str = mb_substr($content, 0, $chars, 'utf-8');
				preg_match_all("/([\x{0000}-\x{00FF}]){1}/u", $str, $half_width); // 半角字符
				$chars = $chars + count($half_width[0]) / 2;
			} 
			$content = mb_substr($content, 0, $chars, 'utf-8');
			$content = $content . "...";
		} 
		$status = $content . $url;
		return trim($status);
	} 
}