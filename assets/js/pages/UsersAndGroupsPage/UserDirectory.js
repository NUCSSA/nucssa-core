import { Component } from '@wordpress/element';
import { fetchLdapConfig, setLdapConfig, syncLdap, testLdapConnection } from '../../utils/api';
import './UserDirectory.scss';

export default class UserDirectory extends Component {
  constructor(props){
    super(props);

    this.state = {
      editMode: false,
      ldapConfig: {
        server: {},
        schema: {},
        user_schema: {},
        group_schema: {},
        membership_schema: {},
      },
    };

    this.testConnectionBtnRef     = React.createRef();
    this.testConnectionSpinnerRef = React.createRef();
    this.ldapSyncBtnRef           = React.createRef();
    this.ldapSyncSpinnerRef       = React.createRef();

    this.handleFormSubmit   = this.handleFormSubmit.bind(this);
    this.renderEditForm     = this.renderEditForm.bind(this);
    this.renderViewOnlyMode = this.renderViewOnlyMode.bind(this);
    this.enterEditMode      = this.enterEditMode.bind(this);
    this.exitEditMode       = this.exitEditMode.bind(this);
    this.handleFieldChange  = this.handleFieldChange.bind(this);
    this.cancelEdits        = this.cancelEdits.bind(this);
    this.allFieldsFilledUp  = this.allFieldsFilledUp.bind(this);
    this.testConnection     = this.testConnection.bind(this);
    this.ldapSync           = this.ldapSync.bind(this);
    this.spinner = this.spinner.bind(this);
  }

  async componentDidMount(){
    // fetch ldap config from server
    const ldapConfig = await fetchLdapConfig();
    this.setState({ldapConfig});
  }

  enterEditMode(){
    this.setState({editMode: true});
  }

  exitEditMode(){
    this.setState({ editMode: false });
  }

  handleFieldChange(section, attribute, value){
    this.setState({
      ldapConfig: {
        ...this.state.ldapConfig,
        [section]: {
          ...this.state.ldapConfig[section],
          [attribute]: value,
        },
      },
    });
  }

  async cancelEdits(){
    // fetch ldap config from server
    const ldapConfig = await fetchLdapConfig();

    this.setState({
      editMode: false,
      ldapConfig,
    });
  }

  async testConnection(){
    const btnColor = {
      original: '#3B86FF',
      error: '#D92110',
    };

    // show spinner & hide button
    this.testConnectionSpinnerRef.current.classList.add('active');
    this.testConnectionBtnRef.current.textContent = '';

    const resp = await testLdapConnection();

    // hide spinner && show button
    this.testConnectionSpinnerRef.current.classList.remove('active');

    if (resp == 'success'){
      this.testConnectionBtnRef.current.textContent = '✓';
      this.testConnectionBtnRef.current.style.backgroundColor = btnColor.original;
    } else {
      this.testConnectionBtnRef.current.textContent = 'Connection Failed';
      this.testConnectionBtnRef.current.style.backgroundColor = btnColor.error;
    }
    setTimeout(() => {
      this.testConnectionBtnRef.current.textContent = 'Test Connection';
      this.testConnectionBtnRef.current.style.backgroundColor = btnColor.original;
    }, 1700);
  }

  async ldapSync(){
    const btnColor = {
      original: '#98C379',
      error: '#D92110',
    };

    // show spinner & hide button
    this.ldapSyncSpinnerRef.current.classList.add('active');
    this.ldapSyncBtnRef.current.textContent = '';

    const resp = await syncLdap();

    // hide spinner && show button
    this.ldapSyncSpinnerRef.current.classList.remove('active');

    if (resp == 'success') {
      this.ldapSyncBtnRef.current.textContent = '✓';
      this.ldapSyncBtnRef.current.style.backgroundColor = btnColor.original;
    } else {
      this.ldapSyncBtnRef.current.textContent = 'Sync Failed';
      this.ldapSyncBtnRef.current.style.backgroundColor = btnColor.error;
    }
    setTimeout(() => {
      this.ldapSyncBtnRef.current.textContent = 'Manual Sync';
      this.ldapSyncBtnRef.current.style.backgroundColor = btnColor.original;
    }, 1700);

  }


