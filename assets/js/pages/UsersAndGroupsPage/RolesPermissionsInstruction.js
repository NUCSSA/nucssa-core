const RolesPermissionsInstruction = () => {
  const check = <i className='dashicons dashicons-yes'></i>;
  const uncheck = <i className='dashicons dashicons-no-alt'></i>;
  /* eslint-disable */
  return (
    <div className="section-container">
      <div className="section-title" style={{textAlign: 'center'}}>About Roles and Permissions</div>
      <div className="instructions">
        <table>
          <thead>
            <tr className='heading'>
              <th></th>
              <th colSpan="4">Posts</th>
              <th colSpan="4">Pages</th>
              <th colSpan="4">Newsletters</th>
              <th colSpan="3">System Admin</th>
            </tr>
            <tr className='subheading'>
              <th></th>
              <th>Add</th><th>Edit Own</th><th>Edit Others</th><th>Publish</th>
              <th>Add</th><th>Edit Own</th><th>Edit Others</th><th>Publish</th>
              <th>Add</th><th>Edit Own</th><th>Edit Others</th><th>Send</th>
              <th>Manage Plugins</th><th>Manage Users</th><th>Manage Themes</th><th></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <th>Administrators</th>
              <td>{check}</td><td>{check}</td><td>{check}</td><td>{check}</td>
              <td>{check}</td><td>{check}</td><td>{check}</td><td>{check}</td>
              <td>{check}</td><td>{check}</td><td>{check}</td><td>{check}</td>
              <td>{check}</td><td>{check}</td><td>{check}</td>
            </tr>
            <tr>
              <th>Editors</th>
              <td>{check}</td><td>{check}</td><td>{check}</td><td>{check}</td>
              <td>{check}</td><td>{check}</td><td>{check}</td><td>{check}</td>
              <td>{check}</td><td>{check}</td><td>{check}</td><td>{check}</td>
              <td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td>
            </tr>
            <tr>
              <th>Authors</th>
              <td>{check}</td><td>{check}</td><td>{uncheck}</td><td>{uncheck}</td>
              <td>{check}</td><td>{check}</td><td>{uncheck}</td><td>{uncheck}</td>
              <td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td>
              <td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td>
            </tr>
            <tr>
              <th>Subscribers</th>
              <td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td>
              <td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td>
              <td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td>
              <td>{uncheck}</td><td>{uncheck}</td><td>{uncheck}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  );
  /* eslint-enable */
};

export default RolesPermissionsInstruction;