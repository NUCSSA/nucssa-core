import React, {Component} from 'react';
import {
  searchAccounts,
  fetchAllRoles,
  addPerm,
  updatePerm,
  removeRoleFromAccount,
  fetchPerms
} from '../../utils/api';
import SearchDropdown from "../../components/SearchDropdown";
import PropTypes from "prop-types";

export default class RolesPermissions extends Component {
  constructor(props) {
    super(props);

    this.state = {
      roles: [],
      perms: [],
      shouldDropdownShown: false,
    };

    this.findMatchedAccounts = this.findMatchedAccounts.bind(this);
    this.renderMatchItem    = this.renderMatchItem.bind(this);
    this.searchFieldValueOnSelection = this.searchFieldValueOnSelection.bind(this);
    this.roleSelectHtml = this.roleSelectHtml.bind(this);
    this.setRoleToAccount = this.setRoleToAccount.bind(this);
    this.removePerm = this.removePerm.bind(this);

    this.searchSelection = null;
  }

  /**
   * Fetch all available roles after mount
   */
  async componentDidMount(){
    /**
     * @var allAvailableRoles
     * {editor: "Editor", author: "Author", ...}
     */
    const allAvailableRoles = await fetchAllRoles();
    const role_slugs = Object.keys(allAvailableRoles);
    let roles = role_slugs.map((slug) => ({slug, display: allAvailableRoles[slug]}));
    let perms = await fetchPerms();
    this.setState({roles, perms});
  }

  /**
   * search for users and groups matching the given keyword
   * @param {String} keyword
   * @return {Array} array of matched items
   */
  async findMatchedAccounts(keyword){
    let matches = [];
    if (keyword.length >= 2){
      /**
       * search DB for matching users and groups
       */
      const {users, groups} = await searchAccounts(keyword);
      const user_matches = users.map(user => ({key: user.id + 'USER', account_id: user.id, account_display_name: user.display_name, account_type: 'USER'}));
      const group_matches = groups.map(group=> ({key: group.id + 'GROUP', account_id: group.id, account_display_name: group.group_name, account_type: 'GROUP'}));
      matches = [...user_matches, ...group_matches];
      // console.log('matches', matches);
      this.setState({shouldDropdownShown: true});
    } else {
      this.setState({shouldDropdownShown: false});
    }
    return matches;
  }

  /**
   * Add a new entry for assigning role to given user or group
   * @param {Object} account {account_id, account_display_name, key, account_type='USER'|'GROUP'}
   */
  addPermHTMLFor(account){
    console.log(">>>> will grant permission to ", account);
    const perms = [...this.state.perms, {...account, role: null}];
    this.setState({perms});
  }

  /**
   * send API request to add role to selected user or group
   * @param {String} role_slug
   * @param {Object} account {id, account_display_name, key, account_id, account_type='USER'|'GROUP'}
   */
  async setRoleToAccount(role_slug, {id, account_id, account_type}){
    if (id){

      console.log('update , id', id, 'account_id', account_id);
      updatePerm(role_slug, id);
    } else {
      console.log('add new');
      const new_perm_id = await addPerm(role_slug, account_id, account_type);
      console.log('new_perm_id', new_perm_id);
      const newPerms = this.state.perms.map((perm) => {
        if (perm.role === role_slug && perm.account_id === account_id && perm.account_type === account_type) {
          return {...perm, id: new_perm_id};
        } else {
          return perm;
        }
      });
      this.setState({perms: newPerms});
    }
  }

  /**
   * send API request to remove role from selected user or group
   * @param {String} role_slug
   * @param {Object} account {id, display_name, key, type='user'} | {id, group_name, key, type='group'}
   */
  async removePerm(role_slug, account){
    const success = await removeRoleFromAccount(role_slug, account.id, account.type);
    if (success){
      const perms = this.state.perms.filter(
        ({type, id}) => type === account.type && id === account.id
      );
      this.setState({perms})
    }
  }

  /**
   * @param {Any} item item represents every single individual item of matches returned from `findMatchedAccounts`
   * @return {PropTypes.ReactElementLike}
   */
  renderMatchItem(item){
    console.log("item", item);
    let dashicon = null;
    if (item.account_type === 'USER'){
      dashicon = 'dashicons-admin-users';
    } else {
      dashicon = 'dashicons-groups';
    }
    return (<span><i className={`dashicons ${dashicon}`}></i>{item.account_display_name}</span>);
  }

