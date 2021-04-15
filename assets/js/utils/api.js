import axios from 'axios';
import { ldapConfigRestURL, permissionsRestURL, nonce } from './constants';

const ACTIONS = {
  GET: 'get',
  POST: 'post'
};

/**
 * Helper method to initial REST API request to the server
 * @param {String} action one of `restActions`
 * @param {String} URL
 * @param {Any} payload
 */
const authenticatedRequest = async (action, URL, payload = null) => {
  const config = { 'headers': { 'X-WP-Nonce': nonce } };
  return await axios({
    method: action,
    url: URL,
    data: payload,
    ...config
  }).then(resp => {
    return resp.data;
  }).catch(error => {
    // eslint-disable-next-line
    console.log(error);
  });
};


export const fetchLdapConfig = async () => {
  return await authenticatedRequest(ACTIONS.GET, ldapConfigRestURL);
};

export const setLdapConfig = async data => {
  const payload = { command: 'save', data };
  return await authenticatedRequest(ACTIONS.POST, ldapConfigRestURL, payload);
};

export const syncLdap = async () => {
  const payload = { command: 'sync' };
  return await authenticatedRequest(ACTIONS.POST, ldapConfigRestURL, payload);
};

export const testLdapConnection = async () => {
  const payload = { command: 'test_connection' };
  return await authenticatedRequest(ACTIONS.POST, ldapConfigRestURL, payload);
};

export const searchAccounts = async keyword => {
  const payload = {
    command: 'search',
    data: keyword
  };
  return await authenticatedRequest(ACTIONS.POST, permissionsRestURL, payload);
};

export const fetchAllRoles = async () => {
  const payload = {
    command: 'get_all_roles'
  };
  return await authenticatedRequest(ACTIONS.POST, permissionsRestURL, payload);
};

export const fetchPerms = async () => {
  const payload = { command: 'get_all_perms' };
  return await authenticatedRequest(ACTIONS.POST, permissionsRestURL, payload);
};

/**
 * Post request to batch update perms
 * @param {Array} perms Array of perms need to be updated/inserted/deleted
 *                      A perm is of shape {account_id, account_type, action, role?, id?}
 * @return promise: {success: bool}
 */
export const savePerms = async perms => {
  const payload = {
    command: 'save_perms',
    data: perms
  };
  return await authenticatedRequest(ACTIONS.POST, permissionsRestURL, payload);
};
