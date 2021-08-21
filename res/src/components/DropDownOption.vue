<template>
    <div class="drop-down-option" :class="{empty: !model.value}" @click="$emit('click', model)">
        <img v-if="model.img" :src="model.img"/>
        <span v-html="model.label"></span>
    </div>
</template>

<script>
    export default {
        name: 'DropDownOption',
        props: {
            model: {
                default: null,
                type: Object
            }
        }
    }
</script>

<style lang="less">
    .drop-down-option{
        cursor: pointer;
        padding: 1ex 1em;
        display: flex;
        flex-direction: row;

        & > img{

        }

        & > span{

        }

    }
</style>
