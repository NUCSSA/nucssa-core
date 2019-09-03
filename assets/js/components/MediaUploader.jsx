/**
 * A configurable MediaUpload tool
 */

import { __ } from '@wordpress/i18n';
import { Button, Spinner, ResponsiveWrapper } from '@wordpress/components';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import PropTypes from 'prop-types';
import './MediaUploader.scss';

function MediaUploader({ triggerButtonLabel, mediaId, media, onUpdateMedia, onRemoveMedia, recommendedSize, allowedTypes } ) {
  const instructions = <p>{__('To edit media, you need permission to upload media.')}</p>;

  let mediaSourceUrl;
  if (media) {
    if (media.media_details.sizes['post-thumbnail']) {
      mediaSourceUrl = media.media_details.sizes['post-thumbnail'].source_url;
    } else {
      mediaSourceUrl = media.source_url;
    }
  }
  return (
    <div className="nucssa-media-uploader">
      {/* Set Media */}
      <MediaUploadCheck fallback={ instructions }>
        <MediaUpload
          title={`Media ${recommendedSize || ''}`}
          onSelect={ onUpdateMedia }
          allowedTypes={ allowedTypes }
          render={ ({open}) => (
            <Button
              className={!mediaId ? 'nucssa-media-uploader__toggle' : 'nucssa-media-uploader__preview'}
              onClick={open}
            >
              {
                !! mediaId && media &&
                  <img src={mediaSourceUrl} alt=""/>
              }
              { !! mediaId && !media && <Spinner /> }
              { !mediaId && triggerButtonLabel }
            </Button>
          )}
          value={ mediaId }
        />
      </MediaUploadCheck>
      {
        // Replace Media Button
        !!mediaId && media && !media.isLoading &&
        <MediaUploadCheck>
          <MediaUpload
            title={`Media ${recommendedSize}`}
            onSelect={ onUpdateMedia }
            allowedTypes={allowedTypes}
            render={({ open }) => (
              <Button onClick={open} isDefault isLarge>
                {__('Replace Media')}
              </Button>
            )}
            value={mediaId}
          />
        </MediaUploadCheck>
      }
      {
        // Remove Media Button
        !! mediaId &&
        <MediaUploadCheck>
          <Button onClick={ onRemoveMedia } isLink isDestructive>
            Remove Media
          </Button>
        </MediaUploadCheck>
      }
    </div>
  );
}

MediaUploader.propTypes = {
  allowedTypes: PropTypes.array,
  recommendedSize: PropTypes.string,
  onUpdateMedia: PropTypes.func.isRequired,
  onRemoveMedia: PropTypes.func.isRequired,
  mediaId: PropTypes.number.isRequired,
  media: PropTypes.object.isRequired,
  triggerButtonLabel: PropTypes.oneOf([PropTypes.string, PropTypes.element]),
};
MediaUploader.defaultProps = {
  allowedTypes: ['image'],
  media: null,
  triggerButtonText: 'Select Media',
};

export default MediaUploader;