import Vue from 'vue'
import { library, config } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

import {
    faUser, faLock, faSignOutAlt, faCog, faCalculator, faBrain
} from '@fortawesome/free-solid-svg-icons'

import {
    faTwitch
} from '@fortawesome/free-brands-svg-icons'

config.autoAddCss = false

library.add(
    faUser, faLock, faSignOutAlt, faCog, faTwitch, faCalculator, faBrain
)

Vue.component('Fa', FontAwesomeIcon)
