import FeaturedPostExtension from './featured-post-sidebar-extension';
import PageIconExtension from './page-icon-sidebar-extension';
import { registerPlugin } from '@wordpress/plugins';

registerPlugin('nucssa-core-editor-extensions', {
  render: () => (
    <>
      <FeaturedPostExtension />
      <PageIconExtension />
    </>
  )
});
