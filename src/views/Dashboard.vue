<template>
	<div id="welcome-widget">
		<span v-if="loading" class="icon icon-loading" />
		<RichText v-else-if="content"
			class="markdown-content"
			:text="content"
			:use-markdown="true" />
		<NcEmptyContent v-else
			:icon="emptyContentIcon">
			<template #desc>
				{{ emptyContentMessage }}
			</template>
		</NcEmptyContent>
		<a v-if="supportUserId"
			v-tooltip.top="{ content: callSupportUserTooltip }"
			class="call-link"
			:href="callUrl">
			<NcAvatar :user="supportUserId"
				:tooltip-message="supportUserName" />
			<span>{{ callSupportUserText }}</span>
		</a>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

import NcEmptyContent from '@nextcloud/vue/dist/Components/NcEmptyContent.js'
import NcAvatar from '@nextcloud/vue/dist/Components/NcAvatar.js'
import Tooltip from '@nextcloud/vue/dist/Directives/Tooltip.js'

import { RichText } from '@nextcloud/vue-richtext'

import Vue from 'vue'
Vue.directive('tooltip', Tooltip)

export default {
	name: 'Dashboard',

	components: {
		NcEmptyContent,
		NcAvatar,
		RichText,
	},

	props: {
		title: {
			type: String,
			required: true,
		},
	},

	data() {
		return {
			loading: true,
			content: '',
			userId: null,
			userName: null,
			supportUserId: null,
		}
	},

	computed: {
		emptyContentMessage() {
			return t('welcome', 'No welcome content')
		},
		emptyContentIcon() {
			return 'icon-close'
		},
		callUrl() {
			return generateUrl('/apps/spreed/?callUser=' + this.supportUserId)
		},
		callSupportUserText() {
			return this.supportText
				? this.supportText.replace('{name}', this.supportUserName)
				: t('welcome', 'Talk to your support contact ({name})', { name: this.supportUserName })
		},
		callSupportUserTooltip() {
			return t('welcome', 'Talk to {name}', { name: this.supportUserName })
		},
	},

	beforeMount() {
		this.getContent()
	},

	mounted() {
	},

	methods: {
		getContent() {
			const url = generateUrl('/apps/welcome/widget-content')
			axios.get(url).then((response) => {
				this.content = response.data.content
				// eslint-disable-next-line
				this.content = this.content.replaceAll(/\!\[(.*)\]\(.*\?fileId=(\d+).*/g, (match, p1, p2) => {
					return '![' + p1 + '](' + generateUrl('/core/preview?fileId=' + p2 + '&x=200&y=200&a=true') + ')'
				})
				this.userId = response.data.userId
				this.userName = response.data.userName
				this.supportUserId = response.data.supportUserId
				this.supportUserName = response.data.supportUserName
				this.supportText = response.data.supportText
			}).catch((error) => {
				console.error(error)
			}).then(() => {
				this.loading = false
			})
		},
	},
}
</script>

<style scoped lang="scss">
:deep(.markdown-content) {
	h1, h2, h3, h4, h5 {
		font-weight: bold;
		margin: 12px 0 12px 0;
	}
	h1 {
		font-size: 30px;
		line-height: 30px;
	}
	h2 {
		font-size: 20px;
		line-height: 20px;
	}
	h3 {
		font-size: 16px;
		line-height: 20px;
	}
	h4 {
		font-size: 14px;
		line-height: 20px;
	}
	h5 {
		font-size: 12px;
		line-height: 20px;
	}
	ul, ol {
		list-style-type: none;
		li {
			list-style-type: none;
		}
		li:before {
			content: '•';
			padding-right: 8px;
		}
		ul, ol {
			margin-left: 20px;
			li:before {
				content: '∘';
			}
			ul, ol {
				li:before {
					content: '⁃';
				}
			}
		}
	}
	a {
		color: var(--color-text-light);
		text-decoration: underline;
	}
	p {
		> img {
			display: block;
			margin: 0 auto 0 auto;
			max-height: 150px;
			max-width: 250px;
		}
	}
}

#welcome-widget {
	overflow: scroll;
	height: 100%;
	padding: 0 10px 0 10px;

	.icon-loading {
		display: block;
		margin-top: 50%;
	}

	.call-link {
		display: flex;
		margin-top: 25px;
		border-radius: var(--border-radius-large);
		span {
			margin: auto 0 auto 10px;
		}
		&:hover {
			background-color: var(--color-background-hover);
		}
	}
}
</style>
