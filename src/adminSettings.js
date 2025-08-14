/* jshint esversion: 6 */

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { getCSPNonce } from '@nextcloud/auth'
import { linkTo } from '@nextcloud/router'
import Vue from 'vue'
import './bootstrap.js'
import AdminSettings from './components/AdminSettings.vue'

__webpack_nonce__ = getCSPNonce() // eslint-disable-line
__webpack_public_path__ = linkTo('welcome', 'js/') // eslint-disable-line

const View = Vue.extend(AdminSettings)
new View().$mount('#welcome_prefs')
