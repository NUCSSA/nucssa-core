import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { TextControl } from '@wordpress/components';

import FeaturedPostBannerImage, {BANNER_SIZE_NARROW, BANNER_SIZE_WIDE} from '../../../components/FeaturedPostBannerImage';

const FeaturedPostExtension = (props) => {
  const toggleFeaturedPost = () => {
    if (props.isFeaturedPost) {
      props.cancelFeaturedPost();
    } else {
      props.setFeaturedPost();
    }
  }

  return (
    <PluginPostStatusInfo className="nucssa-theme-featured-posts">
      <div>
        <label><input type="checkbox" onChange={toggleFeaturedPost} checked={props.isFeaturedPost} />Set as Featured Post</label>
        {
          props.isFeaturedPost &&
          <div className="configs">
            <TextControl type='number' value={props.priority} label="Priority" help="featured posts will show in Homepage carousel" onChange={props.updateFeaturedPostPriority} />
            <div className="divider"></div>
            <p className="hint">Wide Banner Image (1920x400)</p>
            <div className="help">shows on large screen</div>
            <FeaturedPostBannerImage bannerSize={BANNER_SIZE_WIDE} recommendedSize="1920x400" />
            <div className="divider"></div>
            <p className="hint">Wide Banner Image (640x314)</p>
            <div className="help">shows on mobile</div>
            <FeaturedPostBannerImage bannerSize={BANNER_SIZE_NARROW} recommendedSize="640x314" />
          </div>
        }
      </div>
    </PluginPostStatusInfo>
  );
};

const store = 'core/editor';
const metaKey = '_nucssa_featured_post_priority';
const defaultPriority = 10;
const mapStateToProps = withSelect( select => {
  const featuredPriority = select(store).getEditedPostAttribute('meta')[metaKey];
  return {
    isFeaturedPost: featuredPriority > 0,
    priority: featuredPriority
  };
});

const mapDispatchToProps = withDispatch( dispatch => {
  const currentMetas = wp.data.select(store).getEditedPostAttribute('meta');
  const setFeaturedPost = () => dispatch(store).editPost({
    meta: {
      ...currentMetas,
      [metaKey]: defaultPriority
    }
  });
  const cancelFeaturedPost = () => dispatch(store).editPost({
    meta: {
      ...currentMetas,
      [metaKey]: 0
    }
  });
  const updateFeaturedPostPriority = (priority) => dispatch(store).editPost({
    meta: {
      ...currentMetas,
      [metaKey]: priority
    }
  });

  return {
    setFeaturedPost,
    cancelFeaturedPost,
    updateFeaturedPostPriority
  };
});

export default compose(
  mapStateToProps,
  mapDispatchToProps
)(FeaturedPostExtension);