import axios from "axios";
import { ldapConfigRestURL, permissionsRestURL, nonce } from "./constants";

const config = { 'headers': { 'X-WP-Nonce': nonce } };

export async function fetchLdapConfig(){
  return await axios.get(ldapConfigRestURL, config)
      .then((resp) => {
        // console.log(resp);
        return resp.data;
      })
      .catch((error) => {
        console.log(error);
      });
}

export async function setLdapConfig(data) {
  return await axios.post(ldapConfigRestURL, {command: 'save', data}, config)
      .then( (resp) => {
        return resp.data;
      })
      .catch((error) => {
        console.log(error);
      });
}

export async function syncLdap() {
  return await axios.post(ldapConfigRestURL, {command: 'sync'}, config)
      .then( (resp) => {
        return resp.data;
      })
      .catch((error) => {
        console.log(error);
      });
}

export async function testLdapConnection() {
  return await axios
    .post(ldapConfigRestURL, {command: 'test_connection'}, config)
    .then( (resp) => {
      return resp.data;
    })
    .catch((error) => {
      console.log(error);
    });
}

export async function searchUserGroups(keyword){
  const payload = {
    command: 'search',
    data: keyword
  };
  return await axios
    .post(permissionsRestURL, payload, config)
    .then(resp => {
      return resp.data;
    })
    .catch(error => {
      console.log(error);
    });
}

export async function fetchAvailableRoles(){
  const payload = {
    command: 'get_all_roles',
  };
  return await axios
    .post(permissionsRestURL, payload, config)
    .then(resp => {
      return resp.data;
    })
    .catch(error => {
      console.log(error);
    });
}

/**
 * @param {String} role role slug
 * @param {String|Number} id id of the user or group
 * @param {String} type 'user'|'group'
 */
export async function setRoleToUserGroup(role, id, type) {
  console.log('setting role');

  const payload = {
    command: 'set_role',
    data: {type, id, role}
  };
  return await axios
    .post(permissionsRestURL, payload, config)
    .then(resp => {
      return resp.data;
    })
    .catch(error => {
      console.log(error);
    });
}

/**
 * @param {String} role role slug
 * @param {String|Number} id id of the user or group
 * @param {String} type 'user'|'group'
 */
export async function removeRoleFromUserGroup(role, id, type) {
  const payload = {
    command: 'remove_role',
    data: {type, id, role}
  };
  return await axios
    .post(permissionsRestURL, payload, config)
    .then(resp => {
      return resp.data;
    })
    .catch(error => {
      console.log(error);
    });
}
