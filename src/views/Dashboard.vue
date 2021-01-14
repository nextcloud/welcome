<template>
	<div v-if="content">
		{{ content }}
	</div>
	<EmptyContent
		v-else
		:icon="emptyContentIcon">
		<template #desc>
			{{ emptyContentMessage }}
		</template>
	</EmptyContent>
</template>

<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
// import { DashboardWidget } from '@nextcloud/vue-dashboard'
// import { showError } from '@nextcloud/dialogs'
// import moment from '@nextcloud/moment'
import EmptyContent from '@nextcloud/vue/dist/Components/EmptyContent'

export default {
	name: 'Dashboard',

	components: {
		// DashboardWidget,
		EmptyContent,
	},

	props: {
		title: {
			type: String,
			required: true,
		},
	},

	data() {
		return {
			content: '',
		}
	},

	computed: {
		emptyContentMessage() {
			return t('welcome', 'No welcome content')
		},
		emptyContentIcon() {
			return 'icon-close'
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
				this.content = response.data
			}).catch((error) => {
				console.debug(error)
			})
		},
	},
}
</script>

<style scoped lang="scss">
// nothing
</style>
