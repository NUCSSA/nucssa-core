export default (props) => {
  const { thumbnail, mainContact: {name, wechat, email, phone}, workshopLocationAndTime} = props.attributes;
  return (
    <div className={props.className}>
      <div className="thumbnail">
        <img src={thumbnail.url} alt="社团简照" />
      </div>

      <div className="info">
        <div className="contact">
          <h2>联系人</h2>
          <p>{name + (wechat ? ` | ${wechat}` : '') + (email ? ` | ${email}` : '') + (phone ? ` | ${phone}` : '') } </p>
        </div>
        <div className="workshop-info">
          <h2>Workshops</h2>
          <p>
            {workshopLocationAndTime}
          </p>
        </div>
      </div>

    </div>
  );
};