import { FETCH_LDAP_CONFIG, SET_LDAP_CONFIG } from "../actions/actions-names";

export default (state = defaultState, action) => {
  switch( action.type ) {

    case FETCH_LDAP_CONFIG:
      if (state.fetched) {
        return state;
      } else {
        return action.data;
      }
    default:
      return state;
  }
}

const defaultState = {
  fetched: false,
  server: {},
  schema: {},
  userSchema: {},
  groupSchema: {},
  membershipSchema: {}
};