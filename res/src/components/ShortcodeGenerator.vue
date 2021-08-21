<template>
    <div class="wpd-beaver-popups__settings-wrap wrap">
        <h1 class="wpd-beaver-popups__settings-sub-title mb-20">Beaver Popups Shortcodes Generator</h1>
        <spinners></spinners>
        <modals></modals>
        <div v-if="isInitialized">
            <hr class="wp-header-end">
            <p>
                <strong>Generate a shortcode to open a popup from a link</strong>
            </p>
            <p>
                This shortcode allows you to open a popup from text and image links in the WordPress editor.
            </p>
            <p>
                <pre v-show="popupEnabledLinkItems.popups.selected&&popupEnabledLinkItems.text">
                    <code>{{ popupEnabledLinkItemsShortcodes }}</code>
                </pre>
            </p>

            <div class="row mt-2">
                <div class="col-4">
                    <p>Select Popup</p>
                </div>
                <div class="col-8">
                    <b-form-select
                            v-model="popupEnabledLinkItems.popups.selected"
                            :options="popupOptions"
                            class="wpd__custom-select">
                        <template slot="first">
                            <option :value="null" disabled>-- Please select popup --</option>
                        </template>
                        <div class="drop-down-arrow">&bigtriangledown;</div>
                    </b-form-select>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-4">
                    <p>Link Text</p>
                </div>
                <div class="col-8">
                    <b-form-input
                            v-model="popupEnabledLinkItems.text"
                    ></b-form-input>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-4">
                    <p>Link Title (Optional)</p>
                </div>
                <div class="col-8">
                    <b-form-input
                            v-model="popupEnabledLinkItems.title"
                    ></b-form-input>
                </div>
            </div>
            <a
                    v-clipboard:copy="popupEnabledLinkItemsShortcodes"
                    v-clipboard:success="shortcodeCopied"
                    class="wpd-small-link mt-2 float-right"
                    href="javascript:void(0);"
                    @click.prevent
                    v-show="popupEnabledLinkItems.popups.selected&&popupEnabledLinkItems.text">Copy shortcode to clipboard</a>
        </div>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import { Spinners, Modals } from 'vue-chayka-bootstrap'
    export default {
        name: 'PopupShortcodeGenerator',
        data () {
            return {
                popupEnabledLinkItems: {
                    title: '',
                    popups: {
                        selected: null
                    },
                    text: ''
                }
            }
        },
        components: {
            Spinners,
            Modals
        },
        methods: {
            shortcodeCopied (e) {
                const originalText = e.trigger.innerText
                e.trigger.innerText = 'Copied...'
                e.trigger.classList.add('non-link')

                setTimeout(() => {
                    e.trigger.innerText = originalText
                    e.trigger.classList.remove('non-link')
                }, 2000)
            }
        },

        computed: {
            isInitialized () {
                return !!(this.popups)
            },
            ...mapState([
                'popups'
            ]),
            popupOptions () {
                return (this.popups || []).map(popup => ({ value: popup.id, text: popup.title }))
            },
            popupEnabledLinkItemsShortcodesTitle () {
                return this.popupEnabledLinkItems.title ? ` title="${this.popupEnabledLinkItems.title}"` : ''
            },
            popupEnabledLinkItemsShortcodeslink () {
                return this.popupEnabledLinkItems.popups.selected ? ` id="${this.popupEnabledLinkItems.popups.selected}"` : ''
            },
            popupEnabledLinkItemsShortcodes () {
                return `[wpd_beaver_popups_link${this.popupEnabledLinkItemsShortcodesTitle}${this.popupEnabledLinkItemsShortcodeslink}]${this.popupEnabledLinkItems.text}[/wpd_beaver_popups_link]`
            }
        },
        mounted () {
            /**
             * Load all the required data on start
             */
            this.$store.dispatch('loadPopups')
        }
    }
</script>
