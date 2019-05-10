import React, {Component} from "react";
import PropTypes from "prop-types";

const PermSelect = ({value, roles, onChange}) => {

  return (
    <select
      defaultValue={value}
      onChange={e => onChange(e.currentTarget.value)}
    >
      <option value="null">---</option>
      {roles.map(({ slug, display }) => (
        <option key={slug} value={slug}>
          {display}
        </option>
      ))}
    </select>
  );
}

PermSelect.propTypes = {
  roles: PropTypes.array.isRequired,
  value: PropTypes.string,
  onChange: PropTypes.func.isRequired, // signature: (value) => nil
};

PermSelect.defaultProps = {
  value: 'null',
};

export default PermSelect;