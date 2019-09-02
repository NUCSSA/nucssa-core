import { Component } from '@wordpress/element';
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { Button, TextControl } from '@wordpress/components';
import MyMediaUpload from '../../../components/MediaUploader';
import { render } from '@wordpress/element/build/react-platform';

class Coupon extends Component {
  constructor(props) {
    super(props);
    this.state = {
      editingMode: false
    }

    this.renderEditingMode = this.renderEditingMode.bind(this);
    this.renderPreviewMode = this.renderPreviewMode.bind(this);
    this.togglePreview = this.togglePreview.bind(this);
  }

  togglePreview() {
    this.setState({editingMode: !this.state.editingMode});
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

    return (
      <div className="editing">
        <div className="header">
          <i className="dashicons dashicons-tickets-alt"></i>
          Coupon
        </div>

        <div className="content">
          <TextControl type="text" label="商家" value={businessName} onChange={name => updateBusinessName(name)} />
          <TextControl type="text" label="折扣" value={this.props.amount} onChange={amount => amountValidator.test(amount) && this.props.updateAmount(amount)} />
          <TextControl type="text" label="Terms and conditions" value={this.props.terms} onChange={terms => this.props.updateTerms( terms )} />
          <TextControl type="text" label="Phone" value={this.props.phone} onChange={phone => phoneValidator.test(phone) && this.props.updatePhone(phone)} />
          <TextControl type="text" label="Address" value={this.props.address} onChange={address => this.props.updateAddress(address)} />
          <MyMediaUpload
            triggerButtonLabel="Logo"
            allowedTypes={['image']}
            mediaId={this.props.thumbnailId}
            media={this.props.thumbnailMedia}
            onRemoveMedia={this.props.removeThumbnail}
            onUpdateMedia={this.props.updateThumbnail}
          />
        </div>

        <div className="footer">
          <Button className="btn-preview" onClick={this.togglePreview}>Preview</Button>
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
  const {amount, terms, phone, address} = getEditedPostAttribute('meta');
  return {
    businessName,
    thumbnailId,
    thumbnailMedia, // Media object or null
    amount, terms, phone, address
  };
});
const mapDispatchToProps = withDispatch((dispatch,_, {select}) => {
  const { editPost } = dispatch('core/editor');
  const { getEditedPostAttribute } = select('core/editor');

  const allMetas = getEditedPostAttribute('meta');
  return {
    updateBusinessName: businessName => editPost({title: businessName}),
    updateThumbnail: media => editPost({ featured_media: media.id }),
    removeThumbnail: () => editPost({featured_media: 0}),
    updateAmount: amount => editPost({meta: {...allMetas, amount}}),
    updateTerms: terms => editPost({meta: {...allMetas, terms}}),
    updatePhone: phone => editPost({meta: {...allMetas, phone}}),
    updateAddress: address => editPost({meta: {...allMetas, address}}),
  };
});

export default compose(
  mapStateToProps,
  mapDispatchToProps
)(Coupon);
