<template>
    <div class="drop-down-selector" :class="{open: isOpen}">
        <div class="drop-down-selected-option" @click="isOpen ? close() : open()" tabindex="-1" ref="optionContainer"
             @keyup.esc.stop="" @keydown.up.prevent="moveUp()" @keydown.down.prevent="moveDown()" @keydown.enter="select()">
            <drop-down-option :model="model || empty"/>
            <div class="drop-down-arrow">&bigtriangledown;</div>
        </div>
        <div class="drop-down" v-if="isOpen">
            <div class="drop-down-wrap">
                <input ref="searchBox" type="search" v-if="needSearch" v-model="query" placeholder="Search..."
                       @keyup="search()"
                       @keyup.esc.stop="close()" @keydown.up.prevent="moveUp()" @keydown.down.prevent="moveDown()" @keydown.enter="select()">
                <div class="drop-down-options">
                    <drop-down-option :model="empty" v-if="allowEmpty"/>
                    <drop-down-option v-for="(m, index) in models" :key="m.value" :model="m" :class="{selected: index === selectedIndex}" @click="select(index)"/>
                    <div v-if="!models.length" class="not-found">No options meet search query</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Vue from 'vue'
    import DropDownOption from './DropDownOption.vue'
    import { ajax } from 'vue-chayka-bootstrap'
    import config from '../config'

    export default {
        name: 'DropDownSelector',
        components: {
            DropDownOption
        },
        props: {
            value: {
                default: ''
            },
            data: {
                default: () => [],
                type: Array
            },
            empty: {
                default: () => {
                    return {
                        value: '',
                        label: 'Select...',
                        img: ''
                    }
                },
                type: Object
            },
            needSearch: {
                default: false
            },
            allowEmpty: {
                default: false
            },
            url: {
                default: '',
                type: String
            },
            limit: {
                default: 10,
                type: Number
            },
            debounceTime: {
                default: 500,
                type: Number
            },
            transform: {
                default () {
                    return i => i
                },
                type: Function
            }
        },
        data () {
            return {
                siteUrl: config.siteUrl,
                rows: [],
                isOpen: false,
                selectedIndex: -1,
                query: '',
                lastRequestedQuery: '',
                timerHandle: null
            }
        },
        computed: {
            models () {
                return (!this.url && this.data || this.rows).filter(row => !this.query || !!this.url || row.label.toLowerCase().indexOf(this.query.toLowerCase()) >= 0)
            },
            model () {
                return (!this.url && this.data || this.rows).find(model => model.value === this.value)
            }
        },
        methods: {
            open () {
                this.isOpen = true
                window.setTimeout(() => {
                    if (this.needSearch) {
                        this.$refs.searchBox.focus()
                    } else {
                        this.$refs.optionContainer.focus()
                    }
                }, 100)
            },
            close () {
                this.isOpen = false
            },
            moveUp () {
                this.selectedIndex = Math.max(0, this.selectedIndex - 1)
            },
            moveDown () {
                this.selectedIndex = Math.min(this.selectedIndex + 1, this.models.length - 1)
            },
            search () {
//                this.selectedIndex = Math.max(this.selectedIndex, this.models.length - 1)
                if (!this.url) {
                    /**
                     * Non http api call search
                     */
                    this.selectedIndex = Math.min(this.selectedIndex, this.models.length - 1)
                    if (this.selectedIndex === -1 && this.models.length) {
                        this.selectedIndex = 0
                    }
                } else if (this.lastRequestedQuery !== this.query) {
                    /**
                     * http api call search
                     */
                    if (this.timerHandle) {
                        window.clearTimeout(this.timerHandle)
                    }
                    this.timerHandle = window.setTimeout(() => {
                        this.lastRequestedQuery = this.query
                        let query = this.query
                        let limit = this.limit
                        ajax.get(Vue.url(this.siteUrl + this.url, {query, limit}), {}).then(
                            ({body}) => {
                                this.rows = this.transform ? body.payload.map(this.transform) : body.payload
                                this.selectedIndex = 0
                            }
                        )
                    }, this.debounceTime)
                }
            },
            select (index = -1) {
                if (index >= 0) {
                    this.selectedIndex = index
                }
                let model = this.models[this.selectedIndex]
                this.$emit('select', model.value, model)
//                this.query = ''
                this.isOpen = false
            }
        }
    }
</script>

<style lang="less">
    .drop-down-selector{
        /*border: 1px solid black;*/
        overflow: visible;
        & > .drop-down-selected-option {
            border: 1px solid black;
            cursor: pointer;
            position: relative;
            padding-right: 3em;
            & > .drop-down-option{
            }
            & >.drop-down-arrow {
                position: absolute;
                top: 1.25ex;
                right: 1em;
            }
        }
        & > .drop-down{
            position: relative;
            z-index: 100;
            max-height: 0;
            & > .drop-down-wrap{
                background-color: white;
                border: 1px solid black;
                border-top: none;
                display: flex;
                flex-direction: column;
                align-items: stretch;
                & > input {
                    margin: 1ex;
                }
                & > .drop-down-options{
                    max-height: 250px;
                    overflow: scroll;
                    & > .drop-down-option{
                        &:hover{
                            background-color: lightblue;
                        }
                        &.selected{
                            background-color: lightgray;
                        }
                    }
                    & > .not-found{
                        padding: 1ex 1em;
                    }
                }

            }
        }
        &.open > .drop-down {
            /*display: block;*/
        }

    }
</style>
