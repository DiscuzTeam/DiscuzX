<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

/*

hook::register([
	['hookid' => 'common', 'method' => ['sample\lib_hook', 'test1'], 'type' => 1, 'modid' => 'global::common'],
	['hookid' => 'global_usernav_extra1', 'method' => ['sample\lib_hook', 'test1'], 'type' => 1, 'modid' => 'global::global'],
	['hookid' => 'viewthread_avatartop', 'method' => ['sample\lib_hook', 'test2'], 'type' => 1, 'modid' => 'forum::viewthread'],
]);

hook::unregister([
	['hookid' => 'global_usernav_extra1', 'method' => ['sample\lib_hook', 'test'], 'type' => 1, 'modid' => 'global::global'],
]);

 */

class hook {

	const TYPES = [
		1 => 'funcs',
		2 => 'outputfuncs',
		3 => 'messagefuncs',
	];

	/**
	 * @param $data [
	 *      [
	 *        hookid: 嵌入点 ID
	 *        method: [类名, 方法名]
	 *        modid: CURSCRIPT::CURMODULE
	 *        type: 1=funcs,2=outputfuncs,3=messagefuncs
	 *        hookType: 1=PC,2=mobile,空=所有
	 *      ],
	 *      ...
	 * ]
	 */
	public static function register($data, $updateCache = true) {
		self::_do($data, 0, $updateCache);
	}

	public static function unregister($data, $updateCache = true) {
		self::_do($data, 1, $updateCache);
	}

	private static function _do($data, $op, $updateCache = true) {
		global $_G;

		$newhook = table_common_setting::t()->fetch_setting('newhook', true);

		foreach($data as $row) {
			if(!isset($row['modid']) || !isset(self::TYPES[$row['type']]) || !isset($row['hookid']) || !isset($row['method'])) {
				continue;
			}
			$hookTypes = ['hookscript', 'hookscriptmobile'];
			if($row['hookType'] == 1) {
				unset($hookTypes[1]);
			} elseif($row['hookType'] == 2) {
				unset($hookTypes[0]);
			}
			list($hscript, $script) = explode('::', $row['modid']);
			foreach($hookTypes as $hookType) {
				$key = md5(serialize($row['method']));
				if(!$op) {
					if(!method_exists($row['method'][0], $row['method'][1])) {
						continue;
					}
					$newhook[$hookType][$hscript][$script][self::TYPES[$row['type']]][$row['hookid']][$key] = $row['method'];
				} else {
					unset($newhook[$hookType][$hscript][$script][self::TYPES[$row['type']]][$row['hookid']][$key]);
				}
			}
		}

		table_common_setting::t()->update('newhook', $newhook);
		if($updateCache) {
			require_once libfile('function/cache');
			updatecache('setting');
		}
	}

}