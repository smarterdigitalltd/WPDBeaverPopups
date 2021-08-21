<template>
    <div class="item-popup__container" :class="{isConfigured: !!popup}">
        <div class="item-popup__name">
            <a :href="siteUrl + '/?wpd-bb-popup=' + popup.name + '&fl_builder'" class="popup-title" v-if="popup">{{popup.title}}</a>
            <span class="item-popup__trigger-info trigger-delay" v-if="trigger === 'entrance' && setup">(Delay: {{setup.delay}} sec)</span>
            <span class="item-popup__trigger-info trigger-depth" v-if="trigger === 'scroll' && setup">(Scroll depth: {{setup.depth}}%)</span>
        </div>

        <div class="item-popup__actions">
            <span class="item-popup__action-icon dashicons-before dashicons-plus" v-if="!popup" @click="addClicked()"></span>
            <span class="item-popup__action-icon dashicons-before dashicons-admin-generic" v-if="popup" @click="editClicked()"></span>
            <span class="item-popup__action-icon dashicons-before dashicons-trash" v-if="popup" @click="deleteClicked()"></span>
        </div>
    </div>
</template>

<script>
    import { mapState, mapActions } from 'vuex'
    import bus from '../bus'
    import { modals } from 'vue-chayka-bootstrap'
    import config from '../config'

    export default {
        name: 'PopupListItem',
        props: {
            setup: {
                default: null,
                type: Object
            },
            scope: {
                type: String
            },
            subject: {
                default: 'site',
                type: String
            },
            trigger: {
                default: 'open',
                type: String
            }
        },
        computed: {
            ...mapState([
                'popups'
            ]),
            popup () {
                return this.setup && this.setup.id && this.popups.find(popup => popup.id === this.setup.id)
            },
            siteUrl () {
                return config.siteUrl
            }
        },
        methods: {
            ...mapActions([
                'setupPopupTrigger'
            ]),
            addClicked () {
                bus.$emit('PopupSetup.add', {
                    scope: this.scope,
                    subject: this.subject,
                    trigger: this.trigger
                })
            },
            editClicked () {
                bus.$emit('PopupSetup.edit', {
                    scope: this.scope,
                    subject: this.subject,
                    trigger: this.trigger,
                    setup: this.setup
                })
            },
            deleteClicked () {
                modals.confirm(`Are you sure you want to remove the popup for ${this.trigger} event?`, 'Confirm...', null, {
                    buttons: [
                        {
                            text: 'No'
                        },
                        {
                            text: 'Yes',
                            click: () => {
                                this.setupPopupTrigger({
                                    scope: this.scope,
                                    subject: this.subject,
                                    trigger: this.trigger,
                                    id: 0
                                })
                            },
                            cls: 'button-primary'
                        }
                    ]
                })
            }
        }
    }
</script>

<style lang="less">
    .item-popup__container {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;

        &.isConfigured {
            /*justify-content: stretch;*/
        }

        .item-popup__name {
            font-weight: bold;
            max-width: 75%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .item-popup__trigger-info {
            display: block;
            font-size: 80%;
            font-style: italic;
            font-weight: normal;
            color: #333;

            &.trigger-delay {

            }

            &.trigger-depth {

            }
        }

        .item-popup__actions {
            display: flex;
        }

        .item-popup__action-icon {
            white-space: nowrap;
            flex: 0;
            cursor: pointer;

            &:not(:first-child) {
                margin-left: 0.5em;
            }
        }
    }
</style>
