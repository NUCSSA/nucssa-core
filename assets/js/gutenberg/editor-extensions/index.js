import FeaturedPostExtension from './featured-post/featured-post-sidebar-extension';
import PageIconExtension from './page-icon/page-icon-sidebar-extension';
import { registerPlugin } from '@wordpress/plugins';
import { getCurrentPostType } from '../block-utils';

registerPlugin('nucssa-core-editor-extensions', {
  render: () => {

    const currentPostType = getCurrentPostType();
    const excludedPostTypesForFeaturedPost = ['club'];
    const excludedPostTypesForPageIcon = ['club'];
    return (
      <>
        { !excludedPostTypesForFeaturedPost.includes(currentPostType) && <FeaturedPostExtension /> }
        { !excludedPostTypesForPageIcon.includes(currentPostType) && <PageIconExtension /> }
      </>
    );
  }
});
