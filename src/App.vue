<template>
	<Content :class="{'icon-loading': loading}" app-name="reserveroom">
		<AppNavigation>
			<div class="nav-button">
				<button @click="prevDay">
					{{ t('reserveroom', 'Prev') }}
				</button>
				<button @click="today">
					{{ t('reserveroom', 'Today') }}
				</button>
				<button @click="nextDay">
					{{ t('reserveroom', 'Next') }}
				</button>
				<DatetimePicker
					v-model="datetime_current"
					placeholder=" "
					type="date"
					@change="updateDate" />
			</div>
			<div class="nav-button">
				<button @click="mouseClick()">
					{{ t('reserveroom', 'Add Reserve') }}
				</button>
			</div>
			<ul v-if="isAdmin" class="nav-config">
				<div class="nav-outer">
					<AppNavigationItem :title="t('reserveroom', 'Setting Facility')"
						icon="icon-edit"
						:allow-collapse="true"
						:open="false">
							<AppNavigationItem
								v-for="room in sortedRooms"
								:key="room.id"
								icon="icon-edit"
								:title="room.facil_name">
								<template #actions>
									<ActionButton
										v-if="showRoomNameLabel"
									icon="icon-edit"
									@click.prevent.stop="openRoomNameInput">
									{{ t('reserveroom', 'Modify Facility Name') }}
								</ActionButton>
								<ActionInput
									v-if="showRoomNameInput"
									:value="room.facil_name"
									icon="icon-edit"
									@submit.prevent.stop="saveRoomNameInput($event, room)" />
								<ActionButton
									v-if="showRoomOrderLabel"
									icon="icon-edit"
									@click.prevent.stop="openRoomOrderInput">
									{{ t('reserveroom', 'Modify Order') }}
								</ActionButton>
								<ActionInput
									v-if="showRoomOrderInput"
									:value="room.sort_num"
									icon="icon-edit"
									@submit.prevent.stop="saveRoomOrderInput($event, room)" />
								<ActionSeparator />
								<ActionButton icon="icon-delete" @click="deleteRoom(room.id)">
									{{ t('reserveroom', 'Delete') }}
								</ActionButton>
							</template>
						</AppNavigationItem>
						<AppNavigationNewItem :title="t('reserveroom', 'Add Facility')" icon="icon-add" @new-item="addRoom" />
					</AppNavigationItem>
				</div>
			</ul>
		</AppNavigation>

		<AppContent>
			<div class="reserve-header">
				<div class="reserve-date">
					{{ cur_year }}-{{ cur_mon }}-{{ cur_date }} ({{ week[cur_day] }})
				</div>
			</div>
			<div class="daily-reserve">
				<div class="outer">
					<table class="reserve-tbl">
						<thead>
							<tr>
								<td class="column-time" />
								<th v-for="room in sortedRooms" :key="room.id" class="column-reserve">
									{{ room.facil_name }}
								</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="column-time">
									<div v-for="n in 24"
										:id="'time-' + (n - 1)"
										:key="n"
										class="time-cell">
										{{ (n - 1) + ":00" }}
									</div>
								</td>
								<td v-for="room in sortedRooms"
									:key="room.id"
									class="column-reserve">
									<template v-for="n in 48">
										<template v-if="room.reserve[n]">
											<div :key="n" class="reserve-bg-cell">
												<div class="reserve-content">
													<div :class="room.reserve[n].class"
														:style="'height: ' + getHeight(room.reserve[n].start, room.reserve[n].end) + 'px'"
														@click="reserveClick(room.id, room.reserve[n])">
														{{ room.reserve[n].displayname }}
													</div>
													<div :class="room.reserve[n].tooltips">
														<div>{{ getDateTime(room.reserve[n].start) }} - {{ getDateTime(room.reserve[n].end) }}</div>
														<div class="tooltip-desc">
															{{ room.reserve[n].desc }}
														</div>
													</div>
												</div>
											</div>
										</template>
										<template v-else>
											<div :key="n" class="reserve-bg-cell" @click="backgroundClick(room.id, n)" />
										</template>
									</template>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</AppContent>

		<AppSidebar
			v-show="show"
			:title="title"
			@close="close">
			<div class="sidebar">
				<div>
					<p>{{ t('reserveroom', 'Facility') }}</p>
					<Multiselect v-model="selected"
						:options="sortedRooms"
						label="facil_name"
						icon="icon-add" />
				</div>
				<div>
					<p>{{ t('reserveroom', 'From') }}</p>
					<DatetimePicker
						v-model="datetime_from"
						format="YYYY-MM-DD HH:mm"
						placeholder=" "
						:minute-step="Number(30)"
						type="datetime" />
					<p>{{ t('reserveroom', 'To') }}</p>
					<DatetimePicker
						v-model="datetime_to"
						format="YYYY-MM-DD HH:mm"
						:minute-step="Number(30)"
						placeholder=" "
						type="datetime" />
				</div>
				<div>
					<p>{{ t('reserveroom', 'Description') }}</p>
					<textarea v-model="description" class="description" />
				</div>
				<div v-if="addButton">
					<button @click="addReserve">
						{{ t('reserveroom', 'Add') }}
					</button>
					<button @click="close">
						{{ t('reserveroom', 'Cancel') }}
					</button>
				</div>
				<div v-if="editButton">
					<button @click="editReserve">
						{{ t('reserveroom', 'Apply') }}
					</button>
					<button @click="close">
						{{ t('reserveroom', 'Cancel') }}
					</button>
				</div>
				<div v-if="deleteButton">
					<button @click="deleteReserve">
						{{ t('reserveroom', 'Delete') }}
					</button>
				</div>
			</div>
		</AppSidebar>
	</Content>
