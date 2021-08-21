<template>
    <div class="beaver-popups-manager-app wrap">
        <h2 class="beaver-popups-manager__title">Beaver Popups Manager</h2>

        <div class="beaver-popups-manager__header-container">
            <div class="beaver-popups-manager__header-disable-select-container">
                <label for="beaver-popups-manager__disable-popups">Enable/disable popups for site admins</label>
                <select name="beaver-popups-manager__disable-popups" id="beaver-popups-manager__disable-popups">
                    <option value="enable-for-admins">Enabled</option>
                    <option value="disable-for-admins">Disabled</option>
                </select>
            </div>
        </div>
        <spinners></spinners>
        <modals></modals>
        <router-view v-if="isInitialized"></router-view>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import { Spinners, Modals } from 'vue-chayka-bootstrap'

    export default {
        name: 'App',
        components: {
            Spinners,
            Modals
        },
        computed: {
            isInitialized () {
                return !!(
                    this.popups &&
                    this.siteSetup &&
                    this.cptSetup &&
                    this.postsSetup &&
                    this.postsExclusionSetup
                )
            },

            ...mapState([
                /**
                 * List of available popups
                 */
                'popups',

                /**
                 * Site popups setup
                 */
                'siteSetup',

                /**
                 * Custom post types setup
                 */
                'cptSetup',

                /**
                 * Individual posts setup
                 */
                'postsSetup',

                /**
                 * Posts Exclusion Setup
                 */
                'postsExclusionSetup'

            ])
        },
        mounted () {
            /**
             * Load all the required data on start
             */
            this.$store.dispatch('loadPopups')
            this.$store.dispatch('loadSiteSetup')
            this.$store.dispatch('loadCustomPostTypesSetup')
            this.$store.dispatch('loadIndividualPostsSetup')
            this.$store.dispatch('loadIndividualPostsExclusionSetup')
        }
    }
</script>
<style lang="less" scoped>
    .beaver-popups-manager__header-container {
        display: flex;
        justify-content: flex-end;
        display: none;
    }

    .chayka-modals-fader.chayka-modals-fader {
        background-color: rgba(0,0,0,0.5);
        z-index: 100001;

        > .modals-modal {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 1px solid #EEE;
            margin: 0;

            > .modal_header {
                padding-top: 5px;
                padding-bottom: 5px;

                > .modal_header-close {
                    top: 50%;
                    transform: translateY(-50%);
                    font-size: 26px;
                }
            }

            .modal_body {
                h3 {
                    margin-bottom: 0.5rem;
                }

                .chayka-form-field + h3 {
                    margin-top: 2rem;
                }
            }
        }

        .chayka-form-field > .field-input > .input-box > input,
        .drop-down-selector > .drop-down-selected-option,
        .drop-down-selector > .drop-down > .drop-down-wrap {
            box-shadow: none;
            border: 1px solid #EEE;
        }

        .chayka-form-field label {
            min-height: auto;
            font-size: 14px;
            line-height: 2;
        }
    }

    .chayka-spinners.chayka-spinners {
        & > .spinners {
            z-index: 100010;
        }
    }
</style>
