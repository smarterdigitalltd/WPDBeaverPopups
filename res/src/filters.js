import moment from 'moment'

export let momentFormat = (value, format = 'MMMM Do YYYY, h:mm:ss a') => moment(value).format(format)

export let momentFromNow = (value) => moment(value).fromNow()

export let momentCalendar = (value) => moment(value).calendar()

export let truncate = (value, length, suffix = '...') => {
    return value.split(/\s+/).reduce(function (result, token, index, tokens) {
        return result.length < length
            ? result + (result.length ? ' ' + token : token)
            : result + (index + 1 === tokens.length ? suffix : '')
    }, '')
}
