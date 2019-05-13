import axios from "axios";
import { ldapConfigRestURL, permissionsRestURL, nonce } from "./constants";

const ACTIONS = {
  GET: 'get',
  POST: 'post',
};

/**
 * Helper method to initial REST API request to the server
 * @param {String} action one of `restActions`
 * @param {String} URL
 * @param {Any} payload
 */
async function authenticatedRequest(action, URL, payload = null) {
  const config = { 'headers': { 'X-WP-Nonce': nonce } };
  return await axios({
    method: action,
    url: URL,
    data: payload,
    ...config
  }).then(resp => {
    return resp.data;
  }).catch(error => {
    console.log(error);
  });
}


export async function fetchLdapConfig(){
  return await authenticatedRequest(ACTIONS.GET, ldapConfigRestURL);
}

export async function setLdapConfig(data) {
  const payload = { command: 'save', data };
  return await authenticatedRequest(ACTIONS.POST, ldapConfigRestURL, payload);
}

export async function syncLdap() {
  const payload = { command: 'sync' };
  return await authenticatedRequest(ACTIONS.POST, ldapConfigRestURL, payload);
}

export async function testLdapConnection() {
  const payload = { command: 'test_connection' };
  return await authenticatedRequest(ACTIONS.POST, ldapConfigRestURL, payload);
}

export async function searchAccounts(keyword){
  const payload = {
    command: 'search',
    data: keyword
  };
  return await authenticatedRequest(ACTIONS.POST, permissionsRestURL, payload);
}

export async function fetchAllRoles(){
  const payload = {
    command: 'get_all_roles',
  };
  return await authenticatedRequest(ACTIONS.POST, permissionsRestURL, payload);
}

export async function fetchPerms() {
  const payload = { command: 'get_all_perms' };
  return await authenticatedRequest(ACTIONS.POST, permissionsRestURL, payload);
}

/**
 * Post request to batch update perms
 * @param {Array} perms Array of perms need to be updated/inserted/deleted
 *                      A perm is of shape {account_id, account_type, action, role?, id?}
 */
export async function savePerms(perms){
  const payload = {
    command: 'save_perms',
    data: perms
  };
  return await authenticatedRequest(ACTIONS.POST, permissionsRestURL, payload);
}
