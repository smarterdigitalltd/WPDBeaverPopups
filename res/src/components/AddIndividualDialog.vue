<template>
    <modal ref="modal" title="Add individual page or post..."
           :buttons="[{text: 'Cancel', cls: 'button-secondary'}, {text: 'Save', click: saveClicked, cls: 'button-primary', persist: true}]"
           class="add-individual-dialog">
        <form-validator ref="validator">
            <form-field name="popupId" validate-required>
                <drop-down-selector :empty="{value: 0, label: 'Search for individual item...'}" :value="postId"
                                    @select="postId = $event" :need-search="true"
                                    url="/api/wpd/beaver-popups/search-posts/?q={query}&limit={limit}"
                                    :transform="post => ({value: post.id, label: post.title})"/>
                <input type="hidden" v-model="postId"/>
            </form-field>
        </form-validator>
    </modal>
</template>

<script>
    import { mapActions } from 'vuex'
    import { Modal, FormField, FormValidator } from 'vue-chayka-bootstrap'
    import DropDownSelector from './DropDownSelector.vue'

    export default {
        name: 'AddIndividualDialog',
        components: {
            Modal,
            DropDownSelector,
            FormField,
            FormValidator
        },
        data () {
            return {
                scope: '',
                postId: 0
            }
        },
        methods: {
            ...mapActions([
                'addIndividualPost'
            ]),
            open () {
                this.postId = 0
                this.$refs.modal.open()
            },
            saveClicked () {
                this.addIndividualPost({
                    postId: this.postId,
                    validator: this.$refs.validator,
                    modal: this.$refs.modal,
                    callback: () => this.$refs.modal.close()
                })
            }
        }
    }
</script>
