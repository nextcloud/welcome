<?php

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

return [
	'routes' => [
		['name' => 'config#setAdminConfig', 'url' => '/admin-config', 'verb' => 'PUT'],
		['name' => 'config#getWidgetContent', 'url' => '/widget-content', 'verb' => 'GET'],
		['name' => 'config#getWidgetImage', 'url' => '/widget/image/{fileId}', 'verb' => 'GET'],
		['name' => 'config#enableWidgetForAllUsers', 'url' => '/enable-widget', 'verb' => 'PUT'],
	]
];
