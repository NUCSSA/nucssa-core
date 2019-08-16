import {Component} from '@wordpress/element';
import {compose} from '@wordpress/compose';
import { MediaUpload, MediaUploadCheck, MediaPlaceholder, InnerBlocks } from '@wordpress/editor';
import {
  TextControl,
  TextareaControl,
  withNotices,
} from '@wordpress/components';
import {} from '@wordpress/blocks';

class ClubInfoEdit extends Component {
  constructor(props) {
    super(props);

    this.onUploadError = this.onUploadError.bind(this);
    this.renderThumbnailPicker = this.renderThumbnailPicker.bind(this);

  }

  onUploadError( message ) {
    const { noticeOperations } = this.props;
    noticeOperations.removeAllNotices();
    noticeOperations.createErrorNotice(message);
  }

  renderThumbnailPicker() {
    const {attributes, setAttributes} = this.props;

    const onSelectMedia = media => setAttributes({ thumbnail: { url: media.url, id: media.id } });

    if (!attributes.thumbnail.url) {
      return <MediaPlaceholder
        icon="camera"
        labels={{
          title: "照片",
          instructions: 'Upload or pick an image from the library about your club',
        }}
        onSelect={onSelectMedia}
        accept="image/*"
        allowedTypes={['image']}
        notices={this.props.noticeUI}
        onError={this.onUploadError}
      />;
    } else {
      return (
        <>
          <MediaUploadCheck>
            <MediaUpload
              onSelect={onSelectMedia}
              allowedTypes={['image']}
              value={attributes.thumbnail.id}
              render={ ({open}) => (
                <img src={attributes.thumbnail.url} onClick={open} />
              ) }
            />
          </MediaUploadCheck>
        </>
      );
    }
  }

  render() {

    const {
      attributes,
      className,
      setAttributes,
    } = this.props;

    return (
      <div className={className}>
        <h2>社团简要</h2>
        <div className="thumbnail">
          { this.renderThumbnailPicker() }
        </div>
        <div className="contact">
          <h2>主要联系人</h2>
          <TextControl placeholder="姓名" value={attributes.mainContact.name} onChange={ name => setAttributes({mainContact: {...attributes.mainContact, name}}) } />
          <TextControl placeholder="微信" value={attributes.mainContact.wechat} onChange={ wechat => setAttributes({mainContact: {...attributes.mainContact, wechat}}) } />
          <TextControl placeholder="Email" type="email" value={attributes.mainContact.email} onChange={ email => setAttributes({mainContact: {...attributes.mainContact, email}}) } />
          <TextControl placeholder="电话" type="number" value={attributes.mainContact.phone} onChange={ phone => setAttributes({mainContact: {...attributes.mainContact, phone}}) } />
        </div>
        <hr />
        <div className="workshop-info">
          <h2>Workshops</h2>
          <TextareaControl
            placeholder="活动时间和地点"
            value={attributes.workshopLocationAndTime}
            onChange={workshopLocationAndTime => setAttributes({ workshopLocationAndTime})}
          />
        </div>
      </div>
    );
  }
}



export default compose([
  withNotices,
])(ClubInfoEdit);
