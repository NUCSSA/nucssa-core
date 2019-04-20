import React from 'react';
import {connect} from 'react-redux';
import {bindActionCreators} from 'redux';
import {FETCH_LDAP_CONFIG} from '../../redux/actions/actions-names';
import {fetchLdapConfig} from '../../redux/actions/ldap-config-actions';

const UserDirectory = (props) => {
  props.fetchLdapConfig();
  return (
    <div>
      Hello There user
    </div>
  );
}

const mapDispatchToProps = (dispatch) => ({
  fetchLdapConfig: bindActionCreators(fetchLdapConfig, dispatch),
})
export default connect(null, mapDispatchToProps)(UserDirectory);