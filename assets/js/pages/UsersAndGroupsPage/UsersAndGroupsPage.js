import React, {Component} from 'react';
import { render } from 'react-dom';
import { Provider } from 'react-redux';
import { HashRouter, Route, Switch, NavLink } from 'react-router-dom';
import { createStore, combineReducers, compose, applyMiddleware } from 'redux';
import rolesPermissionsReducer from '../../redux/reducers/roles-permissions-reducer';
import thunk from 'redux-thunk';
import UserDirectory from './UserDirectory';
import RolesPermissions from './RolesPermissions';

const store = createStore(
  combineReducers({
    rolesPerissions: rolesPermissionsReducer
  }),
  compose(
    applyMiddleware(thunk),
    window.__REDUX_DEVTOOLS_EXTENSION__ && window.__REDUX_DEVTOOLS_EXTENSION__() || compose
  )
);

const defaultActive = (match, location) => {
  return match || location.pathname == '/';
};

render(
  <Provider store={ store }>
    <HashRouter hashType="noslash">
      <div>
        <div className="nav">
          <NavLink to="/user-directory" activeClassName="active" exact isActive={defaultActive}>User Directory</NavLink>
          <NavLink to="/roles-permissions" activeClassName="active" exact>Roles & Permissions</NavLink>
        </div>
        <Switch>
          <Route path={["", "/user-directory"]} exact component={UserDirectory} />
          <Route path="/roles-permissions" exact component={RolesPermissions} />
        </Switch>
      </div>
    </HashRouter>
  </Provider>,
  document.getElementById('users-and-groups-admin-page')
);
