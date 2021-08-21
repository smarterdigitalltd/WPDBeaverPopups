<template>
    <modal ref="modal"
           :title="popupOptionsTitle"
           :buttons="[{text: 'Cancel', cls: 'button-secondary'}, {text: 'Save', click: saveClicked, cls: 'button-primary', persist: true}]"
           :width="'600px'"
           class="popup-setup-dialog">
        <form-validator ref="validator">
            <form-field name="trigger" label="Trigger event" validate-required="Please select trigger event"
                        v-if="false">
                <drop-down-selector :data="[
                        {value: 'entrance', label: 'On entrance delay'},
                        {value: 'exit', label: 'On exit intent'},
                        {value: 'scroll', label: 'On scroll depth'}
                    ]" :value="trigger" @select="trigger = $event"/>
                <input type="hidden" v-model="trigger"/>
            </form-field>

            <h3>Basic Popup Setup</h3>

            <form-field name="popupId" label="Select popup" validate-required>
                <drop-down-selector :data="popupOptions"
                                    :empty="{value: 0, label: 'Select popup...'}"
                                    :value="popupId"
                                    @select="popupId = $event"
                                    :need-search="true"/>
                <input type="hidden" v-model="popupId"/>
            </form-field>

            <h3>Popup Display Options</h3>

            <form-field name="delay" label="Popup entrance delay (seconds)" v-if="trigger ==='entrance'" :validate-range="{ge: 0}">
                <input type="number" v-model="delay" min="0"/>
            </form-field>

            <form-field name="depth" label="Scroll depth (%)" v-if="trigger ==='scroll'" :validate-range="{ge: 0, le: 100}">
                <input type="number" v-model="depth" min="0" max="100"/>
            </form-field>

            <form-field name="displayPopupMaximumTimes" label="Display this popup a maximum number of times to a visitor (0 = unlimited)" :validate-range="{ge: 0}">
                <input type="number" v-model="displayPopupMaximumTimes" min="0"/>
            </form-field>

            <form-field name="hidePopupForNumberOfDays" label="After, hide for a number of days (up to 730)" :validate-range="{ge: 1, le: 730}" v-if="displayPopupMaximumTimes">
                <input type="number" v-model="hidePopupForNumberOfDays" min="1"/>
            </form-field>

            <h3>Advanced Options</h3>

            <form-field name="cookieTrigger" label="Set cookie when popup opens/closes." validate-required="Please select trigger">
                <div class="popup-setup-dialog__info">Setting a cookie when the popup closes is useful when you want the website visitor to acknowledge something in the popup, like age or acceptance of terms.</div>
                <drop-down-selector :data="[
                        {value: 'open', label: 'On open'},
                        {value: 'close', label: 'On close'}
                    ]"
                    :value="cookieTrigger"
                    @select="cookieTrigger = $event"/>
                <input type="hidden" v-model="cookieTrigger"/>
            </form-field>
        </form-validator>
    </modal>
</template>

<script>
    import { mapState, mapActions } from 'vuex'
    import { Modal, FormField, FormValidator } from 'vue-chayka-bootstrap'
    import DropDownSelector from './DropDownSelector.vue'

    export default {
        name: 'PopupSetupDialog',
        components: {
            Modal,
            DropDownSelector,
            FormField,
            FormValidator
        },
        data () {
            return {
                scope: '',
                subject: '',
                trigger: 'entrance',
                popupId: 0,
                delay: 0,
                depth: 0,
                displayPopupMaximumTimes: 0,
                hidePopupForNumberOfDays: 0,
                cookieTrigger: 'open'
            }
        },
        computed: {
            ...mapState([
                'popups'
            ]),
            popupOptions () {
                return (this.popups || []).map(p => ({ value: p.id, label: p.title }))
            },
            popupOptionsTitle () {
                return this.scope && this.popupId ? 'Edit Popup' : 'Add Popup'
            }
        },
        methods: {
            ...mapActions([
                'setupPopupTrigger'
            ]),
            open (payload) {
                this.scope = payload.scope
                this.subject = payload.subject
                this.trigger = payload.trigger
                this.popupId = payload.setup && payload.setup.id || 0
                this.delay = 5
                this.depth = 50
                this.displayPopupMaximumTimes = payload.setup && payload.setup.displayPopupMaximumTimes || 0
                this.hidePopupForNumberOfDays = payload.setup && payload.setup.hidePopupForNumberOfDays || 7
                this.cookieTrigger = payload.setup && payload.setup.cookieTrigger || 'open'

                switch (this.trigger) {
                case 'entrance':
                    this.delay = payload.setup && payload.setup.delay || 5
                    break
                case 'scroll':
                    this.depth = payload.setup && payload.setup.depth || 50
                    break
                }
                this.$refs.modal.open()
            },
            saveClicked () {
                let setup = {
                    displayPopupMaximumTimes: this.displayPopupMaximumTimes,
                    hidePopupForNumberOfDays: this.hidePopupForNumberOfDays,
                    cookieTrigger: this.cookieTrigger
                }

                switch (this.trigger) {
                case 'entrance':
                    setup.delay = this.delay
                    break
                case 'scroll':
                    setup.depth = this.depth
                    break
                }

                this.setupPopupTrigger({
                    scope: this.scope,
                    subject: this.subject,
                    trigger: this.trigger,
                    id: this.popupId,
                    setup: setup,
                    validator: this.$refs.validator,
                    modal: this.$refs.modal,
                    callback: () => this.$refs.modal.close()
                })
            }
        }
    }
</script>
<style lang="less">
    .popup-setup-dialog {
        .drop-down-selector {
            margin-bottom: 1em;
        }

        &__info {
            background: lightgrey;
            padding: 5px;
            margin-bottom: 10px;
        }
    }
</style>
