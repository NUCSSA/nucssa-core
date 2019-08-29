import { withSelect } from '@wordpress/data';

const ClubInfoEdit = ({className, thumbnail, clubName, workshop, excerpt}) => {

  return (
    <div className={className}>
      <div className="content">
        {/* thumbnail */}
        <div className="thumbnail" style={{ backgroundImage: `url(${thumbnail && thumbnail.source_url})`}}>
          {/* <img src={thumbnail && thumbnail.source_url} alt="club profile" /> */}
        </div>
        <div className="detail">
          {/* workshop schedule */}
          { workshop && <div className="workshop-schedule">{workshop.location} | {workshop.days && workshop.days.join(', ')} | {workshop.time}</div> }
          {/* title */}
          <div className="club-name">{clubName}</div>
          {/* excerpt */}
          <div className="excerpt">{excerpt}</div>
        </div>
      </div>
      <div className="hover-help-message">
        This block is for displaying of information only, to edit the block, fill in <strong>Featured Image</strong>, <strong>Workshop Schedule</strong>, and <strong>Excerpt</strong> in sidebar <strong>Document</strong> Tab.
      </div>
    </div>
  );
};

export default withSelect(select => {
  const clubName = select('core/editor').getEditedPostAttribute('title');
  const thumbnail_id = select('core/editor').getEditedPostAttribute('featured_media');
  const thumbnail = thumbnail_id ? select('core').getMedia(thumbnail_id) : null;
  const workshop_meta_string = select('core/editor').getEditedPostAttribute('meta')['_workshop_schedule'];
  const workshop = workshop_meta_string ? JSON.parse(workshop_meta_string) : null;
  const excerpt = select('core/editor').getEditedPostAttribute('excerpt');
  return {
    thumbnail, // null OR media object
    clubName,
    workshop,
    excerpt,
  };
})(ClubInfoEdit);