  /**
   * check if all required fields are filled
   * @return boolean
   */
  allFieldsFilledUp(){
    const config = this.state.ldapConfig;
    const server_attrs = ['host', 'port', 'username', 'password'];
    const schema_attrs = ['base_dn', 'additional_user_dn', 'additional_group_dn'];
    const user_attrs = ['object_class', 'object_filter', 'username_attribute',
      'firstname_attribute', 'lastname_attribute', 'display_name_attribute',
      'email_attribute', 'user_id_attribute'];
    const group_attrs = ['object_class', 'object_filter', 'name_attribute', 'description_attribute', 'group_id_attribute'];
    const membership_attrs = ['group_membership_attribute', 'user_membership_attribute'];

    // eslint-disable
    if (!config) return false;
    if (!config.server) return false;
    if (!config.schema) return false;
    if (!config.user_schema) return false;
    if (!config.group_schema) return false;
    if (!config.membership_schema) return false;
    if (!server_attrs.every((attr) => config.server.hasOwnProperty(attr) && config.server[attr] != '') ) return false;
    if (!schema_attrs.every((attr) => config.schema.hasOwnProperty(attr) && config.schema[attr] != '') ) return false;
    if (!user_attrs.every((attr) => config.user_schema.hasOwnProperty(attr) && config.user_schema[attr] != '') ) return false;
    if (!group_attrs.every((attr) => config.group_schema.hasOwnProperty(attr) && config.group_schema[attr] != '') ) return false;
    if (!membership_attrs.every((attr) => config.membership_schema.hasOwnProperty(attr) && config.membership_schema[attr] != '') ) return false;

    return true;
    // eslint-enable
  }

  async handleFormSubmit(e){
    e.preventDefault();

    // check all inputs are filled up
    if (!this.allFieldsFilledUp()){
      alert('All fields are required');
      return;
    }

    // send data to server
    let respData = await setLdapConfig(this.state.ldapConfig);
    // get response and show success or error message
    if (respData.hasOwnProperty('update_status') && respData.update_status == 'updated'){
      this.exitEditMode();
    }

  }

