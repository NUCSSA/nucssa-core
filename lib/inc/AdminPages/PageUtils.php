<?php
/**
 * Author: Jason Ji
 * Github: https://github.com/JJPro
 */
namespace nucssa_core\inc\AdminPages;

/**
 * Utility functions for dealing with admin pages
 */
class PageUtils
{
  public static function removeWpFooter()
  {
    add_filter('update_footer', '__return_empty_string', 11);
    add_filter('admin_footer_text', '__return_empty_string', 11);
  }

  public static function printNUCSSAFooterBranding()
  {
    $year = date('Y');
    echo '<div class="nucssa-footer">
      <div class="brand-title">NUCSSA IT</div>
      <img class="brand-image" src="' . NUCSSA_CORE_DIR_URL . '/public/images/logo.png' . '" />
      <div class="copyright">© ' . $year . ' NUCSSA IT All Rights Reserved</div>
    </div>';
    echo '<style>
      .nucssa-footer {
        background-image: linear-gradient(#601C16, #140503);
        padding: 2rem 0;
        text-align: center;
      }
      .nucssa-footer .brand-title {
        font-weight: bold;
        font-size: 3rem;
        color: #D92110;
        line-height: 3rem;
      }
      .nucssa-footer .brand-image {
        width: 9rem;
      }
      .nucssa-footer .copyright{
        color: rgba(255,255,255,0.5);
        font-size: 0.8rem;
        font-weight: 100;
      }
    </style>';
  }

  public static function printStyleFixForAdminPageLeftPadding()
  {
    echo '<style>
      #wpcontent { padding-left: 0 !important; }
      #wpbody-content {padding-bottom: 0;}
    </style>';
  }
}
