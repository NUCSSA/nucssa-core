import React, {Component} from 'react';
import {searchUserGroups} from '../../utils/api';
import SearchDropdown from "../../components/SearchDropdown";
import PropTypes from "prop-types";

export default class RolesPermissions extends Component {
  constructor(props) {
    super(props);

    this.state = {
      roles: [],
      groupRolePairs: [],
      shouldDropdownShown: false,
    };

    this.findUserGroupMatch = this.findUserGroupMatch.bind(this);
    this.renderMatchItem    = this.renderMatchItem.bind(this);
    this.searchFieldValueOnSelection = this.searchFieldValueOnSelection.bind(this);

    this.searchSelection = null;
  }

  /**
   * search for users an groups matching the given keyword
   * @param {String} keyword
   * @return {Array} array of matched items
   */
  async findUserGroupMatch(keyword){
    this.searchSelection = 'test';
    let matches = [];
    if (keyword.length >= 2){
      /**
       * search DB for matching users and groups
       */
      const {users, groups} = await searchUserGroups(keyword);
      const user_matches = users.map(user => ({...user, key: user.id, type: 'user'}));
      const group_matches = groups.map(group=> ({...group, key: group.id, type: 'group'}));
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
   * @param {Object} userGroup {id, display_name, key, type='user'} | {id, group_name, key, type='group'}
   */
  willGrantPermissionTo(userGroup){
    console.log(">>>> will grant permission to ", userGroup);
  }

  /**
   * @param {Any} item item represents every single individual item of matches returned from `findUserGroupMatch`
   * @return {PropTypes.ReactElementLike}
   */
  renderMatchItem(item){
    console.log("item", item);
    if (item.type === 'user'){
      return <span><i className="dashicons dashicons-admin-users"></i>{item.display_name}</span>;
    } else {
      return <span><i className="dashicons dashicons-groups"></i>{item.group_name}</span>;
    }
  }

  searchFieldValueOnSelection(selection){
    console.log("selected", selection);

    this.searchSelection = selection;

    if (selection.type === 'user'){
      return selection.display_name;
    } else {
      return selection.group_name;
    }
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
                <th>Add</th>
                <th>Edit Own</th>
                <th>Edit Others</th>
                <th>Publish</th>

                <th>Add</th>
                <th>Edit Own</th>
                <th>Edit Others</th>
                <th>Publish</th>

                <th>Add</th>
                <th>Edit Own</th>
                <th>Edit Others</th>
                <th>Send</th>

                <th>Manage Plugins</th>
                <th>Manage Users</th>
                <th>Manage Themes</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th>Administrators</th>
                <td>{check}</td>
                <td>{check}</td>
                <td>{check}</td>
                <td>{check}</td>

                <td>{check}</td>
                <td>{check}</td>
                <td>{check}</td>
                <td>{check}</td>

                <td>{check}</td>
                <td>{check}</td>
                <td>{check}</td>
                <td>{check}</td>

                <td>{check}</td>
                <td>{check}</td>
                <td>{check}</td>
              </tr>
              <tr>
                <th>Editors</th>
                <td>{check}</td>
                <td>{check}</td>
                <td>{check}</td>
                <td>{check}</td>

                <td>{check}</td>
                <td>{check}</td>
                <td>{check}</td>
                <td>{check}</td>

                <td>{check}</td>
                <td>{check}</td>
                <td>{check}</td>
                <td>{check}</td>

                <td>{uncheck}</td>
                <td>{uncheck}</td>
                <td>{uncheck}</td>
              </tr>
              <tr>
                <th>Authors</th>
                <td>{check}</td>
                <td>{check}</td>
                <td>{uncheck}</td>
                <td>{uncheck}</td>

                <td>{check}</td>
                <td>{check}</td>
                <td>{uncheck}</td>
                <td>{uncheck}</td>

                <td>{uncheck}</td>
                <td>{uncheck}</td>
                <td>{uncheck}</td>
                <td>{uncheck}</td>

                <td>{uncheck}</td>
                <td>{uncheck}</td>
                <td>{uncheck}</td>
              </tr>
              <tr>
                <th>Subscribers</th>
                <td>{uncheck}</td>
                <td>{uncheck}</td>
                <td>{uncheck}</td>
                <td>{uncheck}</td>

                <td>{uncheck}</td>
                <td>{uncheck}</td>
                <td>{uncheck}</td>
                <td>{uncheck}</td>

                <td>{uncheck}</td>
                <td>{uncheck}</td>
                <td>{uncheck}</td>
                <td>{uncheck}</td>

                <td>{uncheck}</td>
                <td>{uncheck}</td>
                <td>{uncheck}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    );
  }

  renderRolesEditingSection(){
    return (
      <div className="section-container">
        <div className="section-title">Edit User/Group Roles</div>
        <div className="role-entries-subsection">
          {}
        </div>
        <div className="grant-permission-subsection">
          <span>Grant permission to </span>
          <SearchDropdown
            search={this.findUserGroupMatch}
            renderMatchItem={this.renderMatchItem}
            shouldDropdownShown={this.state.shouldDropdownShown}
            noMatchMessage={<React.Fragment><i className="dashicons dashicons-warning"></i> No match found</React.Fragment>}
            searchFieldValueOnSelection={this.searchFieldValueOnSelection}
          />
          <button className='btn btn-add-grant' onClick={() => this.willGrantPermissionTo(this.searchSelection)}>Add</button>
        </div>
      </div>
    );
  }


  render(){
    return (
      <div className="roles-permissions-page">
        {this.renderPageInstructionSection()}
        {this.renderRolesEditingSection()}
      </div>
    );
  }
}