<?php

/**
 * [Discuz!] (C)2001-2099 Discuz! Team
 * This is NOT a freeware, use is subject to license terms
 * https://license.discuz.vip
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = [
	'accessKeyId' => 'accessKey Id',
	'accessKeyId_comment' => 'accessKey Id, obtained from "RAM Access Control" in the Alibaba Cloud backend. Create a sub-user, check "Allow programmatic access", and grant "Manage SMS service" permission',
	'accessKeySecret' => 'accessKey Secret',
	'accessKeySecret_comment' => 'accessKey Secret, obtained from "RAM Access Control" in the Alibaba Cloud backend. Create a sub-user, check "Allow programmatic access", and grant "Manage SMS service" permission',
	'signname' => 'Domestic SMS Signature',
	'signname_comment' => 'Domestic SMS signature. Apply for a signature in Alibaba Cloud SMS service. After approval, view the signature name in Signature Management',
	'templateid' => 'Domestic SMS Template ID',
	'templateid_comment' => 'Domestic SMS template ID. Apply for an SMS template in Alibaba Cloud SMS service. After approval, view the template ID in SMS service. Example: SMS_12345678',
	'signnamegj' => 'International SMS Signature',
	'signnamegj_comment' => 'International SMS signature. Apply for a signature in Alibaba Cloud SMS service. After approval, view the signature name in Signature Management',
	'templateidgj' => 'International SMS Template ID',
	'templateidgj_comment' => 'International SMS template ID. Apply for an SMS template in Alibaba Cloud SMS service. After approval, view the template ID in SMS service. Example: SMS_12345678',
	'senderid' => 'Registered Long Code for US, Canada, etc.',
	'senderid_comment' => 'Registered long code for regions such as the US, Canada, etc. Need to contact Alibaba Cloud customer service to obtain',
	'senderidtemplate' => 'Complete SMS Content for US, Canada, etc.',
	'senderidtemplate_comment' => 'Complete SMS content for regions such as the US, Canada, etc. Subject to the registered content. Example: Your verification code is: ${code}.',
	'senderidareacode' => 'Area Codes for US, Canada, etc. (comma-separated)',
	'senderidareacode_comment' => 'Area Codes for US, Canada, etc. (comma-separated)',
	'alirich' => 'Registered Long Code for Taiwan, New Zealand, etc. (Fixed Alirich)',
	'alirich_comment' => 'Registered long code for regions such as Taiwan, New Zealand, etc. (Fixed Alirich). Consult Alibaba Cloud customer service for details',
	'alirichtemplate' => 'Complete SMS Content for Taiwan, New Zealand, etc.',
	'alirichtemplate_comment' => 'Complete SMS content for regions such as Taiwan, New Zealand, etc. Subject to the registered content. Example: Your verification code is: ${code}.',
	'alirichareacode' => 'Area Codes for Taiwan, New Zealand, etc. (comma-separated)',
	'alirichareacode_comment' => 'Area codes for regions such as Taiwan, New Zealand, etc. (comma-separated). Example: 886,64,62,84',
];

