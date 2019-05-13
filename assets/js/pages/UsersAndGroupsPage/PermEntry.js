import React from 'react';
import PropTypes from 'prop-types';
import PermSelect from './PermSelect';


const PermEntry = ({label, value, editable, allRoles, onDelete, onChange}) => {
  const display = value ? allRoles.find((r) => r.slug === value).display : null;
  return (
    <li>
      <span className="name">{label}</span>
      {
        editable ?
          <PermSelect value={value} roles={allRoles} onChange={onChange} /> :
          <span className="perm">{display}</span>
      }
      {
        editable && (
          <div className="actions">
            <button className="remove-entry">
              <i
                className="dashicons dashicons-no-alt"
                onClick={onDelete}
              />
            </button>
          </div>
        )
      }
    </li>
  );
};

PermEntry.propTypes = {
  label: PropTypes.string.isRequired,
  value: PropTypes.string,
  editable: PropTypes.bool,
  allRoles: PropTypes.array,
  onDelete: PropTypes.func,
  onChange: PropTypes.func,
};

PermEntry.defaultProps = {
  editable: false,
  onDelete: null,
  onChange: null,
};

export default PermEntry;
