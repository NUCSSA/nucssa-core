import { __ } from '@wordpress/i18n';
import { Button, Spinner, ResponsiveWrapper } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import PropTypes from 'prop-types';

const ALLOWED_MEDIA_TYPES = ['image'];

function FeaturedPostBannerImage({ bannerImageId, media, onUpdateImage, onRemoveImage, recommendedSize } ) {
  const SET_BANNER_IMAGE_LABEL = <>Set Banner Image<br/>{recommendedSize}</>;
  const instructions = <p>{__('To edit the featured image, you need permission to upload media.')}</p>;

  let mediaWidth, mediaHeight, mediaSourceUrl;
  if (media) {
    if (media.media_details.sizes['post-thumbnail']) {
      mediaWidth = media.media_details.sizes['post-thumbnail'].width;
      mediaHeight = media.media_details.sizes['post-thumbnail'].height;
      mediaSourceUrl = media.media_details.sizes['post-thumbnail'].source_url;
    } else {
      mediaWidth = media.media_details.width;
      mediaHeight = media.media_details.height;
      mediaSourceUrl = media.source_url;
    }
  }
  return (
    <div className="editor-featured-post-banner-image">
      {/* Set Banner Image */}
      <MediaUploadCheck fallback={ instructions }>
        <MediaUpload
          title={`Banner Image ${recommendedSize}`}
          onSelect={ onUpdateImage }
          allowedTypes={ ALLOWED_MEDIA_TYPES }
          render={ ({open}) => (
            <Button
              className={!bannerImageId ? 'editor-featured-post-banner-image__toggle' : 'editor-featured-post-banner-image__preview'}
              onClick={open}
            >
              {
                !! bannerImageId && media &&
                <ResponsiveWrapper naturalWidth={mediaWidth} naturalHeight={mediaHeight}>
                  <img src={mediaSourceUrl} alt=""/>
                </ResponsiveWrapper>
              }
              { !! bannerImageId && !media && <Spinner /> }
              { !bannerImageId && SET_BANNER_IMAGE_LABEL }
            </Button>
          )}
          value={ bannerImageId }
        />
      </MediaUploadCheck>
      {
        // Replace Banner Image Button
        !!bannerImageId && media && !media.isLoading &&
        <MediaUploadCheck>
          <MediaUpload
            title={`Banner Image ${recommendedSize}`}
            onSelect={ onUpdateImage }
            allowedTypes={ALLOWED_MEDIA_TYPES}
            render={({ open }) => (
              <Button onClick={open} isDefault isLarge>
                {__('Replace Image')}
              </Button>
            )}
            value={bannerImageId}
          />
        </MediaUploadCheck>
      }
      {
        // Remove Banner Image Button
        !! bannerImageId &&
        <MediaUploadCheck>
          <Button onClick={ onRemoveImage } isLink isDestructive>
            Remove Banner Image
          </Button>
        </MediaUploadCheck>
      }
    </div>
  );
}

FeaturedPostBannerImage.propTypes = {
  bannerSize: PropTypes.string.isRequired, // BANNER_SIZE_WIDE | BANNER_SIZE_NARROW
  recommendedSize: PropTypes.string,
}

const mapStateToProps = withSelect( (select, props) => {
  const { getMedia } = select('core');
  const { getEditedPostAttribute } = select('core/editor');
  const bannerImageId = getEditedPostAttribute('meta')[`_banner_image_${props.bannerSize}`];

  return {
    media: bannerImageId ? getMedia( bannerImageId ) : null,
    bannerImageId,
  };
} );

const mapDispatchToProps = withDispatch( (dispatch, props, {select}) => {
  const { editPost } = dispatch('core/editor');
  const { getEditedPostAttribute } = select('core/editor');
  return {
    /**
     * @param image Object
     */
    onUpdateImage: ( image ) => {
      const currentMetas = getEditedPostAttribute('meta');
      editPost( { meta: { ...currentMetas, [`_banner_image_${props.bannerSize}`]: image.id } } );
    },
    onRemoveImage: () => {
      const currentMetas = getEditedPostAttribute('meta');
      editPost({ meta: { ...currentMetas, [`_banner_image_${props.bannerSize}`]: 0 } });
    }
  };
});

export default compose(
  mapStateToProps,
  mapDispatchToProps,
)( FeaturedPostBannerImage );

export const BANNER_SIZE_WIDE = 'wide';
export const BANNER_SIZE_NARROW = 'narrow';