</template>

<script>
import Content from '@nextcloud/vue/dist/Components/NcContent'
import AppContent from '@nextcloud/vue/dist/Components/NcAppContent'
import AppNavigation from '@nextcloud/vue/dist/Components/NcAppNavigation'
import AppNavigationItem from '@nextcloud/vue/dist/Components/NcAppNavigationItem'
import AppNavigationNewItem from '@nextcloud/vue/dist/Components/NcAppNavigationNewItem'
import AppSidebar from '@nextcloud/vue/dist/Components/NcAppSidebar'
import Multiselect from '@nextcloud/vue/dist/Components/NcMultiselect'
import DatetimePicker from '@nextcloud/vue/dist/Components/NcDatetimePicker'
import ActionButton from '@nextcloud/vue/dist/Components/NcActionButton'
import ActionInput from '@nextcloud/vue/dist/Components/NcActionInput'
import ActionSeparator from '@nextcloud/vue/dist/Components/NcActionSeparator'
import { showError, showSuccess } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { getCurrentUser } from '@nextcloud/auth'
import { translate as t } from '@nextcloud/l10n'

export default {
	name: 'App',
	components: {
		Content,
		AppContent,
		AppNavigation,
		AppNavigationItem,
		AppNavigationNewItem,
		AppSidebar,
		Multiselect,
		DatetimePicker,
		ActionButton,
		ActionInput,
		ActionSeparator,
	},
	data() {
		return {
			loading: false,
			show: false,
			rooms: [],
			selected: null,
			datetime_current: null,
			datetime_from: null,
			datetime_to: null,
			description: null,
			title: '',
			showRoomNameLabel: true,
			showRoomNameInput: false,
			showRoomOrderLabel: true,
			showRoomOrderInput: false,
			addButton: true,
			editButton: false,
			deleteButton: false,
			week: [t('reserveroom', 'Sunday'), t('reserveroom', 'Monday'), t('reserveroom', 'Tuesday'), t('reserveroom', 'Wednesday'), t('reserveroom', 'Thursday'), t('reserveroom', 'Friday'), t('reserveroom', 'Saturday')],
			cur_year: 1970,
			cur_mon: 1,
			cur_date: 1,
			cur_day: 4,
			isAdmin: false,
		}
	},
	computed: {
		sortedRooms() {
			return this.rooms.slice(0).sort((a, b) => {
				return a.sort_num - b.sort_num
			})
		},
	},
	mounted() {
		this.myScrollTo()
	},
	created() {
		this.printReserve(new Date())

		// 管理者権限のあるユーザなら会議室設定を表示
		if (getCurrentUser().isAdmin === true) {
			this.isAdmin = true
		}
	},
	methods: {
		myScrollTo() {
			document.getElementById('time-8').scrollIntoView({ behavior: 'smooth', block: 'start', inline: 'start' })
		},
		close() {
			this.show = false
		},
		mouseClick() {
			if (this.show === true) {
				this.show = false
			}
			this.title = t('reserveroom', 'Add Reserve')
			this.show = true
			this.addButton = true
			this.editButton = false
			this.deleteButton = false
			this.roomid = '0'
			this.selected = this.sortedRooms[0]
			this.roomid = '1'
			this.datetime_from = null
			this.datetime_to = null
			this.description = ''
		},
		getHeight(rStart, rEnd) {
			// 00:00
			const currentStart = new Date(this.cur_year, this.cur_mon - 1, this.cur_date, 0, 0, 0, 0)
			// currentStart.setHours(0, 0, 0, 0)

			// 24:00
			const currentEnd = new Date(this.cur_year, this.cur_mon - 1, this.cur_date, 0, 0, 0, 0)
			currentEnd.setDate(currentEnd.getDate() + 1)
			// currentEnd.setHours(0, 0, 0, 0)

			// 開始が00:00以前の場合、00:00にセット
			let calcStart
			if (rStart <= currentStart) {
				calcStart = 0
			} else {
				let min = 0
				if (rStart.getMinutes() >= 30) {
					min = 30
				}
				calcStart = 40 * (rStart.getHours() + (min / 60))
			}

			// 終了が24:00以前の場合、24:00にセット
			let calcEnd
			if (rEnd >= currentEnd) {
				calcEnd = 40 * 24
			} else {
				let hour = rEnd.getHours()
				let min = rEnd.getMinutes()
				if (min > 30) {
					hour += 1
					min = 0
				} else if (min > 0) {
					min = 30
				}
				calcEnd = 40 * (hour + (min / 60))
			}

			const ret = calcEnd - calcStart
			return ret
		},
		getTooltip(idx) {
			if (idx > 42) {
				return 'tooltips-up'
			}
			return 'tooltips'
		},
		getDateTime(rDate) {
			const month = rDate.getMonth() + 1
			const date = rDate.getDate()
			const hours = rDate.getHours()
			const minutes = rDate.getMinutes()

			return ('0' + month).slice(-2) + '/' + ('0' + date).slice(-2) + ' ' + ('0' + hours).slice(-2) + ':' + ('0' + minutes).slice(-2)
		},
		// ビジュアルから予約したい場所をクリック
		backgroundClick(roomid, n) {
			if (this.show === true) {
				this.show = false
			}
			this.title = t('reserveroom', 'Add Reserve')
			this.show = true
			this.addButton = true
			this.editButton = false
			this.deleteButton = false

			// 選択した会議室をプルダウンメニューから選択
			this.selected = this.search_selected(roomid)
			this.roomid = roomid
			const fhour = (n - 1) / 2
			const thour = fhour + 1
			let min = 0
			if ((n % 2) === 0) {
			    min = 30
			}
			this.datetime_from = new Date(this.cur_year, this.cur_mon - 1, this.cur_date, fhour, min, 0, 0)
			this.datetime_to = new Date(this.cur_year, this.cur_mon - 1, this.cur_date, thour, min, 0, 0)
			this.description = ''
		},
		// 予約をクリックして編集
		reserveClick(roomid, reserve) {
			if (reserve.owner !== getCurrentUser().uid && getCurrentUser().isAdmin === false) {
				if (this.show === true) {
					this.show = false
				}
				return true
			}
			if (this.show === true) {
				this.show = false
			}
			this.id = reserve.id
			this.title = t('reserveroom', 'Modify Reserve')
			this.addButton = false
			this.editButton = true
			this.deleteButton = true
			this.selected = this.search_selected(roomid)
			this.roomid = roomid
			this.datetime_from = reserve.start
			this.datetime_to = reserve.end
			this.description = reserve.desc
			this.show = true
		},
		// 会議室の追加(管理者のみ)
		async addRoom(value) {
			const ret = confirm(t('reserveroom', 'Add facility. \nAre you OK?'))

			// キャンセルが押された場合
			if (ret === false) {
				return true
			}
			const room = {
				facil_name: value,
			}

			// 会議室の追加
			try {
				const response = await axios.post(generateUrl('/apps/reserveroom/facility'), room)
				// lib側で正常系エラーが返ってきた場合は、メッセージを出す
				if (typeof response.data.message !== 'undefined') {
					showError(t('reserveroom', response.data.message))
					return false
				}
			} catch (e) {
				console.error(e)
				showError(t('reserveroom', 'Could not add facility.'))
				return false
			}

			this.rooms.push(room)
			this.printReserve(new Date(this.cur_year, this.cur_mon - 1, this.cur_date))
			showSuccess(t('reserveroom', 'Added facility.'))
		},
		// 会議室の削除(管理者のみ)
		async deleteRoom(id) {
			const ret = confirm(t('reserveroom', 'Delete reservation in facility before delete facility.'))
			// キャンセルが押された場合
			if (ret === false) {
				return true
			}

			// 会議室の削除
			try {
				const response = await axios.delete(generateUrl('/apps/reserveroom/facility/' + id))

				// lib側で正常系エラーが返ってきた場合は、メッセージを出す
				if (typeof response.data.message !== 'undefined') {
					showError(t('reserveroom', response.data.message))
					return false
				}
			} catch (e) {
				console.error(e)
				this.printReserve(new Date(this.cur_year, this.cur_mon - 1, this.cur_date))
				showError(t('reserveroom', 'Could not delete facility.'))
				return false
			}

			// 表示用のroomの配列から削除
			let i = 0
			for (const room of this.rooms) {
				if (room.id === id) {
					this.rooms.splice(i, 1)
					break
				}
				i++
			}

			showSuccess(t('reserveroom', 'Deleted facility.'))
		},
		openRoomNameInput(name) {
			this.showRoomNameLabel = false
			this.showRoomNameInput = true
		},
		// 会議室名の変更(管理者のみ)
		async saveRoomNameInput(event, room) {
			const ret = confirm(t('reserveroom', 'Modify facility name. \nAre you OK?'))
			// キャンセルが押された場合
			if (ret === false) {
				return true
			}
			const newName = event.target.querySelector('input[type=text]').value

			const reserve = {
				sort_num: room.sort_num,
				facil_name: newName,
			}

			// 会議室名の編集
			try {
				const response = await axios.put(generateUrl('/apps/reserveroom/facility/' + room.id), reserve)

				// lib側で正常系エラーが返ってきた場合は、メッセージを出す
				if (typeof response.data.message !== 'undefined') {
					showError(t('reserveroom', response.data.message))
					return false
				}
			} catch (e) {
				console.error(e)
				showError(t('reserveroom', 'Could not modify facility.'))
				return false
			}

			room.facil_name = newName

			this.showRoomNameLabel = true
			this.showRoomNameInput = false

			showSuccess(t('reserveroom', 'Modified facility name.'))
		},
		openRoomOrderInput() {
			this.showRoomOrderLabel = false
			this.showRoomOrderInput = true
		},
		// 表示順の変更(管理者のみ)
		async saveRoomOrderInput(event, room) {
			const ret = confirm(t('reserveroom', 'Modify order.\nAre you OK?'))
			// キャンセルが押された場合
			if (ret === false) {
				return true
			}
			const newOrder = event.target.querySelector('input[type=text]').value
			const reserve = {
				sort_num: newOrder,
				facil_name: room.facil_name,
			}

			// 会議室の表示順の編集
			try {
				const response = await axios.put(generateUrl('/apps/reserveroom/facility/' + room.id), reserve)

				// lib側で正常系エラーが返ってきた場合は、メッセージを出す
				if (typeof response.data.message !== 'undefined') {
					showError(t('reserveroom', response.data.message))
					return false
				}
			} catch (e) {
				console.error(e)
				showError(t('reserveroom', 'Could not modify order.'))
				return false
			}

			room.sort_num = Number(newOrder)

			this.showRoomOrderLabel = true
			this.showRoomOrderInput = false

			showSuccess(t('reserveroom', 'Modified order.'))
		},
		// 予約の追加
		async addReserve() {

			if (this.datetime_from === null) {
				showError(t('reserveroom', 'Please enter date. (From)'))
				return false
			}
			if (this.datetime_to === null) {
				showError(t('reserveroom', 'Please enter date. (To)'))
				return false
			}

			// 標準時間をYYYYMMDDhhmmssの形に変換
			const startDate = this.changeDateFormat(this.datetime_from)
			const endDate = this.changeDateFormat(this.datetime_to)

			const reserve = {
				facil_id: this.selected.id,
				start_date_time: startDate,
				end_date_time: endDate,
				memo: this.description,
			}

			// 予約の追加
			try {
				const response = await axios.post(generateUrl('/apps/reserveroom/reserve'), reserve)

				// lib側で正常系エラーが返ってきた場合は、メッセージを出す
				if (typeof response.data.message !== 'undefined') {
					showError(t('reserveroom', response.data.message))
					return false
				}
			} catch (e) {
				console.error(e)
				showError(t('reserveroom', 'Could not add reserve.'))
				return false
			}

			this.show = false
			this.printReserve(new Date(this.cur_year, this.cur_mon - 1, this.cur_date))

			showSuccess(t('reserveroom', 'Added reserve.'))
		},
		// 予約の編集
		async editReserve() {
			if (this.datetime_from === null) {
				showError(t('reserveroom', 'Please enter date. (From)'))
				return false
			}
			if (this.datetime_to === null) {
				showError(t('reserveroom', 'Please enter date. (To)'))
				return false
			}

			// 標準時間をYYYYMMDDhhmmssの形に変換
			const startDate = this.changeDateFormat(this.datetime_from)
			const endDate = this.changeDateFormat(this.datetime_to)

			const reserve = {
				facil_id: this.selected.id,
				start_date_time: startDate,
				end_date_time: endDate,
				memo: this.description,
			}
			// 予約の編集
			try {
				const response = await axios.put(generateUrl('/apps/reserveroom/reserve/' + this.id), reserve)

				// lib側で正常系エラーが返ってきた場合は、メッセージを出す
				if (typeof response.data.message !== 'undefined') {
					showError(t('reserveroom', response.data.message))
					return false
				}
			} catch (e) {
				console.error(e)
				showError(t('reserveroom', 'Could not modify reserve.'))
				return false
			}

			this.show = false
			this.printReserve(new Date(this.cur_year, this.cur_mon - 1, this.cur_date))

			showSuccess(t('reserveroom', 'Modified reserve.'))

		},
		async deleteReserve() {
			const ret = confirm(t('reserveroom', 'Delete reserve.\nAre you OK?'))
			// キャンセルが押された場合
			if (ret === false) {
				return true
			}

			// 予約の削除
			try {
				const response = await axios.delete(generateUrl('/apps/reserveroom/reserve/' + this.id))

				// lib側で正常系エラーが返ってきた場合は、メッセージを出す
				if (typeof response.data.message !== 'undefined') {
					showError(t('reserveroom', response.data.message))
					return false
				}
			} catch (e) {
				console.error(e)
				showError(t('reserveroom', 'Could not delete reserve.'))
				return false
			}

			this.show = false
			this.printReserve(new Date(this.cur_year, this.cur_mon - 1, this.cur_date))

			showSuccess(t('reserveroom', 'Deleted reserve.'))
		},
		// 予約の画面表示
		async printReserve(datetime) {
			// 表示日付を更新する
			this.cur_year = datetime.getFullYear()
			this.cur_mon = datetime.getMonth() + 1
			this.cur_date = datetime.getDate()
			this.cur_day = datetime.getDay()
			this.datetime_current = datetime

			// 会議室の一覧を取得する
			this.rooms = []

			try {
				const response = await axios.get(generateUrl('/apps/reserveroom/facility'))
				for (const d of response.data) {

					const room = {
						id: d.id,
						facil_name: d.facil_name,
						sort_num: d.sort_num,
						reserve: [],
					}

					this.rooms.push(room)
				}
			} catch (e) {
				console.error(e)
				showError(t('reserveroom', 'Could not get list of facility.'))
				return false
			}

			// 表示日の予約一覧を取得する
			try {
				const curDate = this.cur_year + '' + this.cur_mon.toString().padStart(2, '0') + '' + this.cur_date.toString().padStart(2, '0') + '000000'
				const response = await axios.get(generateUrl('/apps/reserveroom/reserve?date=' + curDate))
				for (const d of response.data) {
					for (const room of this.rooms) {
						// d.facil_idが文字列なので数値に変換して比較する
						if (room.id === Number([d.facil_id])) {
							let myself = 'reserve_myself'
							if (d.uid === getCurrentUser().uid) {
								myself = 'reserve_other'
							}

							let dispname = d.uid
							if (d.displayname !== null) {
								dispname = d.displayname
							}

							const startDatetime = new Date(d.start_date_time)
							const endDatetime = new Date(d.end_date_time)

							const idx = this.get_idx(startDatetime)

							room.reserve[idx] = {
								id: d.id,
								start: startDatetime,
								end: endDatetime,
								desc: d.memo,
								owner: d.uid,
								displayname: dispname,
								class: myself,
								tooltips: this.getTooltip(idx),
							}

							break
						}
					}
				}
			} catch (e) {
				console.error(e)
				showError(t('reserveroom', 'Could not get list of reservation.'))
				return false
			}

			// 再レンダリングするため
			this.show = true
			this.show = false

			return true
		},
		today() {
			const today = new Date()
			this.printReserve(today)
		},
		prevDay() {
			const prev = new Date(this.cur_year, this.cur_mon - 1, this.cur_date)
			prev.setDate(prev.getDate() - 1)
			this.printReserve(prev)
		},
		nextDay() {
			const next = new Date(this.cur_year, this.cur_mon - 1, this.cur_date)
			next.setDate(next.getDate() + 1)
			this.printReserve(next)
		},
		updateDate() {
			this.printReserve(this.datetime_current)
		},
		changeDateFormat(standard) {

			// 日付のフォーマットをYYYYMMDDhhmmssの形に変換
			const year = standard.getFullYear()
			const mon = standard.getMonth() + 1
			const date = standard.getDate()
			const hours = standard.getHours()
			const minutes = standard.getMinutes()

			const formatdate = year + '' + mon.toString().padStart(2, '0') + '' + date.toString().padStart(2, '0') + hours.toString().padStart(2, '0') + '' + minutes.toString().padStart(2, '0') + '' + '00'

			return formatdate
		},
		search_selected(id) {
			for (const r of this.rooms) {
				if (r.id === id) {
					return (r)
				}
			}
			return (null)
		},
		get_idx(start) {
			const curTop = new Date(this.cur_year, this.cur_mon - 1, this.cur_date, 0, 0, 0, 0)

			if (start < curTop) {
				return (1)
			}

			let idx = (start.getHours() * 2) + 1
			if (start.getMinutes() >= 30) {
				idx = idx + 1
			}

			return (idx)
		},
	},
}
</script>

