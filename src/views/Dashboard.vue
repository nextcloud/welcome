<template>
	<div id="welcome-widget">
		<span v-if="loading" class="icon icon-loading" />
		<VueMarkdown v-else-if="content"
			class="markdown-content">
			{{ content }}
		</VueMarkdown>
		<EmptyContent
			v-else
			:icon="emptyContentIcon">
			<template #desc>
				{{ emptyContentMessage }}
			</template>
		</EmptyContent>
		<a v-if="supportUserId"
			class="call-link"
			:href="callUrl">
			<Avatar
				:user="supportUserId"
				:tooltip-message="supportUserName" />
			<span>{{ callSupportUserText }}</span>
		</a>
	</div>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import EmptyContent from '@nextcloud/vue/dist/Components/EmptyContent'
import Avatar from '@nextcloud/vue/dist/Components/Avatar'

import VueMarkdown from 'vue-markdown'

export default {
	name: 'Dashboard',

	components: {
		// DashboardWidget,
		EmptyContent,
		VueMarkdown,
		Avatar,
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
				console.debug('"' + this.content + '"')
			}).catch((error) => {
				console.debug(error)
			}).then(() => {
				this.loading = false
			})
		},
	},
}
</script>

<style scoped lang="scss">
::v-deep .markdown-content {
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
	}
	a {
		color: var(--color-text-lighter);
		text-decoration: underline;
	}
	> p {
		display: flex;
		img {
			margin: 0 auto 0 auto;
			height: 100px;
			width: auto;
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
