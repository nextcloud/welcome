<template>
	<div id="welcome_prefs" class="section">
		<h2>
			<a class="icon icon-welcome" />
			{{ t('welcome', 'Welcome widget') }}
		</h2>
		<p class="settings-hint">
			<span class="icon icon-details" />
			{{ t('welcome', 'blabla') }}
		</p>
		<div class="grid-form">
			<label for="welcome-file-path">
				<a class="icon icon-file" />
				{{ t('welcome', 'Markdown content file') }}
			</label>
			<button @click="selectFile">
				<span class="icon icon-folder" />
			</button>
			<input id="welcome-file-path"
				type="text"
				:value="fullFilePath"
				:readonly="true"
				:placeholder="t('welcome', 'No file')"
				@click="selectFile">
		</div>
	</div>
</template>

<script>
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { getCurrentUser } from '@nextcloud/auth'
import { showSuccess, showError } from '@nextcloud/dialogs'

export default {
	name: 'AdminSettings',

	components: {
	},

	props: [],

	data() {
		return {
			state: loadState('welcome', 'admin-config'),
			user: getCurrentUser(),
		}
	},

	computed: {
		fullFilePath() {
			return this.state.filePath
				? this.state.userName + this.state.filePath
				: ''
		},
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
		selectFile() {
			OC.dialogs.filepicker(
				t('welcome', 'Choose markdown welcome content file'),
				(targetPath) => {
					this.state.filePath = targetPath
					this.state.userName = this.user.displayName
					this.state.userId = this.user.uid
					this.saveOptions({
						filePath: this.state.filePath,
						userName: this.state.userName,
						userId: this.state.userId,
					})
				},
				false,
				['text/markdown'],
				true
			)
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
	grid-template: 1fr / 1fr 44px 1fr;
	margin-left: 30px;

	button {
		display: flex;
	}
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