  renderEditForm(){
    /* eslint-disable */
    return (
      <form onSubmit={this.handleFormSubmit}>

        <div className="section-container">
          <div className="section-title">LDAP Server</div>
          <div className="section-fields">
            <label>
              <span className="att-name">Host *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.server && this.state.ldapConfig.server.host || ''} onChange={(e) => this.handleFieldChange('server', 'host', e.currentTarget.value)} />
            </label>
            <label>
              <span className="att-name">Port *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.server && this.state.ldapConfig.server.port || ''} onChange={(e) => this.handleFieldChange('server', 'port', e.currentTarget.value)} />
            </label>
            <label>
              <span className="att-name">Username *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.server && this.state.ldapConfig.server.username || ''} onChange={(e) => this.handleFieldChange('server', 'username', e.currentTarget.value)} />
            </label>
            <label>
              <span className="att-name">Password *</span>
              <input required type="password" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.server && this.state.ldapConfig.server.password || ''} onChange={(e) => this.handleFieldChange('server', 'password', e.currentTarget.value)} />
            </label>
          </div>
        </div>

        <div className="section-container">
          <div className="section-title">LDAP Schema</div>
          <div className="section-fields">
            <label>
              <span className="att-name">Base DN *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.schema && this.state.ldapConfig.schema.base_dn || ''} onChange={(e) => this.handleFieldChange('schema', 'base_dn', e.currentTarget.value)} />
            </label>
            <label>
              <span className="att-name">Additional User DN *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.schema && this.state.ldapConfig.schema.additional_user_dn || ''} onChange={(e) => this.handleFieldChange('schema', 'additional_user_dn', e.currentTarget.value)} />
            </label>
            <label>
              <span className="att-name">Additional Group DN *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.schema && this.state.ldapConfig.schema.additional_group_dn || ''} onChange={(e) => this.handleFieldChange('schema', 'additional_group_dn', e.currentTarget.value)} />
            </label>
          </div>
        </div>

        <div className="section-container">
          <div className="section-title">User Schema</div>
          <div className="section-fields">
            <label>
              <span className="att-name">Object Class *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.user_schema && this.state.ldapConfig.user_schema.object_class || ''} onChange={(e) => this.handleFieldChange('user_schema', 'object_class', e.currentTarget.value)} />
            </label>
            <label>
              <span className="att-name">Object Filter *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.user_schema && this.state.ldapConfig.user_schema.object_filter || ''} onChange={(e) => this.handleFieldChange('user_schema', 'object_filter', e.currentTarget.value)} />
            </label>
            <label>
              <span className="att-name">Username Attribute *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.user_schema && this.state.ldapConfig.user_schema.username_attribute || ''} onChange={(e) => this.handleFieldChange('user_schema', 'username_attribute', e.currentTarget.value)} />
            </label>
            <label>
              <span className="att-name">First Name Attribute *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.user_schema && this.state.ldapConfig.user_schema.firstname_attribute || ''} onChange={(e) => this.handleFieldChange('user_schema', 'firstname_attribute', e.currentTarget.value)} />
            </label>
            <label>
              <span className="att-name">Last Name Attribute *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.user_schema && this.state.ldapConfig.user_schema.lastname_attribute || ''} onChange={(e) => this.handleFieldChange('user_schema', 'lastname_attribute', e.currentTarget.value)} />
            </label>
            <label>
              <span className="att-name">Display Name Attribute *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.user_schema && this.state.ldapConfig.user_schema.display_name_attribute || ''} onChange={(e) => this.handleFieldChange('user_schema', 'display_name_attribute', e.currentTarget.value)} />
            </label>
            <label>
              <span className="att-name">Email Attribute *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.user_schema && this.state.ldapConfig.user_schema.email_attribute || ''} onChange={(e) => this.handleFieldChange('user_schema', 'email_attribute', e.currentTarget.value)} />
            </label>
            <label>
              <span className="att-name">User ID Attribute *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.user_schema && this.state.ldapConfig.user_schema.user_id_attribute || ''} onChange={(e) => this.handleFieldChange('user_schema', 'user_id_attribute', e.currentTarget.value)} />
            </label>
          </div>
        </div>

        <div className="section-container">
          <div className="section-title">Group Schema</div>
          <div className="section-fields">
            <label>
              <span className="att-name">Object Class *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.group_schema && this.state.ldapConfig.group_schema.object_class || ''} onChange={(e) => this.handleFieldChange('group_schema', 'object_class', e.currentTarget.value)} />
            </label>
            <label>
              <span className="att-name">Object Filter *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.group_schema && this.state.ldapConfig.group_schema.object_filter || ''} onChange={(e) => this.handleFieldChange('group_schema', 'object_filter', e.currentTarget.value)} />
            </label>
            <label>
              <span className="att-name">Name Attribute *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.group_schema && this.state.ldapConfig.group_schema.name_attribute || ''} onChange={(e) => this.handleFieldChange('group_schema', 'name_attribute', e.currentTarget.value)} />
            </label>
            <label>
              <span className="att-name">Description Attribute *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.group_schema && this.state.ldapConfig.group_schema.description_attribute || ''} onChange={(e) => this.handleFieldChange('group_schema', 'description_attribute', e.currentTarget.value)} />
            </label>
            <label>
              <span className="att-name">Group ID Attribute *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.group_schema && this.state.ldapConfig.group_schema.group_id_attribute || ''} onChange={(e) => this.handleFieldChange('group_schema', 'group_id_attribute', e.currentTarget.value)} />
            </label>
          </div>
        </div>

        <div className="section-container">
          <div className="section-title">Membership Schema</div>
          <div className="section-fields">
            <label>
              <span className="att-name">Group Membership Attribute *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.membership_schema && this.state.ldapConfig.membership_schema.group_membership_attribute || ''} onChange={(e) => this.handleFieldChange('membership_schema', 'group_membership_attribute', e.currentTarget.value)} />
            </label>
            <label>
              <span className="att-name">User Membership Attribute *</span>
              <input required type="text" className="att-value" value={this.state.ldapConfig && this.state.ldapConfig.membership_schema && this.state.ldapConfig.membership_schema.user_membership_attribute || ''} onChange={(e) => this.handleFieldChange('membership_schema', 'user_membership_attribute', e.currentTarget.value)} />
            </label>
          </div>
        </div>

        <div className="btns-container">
          <input type="submit" value="Save" />
          <input type="button" value="Cancel" onClick={this.cancelEdits} />
        </div>

      </form>
    );
    /* eslint-enable */
  }

  renderViewOnlyMode(){

    return (
      <div className="server-table-container section-container">

        <p className="instruction">LDAP Server will be synchronised automatically every 10 mins.</p>
        <table>
          <thead>
            <tr>
              <th>Server</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{this.state.ldapConfig && this.state.ldapConfig.server && this.state.ldapConfig.server.host}</td>
              <td className="actions">
                <button className="btn btn-edit" onClick={this.enterEditMode}>Edit</button>
                <div className="btn-spinner-container">
                  <button className="btn btn-test" onClick={this.testConnection} ref={this.testConnectionBtnRef}>Test Connection</button>
                  {this.spinner(this.testConnectionSpinnerRef)}
                </div>
                <div className="btn-spinner-container">
                  <button className="btn btn-sync" onClick={this.ldapSync} ref={this.ldapSyncBtnRef}>Manual Sync</button>
                  {this.spinner(this.ldapSyncSpinnerRef)}
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    );
  }

  spinner(ref){
    return (
      <div className={'nucssa-spinner'} ref={ref}>
        <div className="bounce1"></div>
        <div className="bounce2"></div>
        <div className="bounce3"></div>
      </div>
    );
  }

  render(){
    if (this.state.editMode)
      return this.renderEditForm();
    else
      return this.renderViewOnlyMode();
  }
}
