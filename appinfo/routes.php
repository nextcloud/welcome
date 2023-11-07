<?php
/**
 * Nextcloud - Welcome2
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 * @copyright Julien Veyssier 2021
 */

return [
    'routes' => [
        ['name' => 'config#setAdminConfig', 'url' => '/admin-config', 'verb' => 'PUT'],
        ['name' => 'config#getWidgetContent', 'url' => '/widget-content', 'verb' => 'GET'],
		['name' => 'config#getWidgetImage', 'url' => '/widget/image/{fileId}', 'verb' => 'GET'],
	]
];
