<template>
    <div class="popup-exclusion-list-table">
        <div class="popup-exclusion-list-table__container">
            <table v-if="data">
                <thead>
                    <tr>
                        <th>Pages</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="item-title__table-cell">
                            <div class="item__title-container">
                                <div v-for="(entry, id) in data" :key="id">
                                    <span class="item__title" v-html="entry.title"></span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="popup-exclusion-list-buttons__container">
                <button class="button button-primary button-large" v-if="canEdit && isEmpty(data)" @click="addClicked">Add item</button>
                <button class="button button-primary button-large" v-if="canEdit && !isEmpty(data)" @click="editClicked">Update item</button>
            </div>
        </div>
    </div>
</template>
<script>
    import bus from '../bus'
    import config from '../config'
    import isEmpty from 'lodash.isempty'

    export default {
        name: 'PopupExclusion',
        props: {
            data: {
                default: [],
                type: Array
            },
            canEdit: {
                default: false
            }
        },
        computed: {
            siteUrl () {
                return config.siteUrl
            }
        },
        methods: {
            addClicked () {
                bus.$emit('PopupExclusionSetup.add', {
                    subject: []
                })
            },
            editClicked () {
                const self = this
                const popupExclusionPreSelected = self.data.map(post => {
                    return {
                        label: post.title,
                        value: post.id
                    }
                })
                bus.$emit('PopupExclusionSetup.edit', {
                    subject: popupExclusionPreSelected || []
                })
            },
            isEmpty (obj) {
                return isEmpty(obj)
            }
        }
    }
</script>
<style lang="less">
    .popup-exclusion-list-table__container {
        table {
            table-layout: fixed;
            width: 100%;
            background-color: white;
            border: 1px solid #EEE;
            border-radius: 4px;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);


            thead {
                border-bottom: 2px solid #EEE;;
            }

            tbody {
                & > :nth-child(odd) {
                    background-color: #f9f9f9;
                }
            }

            th, td {
                padding: 0.8em;
            }

            th {
                text-align: left;

                &:first-child {
                    width: 220px;
                }
            }

            td {
                .item__title-container {
                    display: flex;
                    flex-flow: row wrap;
                    justify-content: flex-start;
                }
            }

            .item__title {
                white-space: normal;
                padding: 10px;
            }
        }

        .popup-exclusion-list-buttons__container {
            text-align: right;
            margin-top: 1em;
        }

    }
</style>
