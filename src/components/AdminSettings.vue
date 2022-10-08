<template>
	<div id="welcome_prefs" class="section">
		<h2>
			<WelcomeIcon :size="24" class="icon" />
			{{ t('welcome', 'Welcome widget') }}
		</h2>
		<p class="settings-hint">
			<InformationOutlineIcon :size="20" class="icon" />
			{{ t('welcome', 'The dashboard welcome widget will be displayed for all users only if you choose a markdown file.') }}
		</p>
		<div class="line">
			<label for="welcome-file-path">
				<FileIcon :size="20" class="icon" />
				{{ t('welcome', 'Markdown content file') }}
			</label>
			<div>
				<button @click="selectFile">
					<FolderIcon :size="20" class="icon" />
				</button>
				<input id="welcome-file-path"
					type="text"
					:value="fullFilePath"
					:readonly="true"
					:placeholder="t('welcome', 'No file')"
					@click="selectFile">
				<button v-if="state.filePath"
					@click="clear">
					<DeleteIcon :size="20" class="icon" />
				</button>
			</div>
		</div>
		<br>
		<div v-if="state.filePath">
			<div class="line">
				<label for="welcome-widget-title">
					<FileIcon :size="20" class="icon" />
					{{ t('welcome', 'Widget title') }}
				</label>
				<input id="welcome-widget-title"
					v-model="state.widgetTitle"
					type="text"
					:class="{ 'icon-loading-small': saving }"
					:placeholder="t('welcome', 'Welcome')"
					@input="onTextChange">
			</div>
			<div class="line">
				<label for="welcome-support">
					<AccountIcon :size="20" class="icon" />
					{{ t('welcome', 'Support contact') }}
				</label>
				<div v-if="state.supportUserId">
					<Avatar
						:size="40"
						:user="state.supportUserId"
						:tooltip-message="state.supportUserName" />
					<span class="support-user-name">
						{{ state.supportUserName }}
					</span>
					<button
						@click="clearSupportContact">
						<DeleteIcon :size="20" class="icon" />
					</button>
				</div>
				<div v-else>
					<Multiselect
						ref="multiselect"
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
						@select="supportContactSelected">
						<template #option="{option}">
							<Avatar
								class="support-avatar-option"
								:user="option.user"
								:show-user-status="false" />
							<span>
								{{ option.displayName }}
							</span>
						</template>
						<template #noOptions>
							{{ t('welcome', 'No recommendations. Start typing.') }}
						</template>
						<template #noResult>
							{{ t('welcome', 'No result.') }}
						</template>
					</Multiselect>
				</div>
			</div>
			<div class="line">
				<label for="welcome-support-text">
					<FileIcon :size="20" class="icon" />
					{{ t('welcome', 'Support text') }}
				</label>
				<input id="welcome-support-text"
					v-model="state.supportText"
					type="text"
					:class="{ 'icon-loading-small': saving }"
					:placeholder="t('welcome', 'Example: Call {name} to get help.')"
					@input="onTextChange">
				<span class="settings-hint">
					<InformationOutlineIcon :size="20" class="icon" />
					{{ t('welcome', '{name} will be replaced by the support user name') }}
				</span>
			</div>
		</div>
	</div>
</template>

<script>
import FileIcon from 'vue-material-design-icons/File.vue'
import InformationOutlineIcon from 'vue-material-design-icons/InformationOutline.vue'
import AccountIcon from 'vue-material-design-icons/Account.vue'
import DeleteIcon from 'vue-material-design-icons/Delete.vue'
import FolderIcon from 'vue-material-design-icons/Folder.vue'

import { loadState } from '@nextcloud/initial-state'
import { generateUrl, generateOcsUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { getCurrentUser } from '@nextcloud/auth'
import { showSuccess, showError } from '@nextcloud/dialogs'
import Multiselect from '@nextcloud/vue/dist/Components/Multiselect'
import Avatar from '@nextcloud/vue/dist/Components/Avatar'

import { delay } from '../utils'
import WelcomeIcon from './icons/WelcomeIcon'

export default {
	name: 'AdminSettings',

	components: {
		WelcomeIcon,
		Multiselect,
		Avatar,
		DeleteIcon,
		FolderIcon,
		AccountIcon,
		InformationOutlineIcon,
		FileIcon,
	},

	props: [],

	data() {
		return {
			state: loadState('welcome', 'admin-config'),
			saving: false,
			currentUser: getCurrentUser(),
			query: '',
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
					multiselectKey: s.id + s.label,
				}
			})
			if (this.currentUser) {
				const lowerCurrent = this.currentUser.displayName.toLowerCase()
				const lowerQuery = this.query.toLowerCase()
				if (this.query === '' || lowerCurrent.match(lowerQuery)) {
					result.push({
						user: this.currentUser.uid,
						displayName: this.currentUser.displayName,
						icon: 'icon-user',
						multiselectKey: this.currentUser.uid + this.currentUser.displayName,
					})
				}
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
			this.saving = true
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
					this.saving = false
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
			this.query = query
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
		supportContactSelected(user) {
			console.debug(user)
			this.state.supportUserId = user.user
			this.state.supportUserName = user.displayName
			this.saveOptions({
				supportUserId: this.state.supportUserId,
				supportUserName: this.state.supportUserName,
			})
		},
		clearSupportContact() {
			this.state.supportUserId = ''
			this.state.supportUserName = ''
			this.saveOptions({
				supportUserId: '',
				supportUserName: '',
			})
		},
		onTextChange() {
			delay(() => {
				this.saveOptions({
					supportText: this.state.supportText,
					widgetTitle: this.state.widgetTitle,
				})
			}, 2000)()
		},
	},
}
</script>

<style scoped lang="scss">
#welcome_prefs {
	h2 {
		display: flex;
		align-items: center;
	}
	.icon {
		margin-right: 8px;
	}

	.line {
		display: flex;
		align-items: center;
		margin-left: 30px;

		label {
			display: flex;
			align-items: center;
			line-height: 38px;
			width: 250px;
			.icon {
				margin-right: 4px;
			}
		}

		input {
			width: 300px;
		}

		> div {
			display: flex;
		}

		button {
			display: flex;
			width: 44px;

			span {
				margin-bottom: 0 !important;
			}
		}

		.support-user-name {
			line-height: 40px;
			margin: 0 10px 0 10px;
		}
	}
}

.settings-hint {
	margin: 0;
	display: flex;
	align-items: center;
	.icon {
		margin-right: 4px !important;
	}
}

::v-deep .support-avatar-option {
	margin-right: 10px;
}
</style>