<style>
.daily-reserve {
	box-sizing: border-box;
	margin: 40px 0 0 0;
}

.time-list {
	margin: 28px 0 20px 0;
}

.time-cell {
	width: 48px;
	height: 40px;
	text-align: center;
	vertical-align: top;
	color: grey;
	font-size: 90%;
	border-bottom: solid lightgray 1px;
}

.reserve-bg-cell {
	position: relative;
	min-width: 100px;
	height: 20px;
	border-bottom: solid lightgray 1px;
	margin: 0 0px;
}

.time-cell:nth-child(2n) {
	background: rgba(221,221,221,0.3);
}

.reserve-bg-cell:nth-child(4n+3) {
	background: rgba(221,221,221,0.3);
}

.reserve-bg-cell:nth-child(4n+4) {
	background: rgba(221,221,221,0.3);
}

.reserve-bg-cell:hover {
	background: rgba(250,165,165,0.3);
}

.reserve_other {
	width: 100%;
	text-align: center;
	/*padding-left: 5px;*/
	border: solid 1px white;
	border-radius: 8px; top: 0px;
	font-size: 90%;
	color: white;
	background-color:#f08080;
	position: absolute;
	z-index:1;
}

.reserve_myself {
	width: 100%;
	/*padding-left: 5px;*/
	border: solid 1px white;
	border-radius: 8px; top: 0px;
	font-size: 90%;
	color: white;
	background-color:#4169e1;
	position: absolute;
	z-index:1;
}

