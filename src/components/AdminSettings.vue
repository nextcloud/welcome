<!--
  - SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
<template>
	<div id="welcome_prefs" class="section">
		<h2>
			<WelcomeIcon :size="24" />
			{{ t('welcome', 'Welcome widget') }}
		</h2>
		<NcNoteCard type="info">
			{{ t('welcome', 'The dashboard welcome widget will be displayed for all users only if you choose a markdown file.') }}
		</NcNoteCard>
		<div class="line">
			<label for="welcome-file-path">
				<FileOutlineIcon :size="20" class="icon" />
				{{ t('welcome', 'Markdown content file') }}
			</label>
			<div>
				<NcButton @click="selectFile">
					<template #icon>
						<FolderOutlineIcon :size="20" />
					</template>
				</NcButton>
				<input id="welcome-file-path"
					type="text"
					:value="fullFilePath"
					:readonly="true"
					:placeholder="t('welcome', 'No file')"
					@click="selectFile">
				<NcButton v-if="state.filePath"
					@click="clear">
					<template #icon>
						<DeleteOutlineIcon :size="20" />
					</template>
				</NcButton>
			</div>
		</div>
		<br>
		<div v-if="state.filePath">
			<div class="line">
				<label for="welcome-widget-title">
					<FileOutlineIcon :size="20" class="icon" />
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
					<AccountOutlineIcon :size="20" class="icon" />
					{{ t('welcome', 'Support contact') }}
				</label>
				<div v-if="state.supportUserId">
					<NcAvatar
						:size="40"
						:user="state.supportUserId"
						:tooltip-message="state.supportUserName" />
					<span class="support-user-name">
						{{ state.supportUserName }}
					</span>
					<NcButton
						@click="clearSupportContact">
						<template #icon>
							<DeleteOutlineIcon :size="20" />
						</template>
					</NcButton>
				</div>
				<div v-else>
					<NcSelect
						class="support-input"
						label="displayName"
						:clear-search-on-select="false"
						:loading="loadingUsers"
						:options="formattedSuggestions"
						:placeholder="t('welcome', 'Choose a support user')"
						:preserve-search="true"
						:user-select="true"
						@search="asyncFind"
						@update:model-value="supportContactSelected" />
				</div>
			</div>
			<div class="line">
				<label for="welcome-support-text">
					<FileOutlineIcon :size="20" class="icon" />
					{{ t('welcome', 'Support text') }}
				</label>
				<input id="welcome-support-text"
					v-model="state.supportText"
					type="text"
					:class="{ 'icon-loading-small': saving }"
					:placeholder="t('welcome', 'Example: Call {name} to get help.')"
					@input="onTextChange">
			</div>
			<div class="line">
				<NcNoteCard type="info">
					{{ t('welcome', '{name} will be replaced by the support user name') }}
				</NcNoteCard>
			</div>
			<br>
			<NcButton
				class="line"
				@click="enableWidget">
				<template #icon>
					<ViewDashboardOutlineIcon />
				</template>
				{{ t('welcome', 'Enable the widget for all users') }}
			</NcButton>
		</div>
	</div>
</template>

<script>
import AccountOutlineIcon from 'vue-material-design-icons/AccountOutline.vue'
import DeleteOutlineIcon from 'vue-material-design-icons/DeleteOutline.vue'
import FileOutlineIcon from 'vue-material-design-icons/FileOutline.vue'
import FolderOutlineIcon from 'vue-material-design-icons/FolderOutline.vue'
import ViewDashboardOutlineIcon from 'vue-material-design-icons/ViewDashboardOutline.vue'

import { getCurrentUser } from '@nextcloud/auth'
import axios from '@nextcloud/axios'
import { getFilePickerBuilder, showError, showSuccess } from '@nextcloud/dialogs'
import { loadState } from '@nextcloud/initial-state'
import { generateOcsUrl, generateUrl } from '@nextcloud/router'

import NcAvatar from '@nextcloud/vue/components/NcAvatar'
import NcButton from '@nextcloud/vue/components/NcButton'
import NcSelect from '@nextcloud/vue/components/NcSelect'
import NcNoteCard from '@nextcloud/vue/components/NcNoteCard'

import { delay } from '../utils.js'
import WelcomeIcon from './icons/WelcomeIcon.vue'

export default {
	name: 'AdminSettings',

	components: {
		WelcomeIcon,
		NcSelect,
		NcButton,
		NcAvatar,
		NcNoteCard,
		DeleteOutlineIcon,
		FolderOutlineIcon,
		AccountOutlineIcon,
		FileOutlineIcon,
		ViewDashboardOutlineIcon,
	},

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
						+ ': ' + (error.response?.request?.responseText ?? ''),
					)
					console.error(error)
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
		async selectFile() {
			const filepicker = getFilePickerBuilder(t('welcome', 'Choose markdown welcome content file'))
				.addMimeTypeFilter('text/markdown')
				.setMultiSelect(false)
				.allowDirectories(false)
				.addButton({
					label: t('welcome', 'Choose'),
					variant: 'primary',
					callback: (nodes) => {
						console.debug('File picked:', nodes[0])
						const file = nodes[0]

						this.state.filePath = file.path
						this.state.userName = this.currentUser.displayName
						this.state.userId = this.currentUser.uid
						this.saveOptions({
							filePath: this.state.filePath,
							userName: this.state.userName,
							userId: this.state.userId,
						})
					},
				})
				.build()

			await filepicker.pick()
		},
		asyncFind(query) {
			this.query = query
			if (query === '') {
				this.suggestions = []
				return
			}
			this.loadingUsers = true
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
		enableWidget() {
			this.saving = true
			const req = {}
			const url = generateUrl('/apps/welcome/enable-widget')
			axios.put(url, req)
				.then((response) => {
					showSuccess(t('welcome', 'The Welcome widget is now enabled for all active users'))
				})
				.catch((error) => {
					showError(
						t('welcome', 'Failed to enable the Welcome widget')
						+ ': ' + (error.response?.error ?? ''),
					)
					console.error(error)
				})
				.then(() => {
					this.saving = false
				})
		},
	},
}
</script>

<style scoped lang="scss">
#welcome_prefs {
	max-width: 800px;
	h2 {
		display: flex;
		align-items: center;
		justify-content: start;
		gap: 8px;
	}
	.icon {
		margin-inline-end: 8px;
	}

	.line {
		display: flex;
		align-items: center;
		margin-inline-start: 30px;
		margin-top: 4px;

		label {
			display: flex;
			align-items: center;
			line-height: 38px;
			width: 250px;
			.icon {
				margin-inline-end: 4px;
			}
		}

		input, .support-input {
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

:deep(.support-avatar-option) {
	margin-inline-end: 10px;
}
</style>
