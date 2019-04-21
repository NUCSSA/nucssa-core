/**
 * THIS FILE IS OBSOLETE ATM
 * BECAUSE EVERY PAGE WILL HAVE THEIR OWN STORE OBJECT
 */

// import { createStore, combineReducers, applyMiddleware } from 'redux';
// import ldapConfigReducer from '../redux/reducers/ldap-config-reducer';
// import rolesPermissionsReducer from '../redux/reducers/roles-permissions-reducer';
// import thunk from 'redux-thunk';

// export default createStore(
//   combineReducers({
//     ldapConfigs: ldapConfigReducer,
//     rolesPerissions: rolesPermissionsReducer
//   }),
//   window.__REDUX_DEVTOOLS_EXTENSION__ && window.__REDUX_DEVTOOLS_EXTENSION__(),
//   applyMiddleware(thunk)
// );