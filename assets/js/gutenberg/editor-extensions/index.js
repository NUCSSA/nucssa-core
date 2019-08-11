import FeaturedPostExtension from './featured-post-sidebar-extension';
import { registerPlugin } from '@wordpress/plugins';

registerPlugin('nucssa-core-editor-extensions', {
  render: FeaturedPostExtension
});
