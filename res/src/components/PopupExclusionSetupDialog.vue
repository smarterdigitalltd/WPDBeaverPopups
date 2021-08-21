<template>
    <modal ref="modal"
           :title="popupExclusionSetupTitle"
           :buttons="[{text: 'Cancel', cls: 'button-secondary'}, {text: 'Save', click: saveClicked, cls: 'button-primary', persist: true}]"
           :width="'600px'"
           class="popup-exclusion-setup-dialog">
        <form-validator ref="validator">
            <h3>Popup Exclusion Setup</h3>
            <v-select multiple
                      ref="select"
                      v-model="popupExclusions"
                      :options="pageOptions"
                      :filterable="false"
                      label="label"
                      @search="onSearch">
                <template slot="no-options">
                    Type to exclude posts/pages
                </template>
                <template slot="option" slot-scope="option">
                    <div class="d-center">
                        {{ option.label }}
                    </div>
                </template>
                <template slot="selected-option" slot-scope="option">
                    <div class="selected d-center">
                        {{ option.label }}
                    </div>
                </template>
            </v-select>
        </form-validator>
    </modal>
</template>
<script>
    import Vue from 'vue'
    import { mapActions } from 'vuex'
    import { Modal, FormField, FormValidator, ajax } from 'vue-chayka-bootstrap'
    import vSelect from 'vue-select'
    import config from '../config'
    import debounce from 'lodash/debounce'

    export default {
        name: 'PopupExclusionSetupDialog',
        components: {
            Modal,
            FormField,
            FormValidator,
            vSelect
        },
        data () {
            return {
                scope: 'postExclusion',
                popupExclusions: [],
                pageOptions: [],
                limit: 10,
                siteUrl: config.siteUrl,
                debounceTime: 350
            }
        },
        computed: {
            popupExclusionSetupTitle () {
                return this.scope && this.popupExclusions ? 'Update individual page or post exclusion...' : 'Add individual page or post exclusion...'
            }
        },
        methods: {
            ...mapActions([
                'setupIndividualPostsExclusionPopup'
            ]),
            open (payload) {
                this.popupExclusions = payload.subject || []
                this.$refs.modal.open()
            },
            saveClicked () {
                const popupExclusion = this.popupExclusions.map(popupExclusion => {
                    return popupExclusion.value
                })

                this.setupIndividualPostsExclusionPopup({
                    scope: this.scope,
                    subject: popupExclusion,
                    validator: this.$refs.validator,
                    modal: this.$refs.modal,
                    callback: () => this.$refs.modal.close()
                })
            },
            onSearch (query, loading) {
                loading(true)
                this.searchPosts(query, loading)
            },
            searchPosts (query, loading) {
                let limit = this.limit
                const popupExclusion = this.popupExclusions.map(popupExclusion => {
                    return popupExclusion.value
                })
                ajax.get(Vue.url(this.siteUrl + '/api/wpd/beaver-popups/search-posts/?q={query}&limit={limit}', {query, limit}), {}).then(
                    ({body}) => {
                        this.pageOptions = body.payload.map(post => ({value: post.id, label: post.title}))
                            .filter(post => !popupExclusion.includes(post.value))
                        loading(false)
                    }
                )
            }
        },
        created () {
            this.searchPosts = debounce(this.searchPosts, this.debounceTime, { leading:false, trailing:true })
        }
    }
</script>
<style lang="less">
    .popup-exclusion-setup-dialog {
        .v-select .selected-tag {
            padding: 0.25em 1.25em;
        }
    }
</style>
