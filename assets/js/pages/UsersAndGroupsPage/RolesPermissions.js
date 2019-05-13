import React, {Component} from 'react';
import {
  searchAccounts,
  fetchAllRoles,
  fetchPerms,
  savePerms
} from '../../utils/api';
import SearchDropdown from '../../components/SearchDropdown';
import PropTypes from 'prop-types';
import Instruction from './RolesPermissionsInstruction';
import PermEntry from './PermEntry';

const permActions = {
  add: 'add',
  update: 'update',
  delete: 'delete'
};

export default class RolesPermissions extends Component {
  constructor(props) {
    super(props);

    this.state = {
      roles: [],
      perms: [],
      editingMode: false,
      shouldDropdownShown: false
    };

    this.findAccounts = this.findAccounts.bind(this);
    this.renderMatchItem    = this.renderMatchItem.bind(this);
    this.searchFieldValueOnSelection = this.searchFieldValueOnSelection.bind(this);
    this.updatePerm = this.updatePerm.bind(this);
    this.delperm = this.delPerm.bind(this);
    this.save = this.save.bind(this);
    this.cancel = this.cancel.bind(this);
    this.edit = this.edit.bind(this);

    this.searchSelection = null;
  }

  /**
   * Fetch existing perms and available roles after mount
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
  async findAccounts(keyword){
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
  addPerm(account){
    console.log('>>>> will grant permission to ', account);
    const perms = [...this.state.perms, {...account, role: null, dirty: true, action: permActions.add}];
    this.setState({perms});
  }

  updatePerm(perm, newRole) {
    // console.log('perm', perm);
    // console.log('newRole', newRole);
    const perms = this.state.perms.map((p) => {
      if (perm.role === p.role && perm.account_type === p.account_type && perm.account_id === p.account_id) {
        const action = p.dirty ? p.action : permActions.update;
        return {...p, role: newRole, dirty: true, action};
      } else {
        return p;
      }
    });
    this.setState({perms});
  }

  delPerm(perm) {
    if (perm.id){
      const perms = this.state.perms.map((p) => {
        if (p.id === perm.id) {
          return {...p, dirty: true, action: permActions.delete};
        } else {
          return p;
        }
      });
      this.setState({perms});
    } else {
      const perms = this.state.perms.filter((p) => {
        return !(
          perm.role === p.role &&
          perm.account_type === p.account_type &&
          perm.account_id === p.account_id
        );
      });
      this.setState({perms});
    }
  }

  /**
   * @param {Any} item item represents every single individual item of matches returned from `findAccounts`
   * @return {PropTypes.ReactElementLike}
   */
  renderMatchItem(item){
    console.log('item', item);
    let dashicon = null;
    if (item.account_type === 'USER'){
      dashicon = 'dashicons-admin-users';
    } else {
      dashicon = 'dashicons-groups';
    }
    return (<span><i className={`dashicons ${dashicon}`}></i>{item.account_display_name}</span>);
  }

  searchFieldValueOnSelection(selection){
    console.log('selected', selection);

    this.searchSelection = selection;
    return selection.account_display_name;
  }

  async save(){
    const dirtyPerms = this.state.perms.filter((p) => !!p.dirty);
    const status = savePerms(dirtyPerms);
    console.log('save status', status);
    this.setState({ editingMode: false });
  }

  async cancel(){
    const perms = await fetchPerms();
    this.setState({perms, editingMode:false});
  }

  edit() {
    this.setState({editingMode: true});
  }

  render(){
    const grantPermissionControlHTML = (
      <div className="grant-permission-subsection">
        <span>Grant permission to </span>
        <SearchDropdown
          search={this.findAccounts}
          renderMatchItem={this.renderMatchItem}
          shouldDropdownShown={this.state.shouldDropdownShown}
          noMatchMessage={<><i className="dashicons dashicons-warning" /> No match found</>}
          searchFieldValueOnSelection={this.searchFieldValueOnSelection}
        />
        <button className="btn btn-add-grant" onClick={() => this.addPerm(this.searchSelection)}>Add</button>
      </div>
    );

    const submitButton = <input key="submit" type="submit" value="Save Updates" onClick={this.save} />;
    const cancelButton = <input key="cancel" type="button" value="Cancel" onClick={this.cancel} />;
    const editButton = <input key="edit" type="button" value="Edit" onClick={this.edit} />;

    return (
      <div className="roles-permissions-page">
        <Instruction />
        <div className="section-container">
          <div className="section-title">Edit User/Group Roles</div>
          <div className="role-entries-subsection">
            <ul>
              {this.state.perms
                .filter(perm => !(perm.dirty && perm.action === permActions.delete))
                .map(perm => (
                  <PermEntry
                    key={perm.account_type + perm.account_id + perm.role}
                    label={perm.account_display_name}
                    value={perm.role}
                    editable={this.state.editingMode}
                    allRoles={this.state.roles}
                    onChange={value => this.updatePerm(perm, value)}
                    onDelete={() => this.delPerm(perm)}
                  />
                ))}
            </ul>
          </div>
          { this.state.editingMode && grantPermissionControlHTML }
        </div>
        <div className="btns-container">
          {
            this.state.editingMode ? [submitButton, cancelButton] : editButton
          }
        </div>
      </div>
    );
  }
}
