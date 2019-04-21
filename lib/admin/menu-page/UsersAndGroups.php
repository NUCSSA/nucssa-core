<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace NUCSSACore\Admin\MenuPage;

use NUCSSACore\Admin\MenuPage\UsersAndGroups\UserDirectorySettings;

/**
 * `Users and Groups` sub-level menu
 */
class UsersAndGroups
{
  private $userDirectorySettings;

  public function __construct()
  {
    $this->userDirectorySettings = new UserDirectorySettings();
  }

  public function registerSettings()
  {
    // register user directory settings
    $this->userDirectorySettings->registerSettings();
    // register roles and permissions settings
  }

  public function addSettings()
  {
    $this->userDirectorySettings->addSettings();
  }

  public function render()
  {
    echo '<form action="options.php" method="post">';
    $this->userDirectorySettings->render();
    submit_button();
    echo '</form>';
  }
}
