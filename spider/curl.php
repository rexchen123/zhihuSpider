<?php
/**
 * @Author: huhuaquan
 * @Date:   2015-08-10 18:08:43
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2016-05-27 18:21:16
 */
require_once './function.php';
class Curl {

	private static $cookie_arr = array(
		'__utma' => '51854390.1467381360.1466562190.1466562190.1466562190.1',
		'__utmb' => '51854390.6.10.1466562190',
		'__utmc' => '51854390',
		'__utmv' => '51854390.100--|2=registration_date=20160131=1^3=entry_date=20160131=1',
		'__utmz' => '51854390.1466562190.1.1.utmcsr=baidu|utmccn=(organic)|utmcmd=organic',
		'_xsrf' => 'adcfcf915f4506927b88d87646016dc2',
		'_za' => 'e6745b34-4bee-4210-8cc3-f4b1399ef4f7',
		'_zap' => 'd099074d-b768-4d89-8dc4-1ed604fbae81',
		'cap_id' => '"ZTE5YWUzYjVkZTAwNDg3NWE1ODk0NGM0ZmUzN2RkYjQ=|1466562187|8f2d5199e713c8ef61ae919daca66ec928e38008"',
		'd_c0' => '"AAAAZXCOHQqPTsM14o_2_2dqO3sGcdPPYuo=|1466562188"',
		'l_cap_id' => '"ZGM3ZmMyOTlmZjYxNGRkMWI5MTQzMzJlZWJlOTNjYzk=|1466562187|25b1614b998e3c1353c0b85afd80cb75da99d67e"',
		'l_n_c' => '1',
		'login' => '"ZDdhOTMyMGY3MTFlNDU3M2FjMmM2M2FhYjFjZWJjOGY=|1466562209|060b806940a5709c91555b7eedd41fc1de44ee95"',
		'q_c1' => '7a87de9d60334cd5a006bddb8d013034|1466562187000|1466562187000',
		'n_c' => '1',
		'a_t' => '"2.0ABAKkS6fZQkXAAAAooORVwAQCpEun2UJAAAAZXCOHQoXAAAAYQJVTaKDkVcAs0iW6XFoXbvBVm-A5efq2zL9Oj-75DcDI9Nl5zhIf65qA6buTKfbpA=="',
		'z_c0' => 'Mi4wQUJBS2tTNmZaUWtBQUFCbGNJNGRDaGNBQUFCaEFsVk5vb09SVndDelNKYnBjV2hkdThGV2I0RGw1LXJiTXYwNlB3|1466562210|84fbea4af1cc1092828e592d0529162b7e7e9949',
	);

	private static function genCookie() {
		$cookie = '';
		foreach (self::$cookie_arr as $key => $value) {
			if($key != 'z_c0')
				$cookie .= $key . '=' . $value . ';';
			else
				$cookie .= $key . '=' . $value;
		}

		return $cookie;
	}

	/**
	 * [request 执行一次curl请求]
	 * @param  [string] $method     [请求方法]
	 * @param  [string] $url        [请求的URL]
	 * @param  array  $fields     [执行POST请求时的数据]
	 * @return [stirng]             [请求结果]
	 */
	public static function request($method, $url, $fields = array())
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_COOKIE, self::genCookie());
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.130 Safari/537.36');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		if ($method === 'POST')
		{
			curl_setopt($ch, CURLOPT_POST, true );
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		}
		$result = curl_exec($ch);
		return $result;
	}

	/**
	 * [getMultiUser 多进程获取用户数据]
	 * @param  [type] $user_list [description]
	 * @return [type]            [description]
	 */
	public static function getMultiUser($user_list)
	{
		$ch_arr = array();
		$text = array();
		$len = count($user_list);
		$max_size = ($len > 5) ? 5 : $len;
		$requestMap = array();

		$mh = curl_multi_init();
		for ($i = 0; $i < $max_size; $i++)
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_URL, 'http://www.zhihu.com/people/' . $user_list[$i] . '/about');
			curl_setopt($ch, CURLOPT_COOKIE, self::genCookie());
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.130 Safari/537.36');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			$requestMap[$i] = $ch;
			curl_multi_add_handle($mh, $ch);
		}

		$user_arr = array();
		do {
			while (($cme = curl_multi_exec($mh, $active)) == CURLM_CALL_MULTI_PERFORM);

			if ($cme != CURLM_OK) {break;}

			while ($done = curl_multi_info_read($mh))
			{
				$info = curl_getinfo($done['handle']);
				$tmp_result = curl_multi_getcontent($done['handle']);
				$error = curl_error($done['handle']);

				$user_arr[] = getUserInfo($tmp_result);

				//保证同时有$max_size个请求在处理
				if ($i < sizeof($user_list) && isset($user_list[$i]) && $i < count($user_list))
                {
                	$ch = curl_init();
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_URL, 'http://www.zhihu.com/people/' . $user_list[$i] . '/about');
					curl_setopt($ch, CURLOPT_COOKIE, self::genCookie());
					curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.130 Safari/537.36');
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
					$requestMap[$i] = $ch;
					curl_multi_add_handle($mh, $ch);

                    $i++;
                }

                curl_multi_remove_handle($mh, $done['handle']);
			}

			if ($active)
                curl_multi_select($mh, 10);
		} while ($active);

		curl_multi_close($mh);
		return $user_arr;
	}

}