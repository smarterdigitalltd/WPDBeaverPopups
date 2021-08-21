<template>
    <div class="popup-list-table">
        <div class="popup-list-table__container">
            <table rules="all">
                <thead>
                    <tr>
                        <th>Scope</th>
                        <th>On Entrance</th>
                        <th>On Exit</th>
                        <th>On Scroll</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-if="data" v-for="(entry, id) in data" :key="id">
                        <td class="item-title__table-cell">
                            <div class="item__title-container">
                                <span class="item__title" v-html="entry.title"></span>
                                <span v-if="canEdit" class="item__action-icon dashicons-before dashicons-trash" @click="$emit('remove', entry)"></span>
                            </div>
                        </td>

                        <td>
                            <popup-list-item :setup="entry.rules.entrance" trigger="entrance" :scope="scope" :subject="id"></popup-list-item>
                        </td>

                        <td>
                            <popup-list-item :setup="entry.rules.exit" trigger="exit" :scope="scope" :subject="id"></popup-list-item>
                        </td>

                        <td>
                            <popup-list-item :setup="entry.rules.scroll" trigger="scroll" :scope="scope" :subject="id"></popup-list-item>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="popup-list-buttons__container">
                <button class="button button-primary button-large" v-if="canEdit" @click="$emit('add', scope)">Add item</button>
            </div>
        </div>
    </div>
</template>

<script>
    import PopupListItem from './PopupListItem.vue'

    export default {
        name: 'PopupList',
        components: {
            PopupListItem
        },
        props: {
            data: {
                default: null,
                type: Object
            },
            scope: {
                default: 'site',
                type: String
            },
            canEdit: {
                default: false
            }
        }
    }
</script>

<style lang="less">
    .popup-list-table__container {
        table {
            table-layout: fixed;
            width: 100%;
            background-color: white;
            border: 1px solid #EEE;
            border-radius: 4px;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);


            thead {
                /*box-shadow: 0 1px 4px 0 #ccc;*/
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
                    align-items: center;
                    flex-direction: row;
                    justify-content: space-between;
                }
            }

            .item__title {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        }

        .popup-list-buttons__container {
            text-align: right;
            margin-top: 1em;
        }

    }
</style>
