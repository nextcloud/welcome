<template>
	<div id="welcome-widget">
		<a v-if="userId"
			class="call-link"
			:href="callUrl">
			<Avatar :user="userId" />
			<span>Call {{ userName }}</span>
		</a>
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
			return generateUrl('/apps/spreed/?callUser=' + this.userId)
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
				this.userId = response.data.userId
				this.userName = response.data.userName
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
	h1 {
		font-size: 40px;
		font-weight: bold;
		line-height: 40px;
		margin-bottom: 10px;
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
}

#welcome-widget {
	overflow: scroll;
	height: 100%;

	.icon-loading {
		display: block;
		margin-top: 50%;
	}

	.call-link {
		display: flex;
		span {
			margin: auto 0 auto 10px;
		}
	}
}
</style>