.nav-button {
	padding: 10px;
}

.nav-config {
	padding: 10px;
	height: 80%;
}

.reserve-header {
	position: fixed;
	background: #ffffff;
	width: 100%;
}

.reserve-date {
	font-size: 140%;
	padding: 10px 0 10px 60px;
}

.room-name {
	width: 100px;
	height: 40px;
	text-align: center;
}

.room-column {
	margin: 0 0 20px 0;
}

.sidebar {
	padding: 20px;
}

.description {
	width: 300px;
	height: 150px;
}

.tooltips {
	display: none;
	width: 180px;
	height: 60px;
	padding: 0.3em 0.5em;
	color: #FFFFFF;
	background: #000022;
	border-radius: 0.5em;
	text-align: left;
	position: absolute;
	top: 25px;
	left: 8px;
	font-size: 80%;
	z-index: 2;
}

.tooltips-up {
	display: none;
	width: 180px;
	height: 60px;
	padding: 0.3em 0.5em;
	color: #FFFFFF;
	background: #000022;
	border-radius: 0.5em;
	text-align: left;
	position: absolute;
	top: -60px;
	left: 8px;
	font-size: 80%;
	z-index: 2;
}

.tooltip-desc {
	width: 180px;
	overflow-wrap: normal;
}

.reserve-content {
	position: relative;
}

