import FeaturedPostExtension from './featured-post/featured-post-sidebar-extension';
import PageIconExtension from './page-icon/page-icon-sidebar-extension';
import ClubInfoSidebarExtension from './club-info/club-info-sidebar-extension';
import { registerPlugin } from '@wordpress/plugins';
import { getCurrentPostType } from '../block-utils';

registerPlugin('nucssa-core-editor-extensions', {
  render: () => {

    const currentPostType = getCurrentPostType();
    const postTypesForFeaturedPost = ['post', 'page', 'club'];
    const postTypesForPageIcon = ['page'];
    return (
      <>
        { postTypesForFeaturedPost.includes(currentPostType) && <FeaturedPostExtension /> }
        { postTypesForPageIcon.includes(currentPostType) && <PageIconExtension /> }
        { currentPostType == 'club' && <ClubInfoSidebarExtension /> }
      </>
    );
  }
});
