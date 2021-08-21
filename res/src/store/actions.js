import Vue from 'vue'
import VueResource from 'vue-resource'
import config from '../config'
import { ajax } from 'vue-chayka-bootstrap'

Vue.use(VueResource)

/**
 * Load available popups
 * @param store
 * @return {*|Promise.<TResult>}
 */
export const loadPopups = (store) => {
    return ajax.get(`${config.apiBaseUrl}/load-list`, {
        spinnerMessage: 'Loading available popups...',
        errorMessage: 'Error loading available popups'
    }).then(
        ({body}) => {
            let popups = body['payload'] || {}
            store.commit('popups', popups)
            return popups
        }
    ).catch(() => {})
}

/**
 * Load site popups setup
 * @param store
 * @return {*|Promise.<TResult>}
 */
export const loadSiteSetup = (store) => {
    return ajax.get(`${config.apiBaseUrl}/load-site-setup`, {
        spinnerMessage: 'Loading site popups setup...',
        errorMessage: 'Error loading site popups setup'
    }).then(
        ({body}) => {
            let setup = body['payload'] || {}
            store.commit('siteSetup', setup)
            return setup
        }
    ).catch(() => {})
}

/**
 * Load site popups setup
 * @param store
 * @return {*|Promise.<TResult>}
 */
export const loadCustomPostTypesSetup = (store) => {
    return ajax.get(`${config.apiBaseUrl}/load-custom-post-types-setup`, {
        spinnerMessage: 'Loading custom post types popups setup...',
        errorMessage: 'Error loading custom post types popups setup'
    }).then(
        ({body}) => {
            let setup = body['payload'] || {}
            store.commit('cptSetup', setup)
            return setup
        }
    ).catch(() => {})
}

/**
 * Load individual posts popups setup
 * @param store
 * @return {*|Promise.<TResult>}
 */
export const loadIndividualPostsSetup = (store) => {
    return ajax.get(`${config.apiBaseUrl}/load-individual-posts-setup`, {
        spinnerMessage: 'Loading individual posts popups setup...',
        errorMessage: 'Error loading individual posts popups setup'
    }).then(
        ({body}) => {
            let setup = body['payload'] || {}
            store.commit('postsSetup', setup)
            return setup
        }
    ).catch(() => {})
}

/**
 * Load Individual Post Exclusion Setup
 * @since 1.1.1
 * @param store
 * @returns {Promise<T>}
 */
export const loadIndividualPostsExclusionSetup = (store) => {
    return ajax.get(`${config.apiBaseUrl}/load-individual-posts-exclusion-setup`, {
        spinnerMessage: 'Loading individual posts popups exclusion setup...',
        errorMessage: 'Error loading individual posts popups exclusion setup'
    }).then(
        ({body}) => {
            let setup = body['payload'] || {}
            store.commit('postsExclusionSetup', setup)
            return setup
        }
    ).catch(() => {})
}

/**
 * Setup popup trigger
 *
 * @param store
 * @param payload
 * @return {Promise.<TResult>}
 */
export const setupPopupTrigger = (store, payload) => {
    let { validator, callback, modal } = payload
    let { scope, subject, trigger, id, setup } = payload
    return ajax.post(`${config.apiBaseUrl}/setup-popup-trigger`, { scope, subject, trigger, id, setup }, {
        spinnerMessage: 'Setting up popup trigger...',
        errorMessage: 'Failed to setup popup trigger',
        validator,
        modal
    }).then(({body}) => {
        let subjectSetup = body.payload
        let setup

        switch (scope) {
            case 'site':
                setup = {
                    ...store.state.siteSetup,
                    [subject]: subjectSetup
                }
                store.commit('siteSetup', setup)
                break
            case 'cpt':
                setup = {
                    ...store.state.cptSetup,
                    [subject]: subjectSetup
                }
                store.commit('cptSetup', setup)
                break
            case 'post':
                setup = {
                    ...store.state.postsSetup,
                    [subject]: subjectSetup
                }
                store.commit('postsSetup', setup)
                break
        }
    }).then(callback || function () { })
}

/**
 * Setup Individual Posts Exclusion
 * @since 1.1.1
 * @param store
 * @param payload
 * @return {Promise.<TResult>}
 */
export const setupIndividualPostsExclusionPopup = (store, payload) => {
    let { validator, callback, modal, subject } = payload
    return ajax.post(`${config.apiBaseUrl}/setup-individual-posts-exclusion`, { subject }, {
        spinnerMessage: 'Setting up popup exclusions...',
        errorMessage: 'Failed to setup popup exclusions',
        validator,
        modal
    }).then(({body}) => {
        let setup = body.payload || []
        store.commit('postsExclusionSetup', setup)
    }).then(callback || function () { })
}

/**
 * Add individual post
 * @param store
 * @param postId
 * @return {*|Promise.<TResult>}
 */
export const addIndividualPost = (store, payload) => {
    let { validator, callback, modal } = payload
    let { postId } = payload

    return ajax.get(Vue.url(`${config.apiBaseUrl}/add-individual-post{/postId}`, { postId }), {
        spinnerMessage: 'Adding individual post...',
        errorMessage: 'Error adding individual post',
        validator,
        modal
    }).then(
        ({body}) => {
            let setup = body['payload'] || {}
            let postsSetup = {
                [postId]: setup,
                ...store.state.postsSetup
            }
            store.commit('postsSetup', postsSetup)
            return setup
        }
    ).then(callback || function () {}).catch(() => {})
}

/**
 * Load individual posts popups setup
 * @param store
 * @param payload
 * @return {*|Promise.<TResult>}
 */
export const removeIndividualPost = (store, payload) => {
    let { validator, callback, modal } = payload
    let { postId } = payload

    return ajax.get(Vue.url(`${config.apiBaseUrl}/remove-individual-post{/postId}`, { postId }), {
        spinnerMessage: 'Removing individual post...',
        errorMessage: 'Error removing individual post',
        validator,
        modal
    }).then(
        ({body}) => {
            let setup = body['payload'] || {}
            let postsSetup = {
                ...store.state.postsSetup
            }
            delete postsSetup[postId]
            store.commit('postsSetup', postsSetup)
            return setup
        }
    ).then(callback || function () {}).catch(() => {})
}