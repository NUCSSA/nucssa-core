<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace NUCSSACore\Admin\MenuPage\UsersAndGroups;

class UserDirectorySettings
{
  public function registerSettings()
  {
    $this->registerSettings__LdapServer();
    $this->registerSettings__LdapSchema();
    $this->registerSettings__UserSchema();
    $this->registerSettings__GroupSchema();
    $this->registerSettings__MembershipSchema();
  }

  public function addSettings()
  {
    $this->addSettings__LdapServer();
    $this->addSettings__LdapSchema();
    $this->addSettings__UserSchema();
    $this->addSettings__GroupSchema();
    $this->addSettings__MembershipSchema();
  }

  public function render()
  {
    settings_fields( 'nucssa-user-directory__ldap-server');
    do_settings_sections('admin-menu-page-nucssa-core');
  }

  private function registerSettings__LdapServer()
  {
    // Host
    register_setting(
      'nucssa-user-directory__ldap-server',
      'host'
      // array(
      //   'type'              => 'string',
      //   'sanitize_callback' => 'sanitize_text_field',
      //   'default'           => 'wiki.nucssa.org'
      // )
    );
    // Port
    register_setting(
      'nucssa-user-directory__ldap-server',
      'username',
      array(
        'type'              => 'integer',
        'default'           => NULL
      )
    );
    // Username
    register_setting(
      'nucssa-user-directory__ldap-server',
      'username',
      array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => NULL
      )
    );
    // Password
    register_setting(
      'nucssa-user-directory__ldap-server',
      'password',
      array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => NULL
      )
    );
  }

  private function registerSettings__LdapSchema()
  {
    // Base DN
    register_setting(
      'nucssa-user-directory__ldap-schema',
      'base-dn',
      array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => NULL
      )
    );
    // Additional User DN
    register_setting(
      'nucssa-user-directory__ldap-schema',
      'additional-user-dn',
      array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => NULL
      )
    );
    // Additional Group DN
    register_setting(
      'nucssa-user-directory__ldap-schema',
      'additional-group-dn',
      array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => NULL
      )
    );
  }

  private function registerSettings__UserSchema()
  {
    // Object Class
    register_setting(
      'nucssa-user-directory__user-schema',
      'object-class',
      array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => NULL
      )
    );
    // Object Filter
    register_setting(
      'nucssa-user-directory__user-schema',
      'object-filter',
      array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => NULL
      )
    );
    // Username Attribute
    register_setting(
      'nucssa-user-directory__user-schema',
      'username-attribute',
      array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => NULL
      )
    );
    // First Name Attribute
    register_setting(
      'nucssa-user-directory__user-schema',
      'first-name-attribute',
      array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => NULL
      )
    );
    // last Name Attribute
    register_setting(
      'nucssa-user-directory__user-schema',
      'last-name-attribute',
      array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => NULL
      )
    );
    // display Name Attribute
    register_setting(
      'nucssa-user-directory__user-schema',
      'display-name-attribute',
      array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => NULL
      )
    );
    // Email Attribute
    register_setting(
      'nucssa-user-directory__user-schema',
      'email-attribute',
      array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => NULL
      )
    );
    // User ID Attribute
    register_setting(
      'nucssa-user-directory__user-schema',
      'user-id-attribute',
      array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => NULL
      )
    );
  }

  private function registerSettings__GroupSchema()
  {
    // Object Class
    register_setting(
      'nucssa-user-directory__group-schema',
      'object-class',
      array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => NULL
      )
    );
    // Object Filter
    register_setting(
      'nucssa-user-directory__group-schema',
      'object-filter',
      array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => NULL
      )
    );
    // Name Attribute
    register_setting(
      'nucssa-user-directory__group-schema',
      'name-attribute',
      array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => NULL
      )
    );
    // Description Attribute
    register_setting(
      'nucssa-user-directory__group-schema',
      'description-attribute',
      array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => NULL
      )
    );
    // Group ID Attribute
    register_setting(
      'nucssa-user-directory__group-schema',
      'group-id-attribute',
      array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => NULL
      )
    );
  }

  private function registerSettings__MembershipSchema()
  {
    // Group Membership Attribute
    register_setting(
      'nucssa-user-directory__membership-schema',
      'group-membership-attribute',
      array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => NULL
      )
    );
    // User Membership Attribute
    register_setting(
      'nucssa-user-directory__membership-schema',
      'user-membership-attribute',
      array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default'           => NULL
      )
    );
  }

  private function addSettings__LdapServer()
  {
    add_settings_section('nucssa-ldap-server', 'LDAP Server', NULL, 'admin-menu-page-nucssa-core');
    add_settings_field(
      'ldap-server-host', // id
      'Host',             // label
      function($args){$this->renderTextInput($args);}, // render callback
      'admin-menu-page-nucssa-core', // page
      'nucssa-ldap-server', // section
      array(                // args
        'label_for' => 'ldap-server-host',
        'class'     => 'tr-class',
        'option-group' => 'nucssa-user-directory__ldap-server',
        'option-name'    => 'host'
      )
    );
    add_settings_field(
      'ldap-server-port', // id
      'Port',             // label
      function($args){$this->renderTextInput($args);}, // render callback
      'admin-menu-page-nucssa-core', // page
      'nucssa-ldap-server', // section
      array(                // args
        'label_for' => 'ldap-server-port',
        'class'     => 'tr-class',
        'option-group' => 'nucssa-user-directory__ldap-server',
        'option-name'    => 'port'
      )
    );
    add_settings_field(
      'ldap-server-username', // id
      'Username',             // label
      function($args){$this->renderTextInput($args);}, // render callback
      'admin-menu-page-nucssa-core', // page
      'nucssa-ldap-server', // section
      array(                // args
        'label_for' => 'ldap-server-username',
        'class'     => 'tr-class',
        'option-group' => 'nucssa-user-directory__ldap-server',
        'option-name'    => 'username'
      )
    );
    add_settings_field(
      'ldap-server-password', // id
      'Password',             // label
      function($args){$this->renderTextInput($args);}, // render callback
      'admin-menu-page-nucssa-core', // page
      'nucssa-ldap-server', // section
      array(                // args
        'label_for' => 'ldap-server-password',
        'class'     => 'tr-class',
        'option-group' => 'nucssa-user-directory__ldap-server',
        'option-name'    => 'password'
      )
    );
  }

  private function addSettings__LdapSchema()
  {
    add_settings_section('nucssa-ldap-schema', 'LDAP Schema', NULL, 'admin-menu-page-nucssa-core');
    add_settings_field(
      'ldap-schema-base-dn', // id
      'Base DN',             // label
      function ($args) {
        $this->renderTextInput($args);
      }, // render callback
      'admin-menu-page-nucssa-core', // page
      'nucssa-ldap-schema', // section
      array(                // args
        'label_for' => 'ldap-schema-base-dn',
        'class'     => 'tr-class',
        'option-group' => 'nucssa-user-directory__ldap-schema',
        'option-name'    => 'base-dn'
      )
    );
  }

  private function addSettings__UserSchema()
  {

  }

  private function addSettings__GroupSchema()
  {

  }

  private function addSettings__MembershipSchema()
  {

  }

  private function renderTextInput($args)
  {
    [
      'label_for' => $id,
      'option-group' => $group,
      'option-name'  => $name
    ] = $args;
    $value = get_option($name);
    // var_dump($value);
    $html_name = $group . '[' . $name . ']';
    // var_dump($html_name);
    echo "<input type='text' id='{$id}' value='$value' name='$html_name' />";
  }

}
