import { Component } from '@wordpress/element';
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { Button, TextControl, Toolbar, IconButton } from '@wordpress/components';
import MyMediaUpload from '../../../components/MediaUploader';
import { BlockControls } from '@wordpress/block-editor';

import { httpizeLink } from '../../../utils/utils';

class Coupon extends Component {
  constructor(props) {
    super(props);
    this.state = {
      editingMode: false
    }

    this.renderEditingMode = this.renderEditingMode.bind(this);
    this.renderPreviewMode = this.renderPreviewMode.bind(this);
    this.togglePreview = this.togglePreview.bind(this);
    this._logoUrl = this._logoUrl.bind(this);
    this.style1ContentHTML = this.style1ContentHTML.bind(this);
    this.style2ContentHTML = this.style2ContentHTML.bind(this);
  }

  componentDidMount() {
    const {businessName, amount, terms, phone, address} = this.props;
    if (!businessName && !amount && !terms && !phone && !address){
      this.setState({editingMode: true});
    }
  }

  componentDidUpdate({className: prevClassName}) {
    const { className, setAttributes } = this.props;
    const styleRegex = /is-style-[-\w\d]+/;
    let matches = className.match(styleRegex);
    const currentStyle = matches ? matches[0] : null;
    matches = prevClassName.match(styleRegex);
    const prevStyle = matches ? matches[0] : null;

    if (currentStyle !== prevStyle) {
      this.props.updateStyle(currentStyle);
    }
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
      <div className="coupon-editing">
        <div className="header">
          <i className="dashicons dashicons-tickets-alt"></i>
          Coupon
        </div>

        <div className="content">
          <TextControl type="text" label="商家" value={businessName} onChange={name => updateBusinessName(name)} />
          <TextControl type="text" label="折扣" value={this.props.amount} onChange={amount => amountValidator.test(amount) && this.props.updateAmount(amount)} />
          <TextControl type="text" label="Terms and conditions" value={this.props.terms} onChange={terms => this.props.updateTerms( terms )} />
          <TextControl type="text" label="Phone" value={this.props.phone} onChange={phone => phoneValidator.test(phone) && this.props.updatePhone(phone)} />
          <TextControl type="text" label="Website" value={this.props.website} onChange={website => this.props.updateWebsite(website)} />
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
          <Button className="btn-preview" isLarge isPrimary onClick={this.togglePreview}>Preview</Button>
        </div>
      </div>
    );
  }

  style1ContentHTML() {
    const { className, businessName, amount, terms, phone, website, address, thumbnailMedia } = this.props;

    return (
      <div className="card-container">
        <div className="upper">
          <div className="business-name">{businessName}</div>
          <div className="coupon-literal">coupon</div>
        </div>
        <div className="lower">
          {thumbnailMedia && <img src={this._logoUrl()} alt="商家logo" />}
          <div className="details">
            <div className="terms">
              <div className="header">terms and conditions</div>
              <div className="content">{terms}</div>
            </div>
            <div className="contact">
              <table>
                {phone && <tr><th className="label">phone:</th><td className="content">{phone}</td></tr>}
                {website && <tr><th className="label">website:</th><td className="content"><a href={httpizeLink(website)}>{website}</a></td></tr>}
                {address && <tr><th className="label">address:</th><td className="content">{address}</td></tr>}
              </table>
            </div>
          </div>
        </div>
        <div className="coupon-amount-container">
          <div className="outline"></div>
          <div className="coupon-amount">{amount}</div>
          <div className="off-literal">off</div>
        </div>
      </div>
    );
  }

  style2ContentHTML() {
    const { className, businessName, amount, terms, phone, website, address, thumbnailMedia } = this.props;

    return (
      <div className="card-container">
        <div className="left">
          <div className="phone">{phone}</div>
          <div className="coupon-amount">{amount}</div>
          <div className="off-literal">off</div>
          <div className="business-name">{businessName}</div>
          <div className="terms-header">terms and conditions</div>
          <div className="terms-content">{terms}</div>
          {website && <div className="website"><a href={httpizeLink(website)}>{website}</a></div>}
          <div className="address">{address}</div>
        </div>
        <div className="right">
          {thumbnailMedia && <div className="logo" style={{ background: `url(${this._logoUrl()})` }}></div>}
        </div>
      </div>
    );
  }

  _logoUrl(){
    const { thumbnailMedia } = this.props;
    let logo_url;
    if (thumbnailMedia) {
      if (thumbnailMedia.media_details.sizes['post-thumbnail']) {
        logo_url = thumbnailMedia.media_details.sizes['post-thumbnail'].source_url;
      } else {
        logo_url = thumbnailMedia.source_url;
      }
    }

    return logo_url;
  }

  renderPreviewMode() {
    const { className, businessName, amount, terms, phone, website, address, thumbnailMedia } = this.props;

    let contentHTML;
    if (className.includes('is-style-style-1')) {
      contentHTML = this.style1ContentHTML();
    }
    if (className.includes('is-style-style-2')) {
      contentHTML = this.style2ContentHTML();
    }
    // Default Style
    if (!contentHTML) {
      contentHTML = this.style1ContentHTML();
    }


    return (
      <>
        <BlockControls>
          <Toolbar>
            <IconButton label="Edit" icon="edit" onClick={this.togglePreview} />
          </Toolbar>
        </BlockControls>
        <div className="coupon-preview">
          { contentHTML }
        </div>
      </>
    );
  }

  render() {

    return (
      <div className={this.props.className}>
        { this.state.editingMode ? this.renderEditingMode() : this.renderPreviewMode() }
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
  const {style, amount, terms, phone, address, website } = getEditedPostAttribute('meta');
  return {
    businessName,
    thumbnailId,
    thumbnailMedia, // Media object or null
    style, amount, terms, phone, address, website
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
    updateWebsite: website => editPost({meta: {...allMetas, website}}),
    updateAddress: address => editPost({meta: {...allMetas, address}}),
    updateStyle: style => editPost({meta: {...allMetas, style}}),
  };
});

export default compose(
  mapStateToProps,
  mapDispatchToProps
)(Coupon);
