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

export async function searchAccounts(keyword){
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

export async function fetchAllRoles(){
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
export async function addPerm(role, account_id, account_type) {
  console.log('setting role');

  const payload = {
    command: 'add_perm',
    data: {account_type, account_id, role}
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
export async function updatePerm(role, perm_id) {
  console.log('update perm');

  const payload = {
    command: 'update_perm',
    data: {perm_id, role}
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
export async function removeRoleFromAccount(role, id, type) {
  const payload = {
    command: 'del_perm',
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

export async function fetchPerms(){
  const payload = {command: 'get_all_perms'};
  return await axios
    .post(permissionsRestURL, payload, config)
    .then(resp => {
      return resp.data;
    })
    .catch(error => {
      console.log(error);
    });
}