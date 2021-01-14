<template>
	<div id="welcome_prefs" class="section">
		<h2>
			<a class="icon icon-welcome" />
			{{ t('welcome', 'Welcome widget') }}
		</h2>
		<p class="settings-hint">
			<span class="icon icon-details" />
			{{ t('integration_gitlab', 'Make sure you set the "Redirect URI" to') }}
		</p>
		<div class="grid-form">
			<label for="welcome-file-path">
				<a class="icon icon-link" />
				{{ t('welcome', 'Markdown content file') }}
			</label>
			<input id="welcome-file-path"
				type="text"
				:value="filePath"
				:readonly="true"
				:placeholder="t('welcome', 'No file')">
			<button>
				<span class="icon icon-file" />
			</button>
		</div>
	</div>
</template>

<script>
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { delay } from '../utils'
import { showSuccess, showError } from '@nextcloud/dialogs'

export default {
	name: 'AdminSettings',

	components: {
	},

	props: [],

	data() {
		return {
			state: loadState('welcome', 'admin-config'),
		}
	},

	watch: {
	},

	mounted() {
	},

	methods: {
		saveOptions(values) {
			const req = {
				values,
			}
			const url = generateUrl('/apps/welcome/admin-config')
			axios.put(url, req)
				.then((response) => {
					showSuccess(t('welcome', 'Welcome admin options saved'))
				})
				.catch((error) => {
					showError(
						t('welcome', 'Failed to save welcome admin options')
						+ ': ' + (error.response?.request?.responseText ?? '')
					)
					console.debug(error)
				})
				.then(() => {
				})
		},
	},
}
</script>

<style scoped lang="scss">
.grid-form label {
	line-height: 38px;
}

.grid-form input {
	width: 100%;
}

.grid-form {
	max-width: 500px;
	display: grid;
	grid-template: 1fr / 1fr 1fr;
	margin-left: 30px;
}

#welcome_prefs .icon {
	display: inline-block;
	width: 32px;
}

#welcome_prefs .grid-form .icon {
	margin-bottom: -3px;
}

.icon-welcome {
	background-image: url(./../../img/app-dark.svg);
	background-size: 23px 23px;
	height: 23px;
	margin-bottom: -4px;
}

body.theme--dark .icon-welcome {
	background-image: url(./../../img/app.svg);
}

</style>
