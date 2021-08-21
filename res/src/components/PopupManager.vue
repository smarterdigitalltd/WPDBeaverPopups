<template>
    <div class="popup-lists">
        <div class="popup-list__container">
            <h3>Sitewide Popups</h3>
            <popup-list :data="siteSetup" scope="site"></popup-list>
        </div>

        <div class="popup-list__container">
            <h3>Post Types Popups</h3>
            <popup-list :data="cptSetup" scope="cpt"></popup-list>
        </div>

        <div class="popup-list__container">
            <h3>Single Pages/Post Popups</h3>
            <popup-list :data="postsSetup" scope="post"
                        :can-edit="true"
                        @add="$refs.addIndividualDialog.open()"
                        @remove="confirmRemoveIndividualPost($event)"></popup-list>
            <add-individual-dialog ref="addIndividualDialog"></add-individual-dialog>
        </div>

        <div class="popup-list__container">
            <h3>Single Pages/Post Popups Exclusion</h3>
            <popup-exclusion :data="postsExclusionSetup"
                             :can-edit="true"></popup-exclusion>
        </div>

        <popup-setup-dialog ref="popupSetupDialog"></popup-setup-dialog>
        <popup-exclusion-setup-dialog ref="popupExclusionSetupDialog"></popup-exclusion-setup-dialog>
    </div>
</template>

<script>
    import { mapState, mapActions } from 'vuex'
    import PopupList from './PopupList.vue'
    import PopupSetupDialog from './PopupSetupDialog.vue'
    import AddIndividualDialog from './AddIndividualDialog.vue'
    import PopupExclusion from './PopupExclusion.vue'
    import PopupExclusionSetupDialog from './PopupExclusionSetupDialog.vue'
    import { modals } from 'vue-chayka-bootstrap'
    import bus from '../bus'

    export default {
        name: 'PopupManager',
        components: {
            PopupList,
            PopupSetupDialog,
            AddIndividualDialog,
            PopupExclusion,
            PopupExclusionSetupDialog
        },
        computed: {
            ...mapState([
                'popups', 'siteSetup', 'cptSetup', 'postsSetup', 'postsExclusionSetup'
            ])
        },
        created () {
            bus.$on('PopupSetup.add', (payload) => {
                this.$refs.popupSetupDialog.open(payload)
            })
            bus.$on('PopupSetup.settings', (payload) => {
                this.$refs.popupSettingsDialog.open(payload)
            })
            bus.$on('PopupSetup.edit', (payload) => {
                this.$refs.popupSetupDialog.open(payload)
            })
            bus.$on('PopupExclusionSetup.add', (payload) => {
                this.$refs.popupExclusionSetupDialog.open(payload)
            })
            bus.$on('PopupExclusionSetup.edit', (payload) => {
                this.$refs.popupExclusionSetupDialog.open(payload)
            })
        },
        methods: {
            ...mapActions([
                'removeIndividualPost'
            ]),
            confirmRemoveIndividualPost (post) {
                modals.confirm(`Remove "${post.title}"?`, 'Confirm...', () => {
                    let postId = post.id
                    this.removeIndividualPost({ postId })
                })
            }
        }
    }
</script>

<style lang="less">
    .popup-list__container:not(:first-child) {
        margin-top: 3em;
    }
</style>
