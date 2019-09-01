import { Component } from '@wordpress/element';
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { Button, TextControl } from '@wordpress/components';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { render } from '@wordpress/element/build/react-platform';

class Coupon extends Component {
  constructor(props) {
    super(props);
    this.state = {
      editingMode: false
    }

    this.renderEditingMode = this.renderEditingMode.bind(this);
    this.renderPreviewMode = this.renderPreviewMode.bind(this);
  }

  renderEditingMode() {
    const {
      attributes, setAttributes,
      businessName, thumbnailId, thumbnailMedia,
      updateBusinessName, updateThumbnail, removeThumbnail
    } = this.props;
    const { style, amount, terms, phone, address } = attributes;

    const amountValidator = /^[\d$%]*$/;
    const phoneValidator = /^[\d()\s\-]*$/;

    console.log('attrs', attributes);

    return (
      <div className="editing">
        <div className="header">
          <i className="dashicons dashicons-tickets-alt"></i>
          Coupon
        </div>

        <div className="content">
          <TextControl type="text" label="商家" value={businessName} onChange={name => updateBusinessName(name)} />
          <TextControl type="text" label="折扣" value={attributes.amount} onChange={amount => amountValidator.test(amount) && setAttributes({ amount })} />
          <TextControl type="text" label="Terms and conditions" value={attributes.terms} onChange={terms => setAttributes({ terms })} />
          <TextControl type="text" label="Phone" value={attributes.phone} onChange={phone => phoneValidator.test(phone) && setAttributes({ phone })} />
          <TextControl type="text" label="Address" value={attributes.address} onChange={address => setAttributes({ address })} />

        </div>
      </div>
    );
  }

  renderPreviewMode() {
    return (
      <div className="preview">
        preview mode
      </div>
    );
  }

  render() {

    return (
      <div className={this.props.className}>
        { this.renderEditingMode() }
      </div>
    );
  }
}

const mapStateToProps = withSelect(select => {
  const { getMedia } = select('core');
  const { getEditedPostAttribute } = select('core/editor');
  const businessName = getEditedPostAttribute('title');
  const thumbnailId = getEditedPostAttribute('featured_media');
  const thumbnailMedia = thumbnailId ? getMedia(thumbnailId) : null;
  return {
    businessName,
    thumbnailId,
    thumbnailMedia, // Media object or null
  };
});
const mapDispatchToProps = withDispatch(dispatch => {
  const { editPost } = dispatch('core/editor');
  return {
    updateBusinessName: businessName => editPost({title: businessName}),
    updateThumbnail: media => editPost({ featured_media: media.id }),
    removeThumbnail: () => editPost({featured_media: 0}),
  };
});

export default compose(
  mapStateToProps,
  mapDispatchToProps
)(Coupon);
