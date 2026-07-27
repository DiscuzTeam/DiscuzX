<?php

namespace admin;

class widget_data {

	const widgets = [
		'left' => [
			'show_charts',
			'show_sysinfo',
			'show_hotthreads',
			'show_forever_thanks',
		],
		'right' => [
			'show_sitestatus',
			'show_todo',
			'show_onlines',
			'show_note',
			'show_filecheck',
		]
	];

	public static function get($type) {
		static $plugins = null;
		$widgets = self::widgets;
		if($plugins === null) {
			$plugins = \table_common_plugin::t()->fetch_all_data();
		}
		foreach($plugins as $plugin) {
			if(!$plugin['available']) {
				continue;
			}
			if(class_exists($plugin['identifier'].'\\admin_widget')) {
				foreach(get_class_methods($plugin['identifier'].'\\admin_widget') as $func) {
					if(!str_starts_with($func, 'widget_')) {
						continue;
					}
					$_type = str_ends_with($func, '_left') ? 'left' : 'right';
					if($_type != $type) {
						continue;
					}
					$widgets[$type][] = $plugin['identifier'].','.$func;
				}
			}
		}
		return $widgets;
	}

}

