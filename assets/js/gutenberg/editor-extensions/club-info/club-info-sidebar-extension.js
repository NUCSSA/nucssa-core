import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { TextControl, SelectControl } from '@wordpress/components';

const ClubInfoExtension = (props) => {
  const {location, days, time} = props.workshop;
  const workshopDaysOptions = [
    {label: 'Monday', value: 'M'},
    {label: 'Tuesday', value: 'T'},
    {label: 'Wednesday', value: 'W'},
    {label: 'Thursday', value: 'Th'},
    {label: 'Friday', value: 'F'},
    {label: 'Saturday', value: 'S'},
    {label: 'Sunday', value: 'Sn'},
  ];
  return (
    <PluginDocumentSettingPanel
      name="workshop-schedule-panel"
      title="Workshop Schedule"
      className="workshop-schedule-panel"
      icon='none'
    >
      <TextControl value={location} label="Workshop地点" onChange={location => props.changeWorkshopSchedule({...props.workshop, location})} />
      <SelectControl
        multiple
        label="Workshop Days"
        value={days}
        options={workshopDaysOptions}
        onChange={ days => props.changeWorkshopSchedule({...props.workshop, days}) }
        help="Hold Command/Control to select multiple values"
      />
      <TextControl value={time} label="Workshop Time" onChange={time => props.changeWorkshopSchedule({...props.workshop, time})} />
    </PluginDocumentSettingPanel>
  );
};

const store = 'core/editor';
const metaKey = '_workshop_schedule';

const mapStateToProps = withSelect(select => {
  let workshopJSON = select(store).getEditedPostAttribute('meta')[metaKey];
  const workshop = workshopJSON ? JSON.parse(workshopJSON) : {location: '', days: '', time: ''};
  return { workshop };
});

const mapDispatchToProps = withDispatch(dispatch => {
  const currentMetas = wp.data.select(store).getEditedPostAttribute('meta');
  const changeWorkshopSchedule = (workshop) => dispatch(store).editPost({
    meta: {
      ...currentMetas,
      [metaKey]: JSON.stringify(workshop)
    }
  });

  return { changeWorkshopSchedule };
});


export default compose(
  mapStateToProps,
  mapDispatchToProps
)(ClubInfoExtension);