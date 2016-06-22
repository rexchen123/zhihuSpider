<?php
/**
 * @Author: huhuaquan
 * @Date:   2015-08-21 15:25:27
 * @Last Modified by:   huhuaquan
 * @Last Modified time: 2016-04-19 18:31:16
 */
class User {

	const TABLE_NAME = 'user';

	const FOLLOW_TABLE_NAME = 'user_follow';

	/**
	 * [existed 判断用户是否已存在]
	 * @param  [type] $params [description]
	 * @param  [type] $table  [description]
	 * @return [type]         [description]
	 */
	public static function existed($params, $table)
	{
		$collection = new Mongo_Collection($table);
		$result = $collection->count($params);
		return $result;
	}

	/**
	 * [add 新增一个用户]
	 * @param [type] $params [description]
	 */
	public static function add($params)
	{
		$collection = new Mongo_Collection(self::TABLE_NAME);
		$existed_params = [
			'u_id' => $params['u_id']
		];
		if (self::existed($existed_params, self::TABLE_NAME))
		{
			return;
		}
		$result = $collection->insert($params);
		return $result;
	}

	/**
	 * [addMulti 增加多个用户]
	 * @param [type] $data [description]
	 */
	public static function addMulti($data)
	{
		$collection = new Mongo_Collection(self::TABLE_NAME);
		$result = $collection->insertAll($data);
		return $result;
	}

	/**
	 * [info 返回用户信息]
	 * @param  [type] $u_id [description]
	 * @return [type]       [description]
	 */
	public static function info($u_id)
	{
		$collection = new Mongo_Collection(self::TABLE_NAME);
		$existed_params = [
			'u_id' => $u_id
		];

		$result = $collection->findOne($existed_params);
		if (empty($result))
		{
			echo "--------user $u_id not existed--------\n";
		}
		return $result;
	}

	/**
	 * [addFollowList 增加用户关系]
	 * @param [type] $user_follow_list [description]
	 */
	public static function addFollowList($user_follow_list)
	{
		echo "--------start adding user follow relation--------\n";
		$collection = new Mongo_Collection(self::FOLLOW_TABLE_NAME);
		$result = $collection->insertAll($user_follow_list);
		echo "--------add user follow relation done--------\n";
		return $result;
	}

	/**
	 * [getFollowUserList 返回用户关系列表]
	 * @param  [type] $u_id [description]
	 * @param  [type] $page [description]
	 * @return [type]       [description]
	 */
	public static function getFollowUserList($u_id, $page)
	{
		$collection = new Mongo_Collection(self::FOLLOW_TABLE_NAME);
		$params = [
			'u_id' => $u_id,
		];
		$result = $collection->find($params, $page);
		return $result;
	}

	/**
	 * [getFolloweeCount 返回用户关注人数量]
	 * @param  [type] $u_id [description]
	 * @return [type]       [description]
	 */
	public static function getFolloweeCount($u_id)
	{
		$collection = new Mongo_Collection(self::FOLLOW_TABLE_NAME);
		$params = [
			'u_id' => $u_id
		];
		$result = $collection->count($params);
		return $result;
	}

	/**
	 * [getFolloweeCount 返回用户关注者数量]
	 * @param  [type] $u_id [description]
	 * @return [type]       [description]
	 */
	public static function getFollowerCount($u_id)
	{
		$collection = new Mongo_Collection(self::FOLLOW_TABLE_NAME);
		$params = [
				'u_follow_id' => $u_id
		];
		$result = $collection->count($params);
		return $result;
	}

	/**
	 * [totalCount 返回用户总数量]
	 * @return [type] [description]
	 */
	public static function totalCount()
	{
		$collection = new Mongo_Collection(self::TABLE_NAME);
		$result = $collection->count(array());
		return $result;
	}

	public static function update($user_data, $u_id)
	{
		$collection = new Mongo_Collection(self::TABLE_NAME);
		$where = [
			'u_id' => $u_id
		];
		$result = $collection->update($where, $user_data);
		return $result;
	}

	/**
	 * [addressCountList 根据地区统计]
	 * @return [type] [description]
	 */
	public static function addressCountList()
	{
		$tmp_pdo = PDO_MySQL::getInstance();
		$conditions = array(
			'fields' => 'address, count(*) as address_count',
			'sort' => array('address_count' => 0),
			'group_by' => 'address',
			'limit' => 11
		);
		$result = $tmp_pdo->getAll(self::TABLE_NAME, $conditions);
		$tmp_pdo = null;
		return $result;
	}

	/**
	 * [majorCountList 根据专业统计数量]
	 * @return [type] [description]
	 */
	public static function majorCountList()
	{
		$tmp_pdo = PDO_MySQL::getInstance();
		$conditions = array(
			'fields' => 'major, count(*) as major_count',
			'sort' => array('major_count' => 0),
			'group_by' => 'major',
			'limit' => 11
		);
		$result = $tmp_pdo->getAll(self::TABLE_NAME, $conditions);
		$tmp_pdo = null;
		return $result;
	}

	/**
	 * [businessCountList 根据行业统计数量]
	 * @return [type] [description]
	 */
	public static function businessCountList()
	{
		$tmp_pdo = PDO_MySQL::getInstance();
		$conditions = array(
			'fields' => 'business, count(*) as business_count',
			'sort' => array('business_count' => 0),
			'group_by' => 'business',
			'limit' => 11
		);
		$result = $tmp_pdo->getAll(self::TABLE_NAME, $conditions);
		$tmp_pdo = null;
		return $result;
	}
}