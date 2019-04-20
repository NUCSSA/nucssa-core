import axios from "axios";
import { ldapConfigRestURL, nonce } from '../../utils/constants';
import { FETCH_LDAP_CONFIG, SET_LDAP_CONFIG } from './actions-names';

// fetch LDAP configurations
export const fetchLdapConfig = () => {
  return (dispatch) => {
    console.log(">>> dispatch", dispatch);

    return axios.get(
        ldapConfigRestURL,
        {
          'headers': {
            'X-WP-Nonce': nonce
          }
        }
      )
      .then( (resp) => {
        console.log(resp);

      })
      .catch( (error) => {
        console.log(error);
      });
  }
}


// set LDAP configurations
export const setLdapConfig = ({server, schema, userSchema, groupSchema, membershipSchema}) => {

}