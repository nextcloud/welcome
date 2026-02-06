/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { createApp } from 'vue'
import Dashboard from './views/Dashboard.vue'

document.addEventListener('DOMContentLoaded', function() {

	OCA.Dashboard.register('welcome', (el, { widget }) => {
		const app = createApp(Dashboard, {
			title: widget.title,
		})
		app.mixin({ methods: { t, n } })
		app.mount(el)
	})

})