  searchFieldValueOnSelection(selection){
    console.log("selected", selection);

    this.searchSelection = selection;
    return selection.account_display_name;
  }

  roleSelectHtml(account, role){
    console.log('account', account);
    return (
      <select defaultValue={role || 'null'} onChange={e => this.setRoleToAccount(e.currentTarget.value, account)}>
        <option value="null">---</option>
        {
          this.state.roles.map(
            ({slug, display}) => <option key={slug} value={slug}>{display}</option>
          )
        }
      </select>
    );
  }

  renderPageInstructionSection(){
    const check = <i className='dashicons dashicons-yes'></i>;
    const uncheck = <i className='dashicons dashicons-no-alt'></i>;
    return (
      <div className="section-container">
        <div className="section-title" style={{textAlign: 'center'}}>About Roles and Permissions</div>
        <div className="instructions">
          <table>
            <thead>
              <tr className='heading'>
                <th></th>
                <th colSpan="4">Posts</th>
                <th colSpan="4">Pages</th>
                <th colSpan="4">Newsletters</th>
                <th colSpan="3">System Admin</th>
              </tr>
              <tr className='subheading'>
                <th></th>
                <th>Add</th><th>Edit Own</th><th>Edit Others</th><th>Publish</th>
                <th>Add</th><th>Edit Own</th><th>Edit Others</th><th>Publish</th>
                <th>Add</th><th>Edit Own</th><th>Edit Others</th><th>Send</th>
                <th>Manage Plugins</th><th>Manage Users</th><th>Manage Themes</th><th></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th>Administrators</th>
                <td>{check}</td><td>{check}</td><td>{check}</td><td>{check}</td>
                <td>{check}</td><td>{check}</td><td>{check}</td><td>{check}</td>
                <td>{check}</td><td>{check}</td><td>{check}</td><td>{check}</td>
                <td>{check}</td><td>{check}</td><td>{check}</td>
              </tr>
              <tr>
                <th>Editors</th>
                <td>{check}</td><td>{check}</td><td>{check}</td><td>{check}</td>
                <td>{check}</td><td>{check}</td><td>{check}</td><td>{check}</td>
                <td>{check}</td><td>{check}</td><td>{check}</td><td>{check}</td>
                <td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td>
              </tr>
              <tr>
                <th>Authors</th>
                <td>{check}</td><td>{check}</td><td>{uncheck}</td><td>{uncheck}</td>
                <td>{check}</td><td>{check}</td><td>{uncheck}</td><td>{uncheck}</td>
                <td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td>
                <td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td>
              </tr>
              <tr>
                <th>Subscribers</th>
                <td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td>
                <td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td>
                <td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td>
                <td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    );
  }

  renderPermsEditingSection(){
    console.log('perms', this.state.perms);
    return (
      <div className="section-container">
        <div className="section-title">Edit User/Group Roles</div>
        <div className="role-entries-subsection">
          <ul>
            {
              this.state.perms.map(
                (perm) => {
                  return <li key={perm.account_type + perm.account_id + perm.role}>
                    <span className="name">{perm.account_display_name}</span>
                    {this.roleSelectHtml(perm, perm.role)}
                    <div className="actions">
                      <button className="remove-entry">
                        <i className="dashicons dashicons-no-alt" onClick={() => this.removePerm(perm.id)}></i>
                      </button>
                    </div>
                  </li>;
                }
              )
            }
          </ul>
        </div>
        <div className="grant-permission-subsection">
          <span>Grant permission to </span>
          <SearchDropdown
            search={this.findMatchedAccounts}
            renderMatchItem={this.renderMatchItem}
            shouldDropdownShown={this.state.shouldDropdownShown}
            noMatchMessage={<React.Fragment><i className="dashicons dashicons-warning"></i> No match found</React.Fragment>}
            searchFieldValueOnSelection={this.searchFieldValueOnSelection}
          />
          <button className='btn btn-add-grant' onClick={() => this.addPermHTMLFor(this.searchSelection)}>Add</button>
        </div>
      </div>
    );
  }


  render(){
    return (
      <div className="roles-permissions-page">
        {this.renderPageInstructionSection()}
        {this.renderPermsEditingSection()}
      </div>
    );
  }
}