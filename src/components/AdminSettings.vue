<template>
	<div id="welcome_prefs" class="section">
		<h2>
			<a class="icon icon-welcome" />
			{{ t('welcome', 'Welcome widget') }}
		</h2>
		<p class="settings-hint">
			<span class="icon icon-details" />
			{{ t('welcome', 'The dashboard welcome widget will be displayed for all users only if you choose a markdown file.') }}
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
			<button v-if="state.filePath"
				@click="clear">
				<span class="icon icon-delete" />
			</button>
			<Multiselect ref="multiselect"
				class="support-input"
				label="displayName"
				:clear-on-select="false"
				:hide-selected="false"
				:internal-search="false"
				:loading="loadingUsers"
				:options="formattedSuggestions"
				:placeholder="t('welcome', 'Choose a support user')"
				:preselect-first="false"
				:preserve-search="true"
				:searchable="true"
				:user-select="true"
				@search-change="asyncFind"
				@select="supportUserSelected">
				<template #noOptions>
					{{ t('welcome', 'No recommendations. Start typing.') }}
				</template>
				<template #noResult>
					{{ t('welcome', 'No result.') }}
				</template>
			</Multiselect>
		</div>
	</div>
</template>

<script>
import { loadState } from '@nextcloud/initial-state'
import { generateUrl, generateOcsUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { getCurrentUser } from '@nextcloud/auth'
import { showSuccess, showError } from '@nextcloud/dialogs'
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'

export default {
	name: 'AdminSettings',

	components: {
		Multiselect,
	},

	props: [],

	data() {
		return {
			state: loadState('welcome', 'admin-config'),
			currentUser: getCurrentUser(),
			loadingUsers: false,
			suggestions: [],
		}
	},

	computed: {
		fullFilePath() {
			return this.state.filePath
				? this.state.userName + this.state.filePath
				: ''
		},
		formattedSuggestions() {
			const result = this.suggestions.map((s) => {
				return {
					user: s.id,
					displayName: s.label,
					icon: 'icon-user',
					multiselectKey: s.id,
				}
			})
			if (this.currentUser) {
				result.push({
					user: this.currentUser.uid,
					displayName: this.currentUser.displayName,
					icon: 'icon-user',
					multiselectKey: this.currentUser.uid,
				})
			}
			return result
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
		clear() {
			this.state.filePath = ''
			this.state.userName = ''
			this.state.userId = ''
			this.saveOptions({
				filePath: '',
				userName: '',
				userId: '',
			})
		},
		selectFile() {
			OC.dialogs.filepicker(
				t('welcome', 'Choose markdown welcome content file'),
				(targetPath) => {
					this.state.filePath = targetPath
					this.state.userName = this.currentUser.displayName
					this.state.userId = this.currentUser.uid
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
		asyncFind(query) {
			if (query === '') {
				this.suggestions = []
				return
			}
			this.loadingUsers = true
			console.debug(query)
			const url = generateOcsUrl('core/autocomplete/get', 2).replace(/\/$/, '')
			axios.get(url, {
				params: {
					format: 'json',
					search: query,
					itemType: ' ',
					itemId: ' ',
					shareTypes: [],
				},
			}).then((response) => {
				console.debug(response)
				this.suggestions = response.data.ocs.data
			}).catch((error) => {
				console.error(error)
			}).then(() => {
				this.loadingUsers = false
			})
		},
		supportUserSelected(user) {
			console.debug(user)
		},
	},
}
</script>

<style scoped lang="scss">
#welcome_prefs .icon {
	display: inline-block;
	width: 32px;
}

#welcome_prefs .grid-form .icon {
	margin-bottom: -3px;
}

.grid-form label {
	line-height: 38px;
}

.grid-form input {
	width: 100%;
}

.grid-form {
	max-width: 500px;
	display: grid;
	grid-template: 1fr / 1fr 44px 1fr 44px;
	margin-left: 30px;

	button {
		display: flex;
		span {
			margin-bottom: 0 !important;
		}
	}
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