.reserve-content:hover .tooltips {
	display: block;
}

.reserve-content:hover .tooltips-up {
	display: block;
}

.reserve-tbl {
	/*width: 100%;*/
	border-collapse: separate;
	border-spacing: 0;
	table-layout: fixed;
}

.reserve-tbl th {
	text-align: center;
	height: 20px;
	position: -webkit-sticky;
	position: sticky;
	top: 0;
	z-index: 3;
}

.reserve-tbl th:first-child {
	position: -webkit-sticky;
	position: sticky;
	left: 0;
	z-index: 3;
}

.reserve-tbl td {
	text-align: center;
	height: 40px;
}

.column-time:first-child {
	border-left: none;
	border-top: none;
}

.column-time {
	width: 48px;
	border: solid 1px lightgray;
	background-color: white;
	text-align: right;
	padding-right: 2px;
	position: -webkit-sticky;
	position: sticky;
	left: 0;
	z-index: 4;
}

th.column-reserve {
	background: rgba(235,235,235,1);
}

.column-reserve {
	width: 100px;
	border: solid 1px lightgray;
	background-color: white;
}

.outer {
	width: 100%;
	height: 75vh;
	overflow: scroll;
	scrollbar-width: auto;
}

.nav-outer {
	width: 100%;
	height: 100%;
	overflow: auto;
	scrollbar-width: auto;
}
</style>
