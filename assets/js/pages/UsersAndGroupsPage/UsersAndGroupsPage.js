import { render } from 'react-dom';
// BUG: wp.element.render doesn't work well with HashRouter
// import { render } from '@wordpress/element';
import { HashRouter, Route, Switch, NavLink } from 'react-router-dom';
import UserDirectory from './UserDirectory';
import RolesPermissions from './RolesPermissions';

const defaultActive = (match, location) => {
  return match || location.pathname == '/';
};

render(
  <HashRouter hashType="noslash">
    <div>
      <div className="nav">
        <NavLink to="/user-directory" activeClassName="active" exact isActive={defaultActive}>User Directory</NavLink>
        <NavLink to="/roles-permissions" activeClassName="active" exact>Roles & Permissions</NavLink>
      </div>
      <Switch>
        <Route path={['/', '/user-directory']} exact component={UserDirectory} />
        <Route path="/roles-permissions" exact component={RolesPermissions} />
      </Switch>
    </div>
  </HashRouter>,
  document.getElementById('users-and-groups-admin-page')
);
