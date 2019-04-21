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
