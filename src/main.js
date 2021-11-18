/*
 * ReserveRoom
 */
import Vue from 'vue'
import { translate as t, translatePlural as n } from '@nextcloud/l10n'

import App from './App'

// Adding translations to the whole app
Vue.mixin({
	methods: {
		t,
		n,
	},
})

export default new Vue({
	el: '#content',
	render: h => h(App),
})
