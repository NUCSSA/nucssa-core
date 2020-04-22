import { Input, Notification, Button } from "rsuite";
export default (props) => {
  const {url, setURL } = props;

  return <>
    <div className="step-1">
      <label>微信文章链接
        <Input value={url} onChange={ url => setURL(url)} />
      </label>
      <div className="steps-action">
        <Button onClick={() => validateWeChatURL(url) && props.next()}>Continue</Button>
      </div>
    </div>
  </>;
};

const validateWeChatURL = (url) => {

  if (!url.includes('mp.weixin.qq')){
    Notification['error']({
      title: 'Error',
      description: <>
          <p>这似乎不是微信公众号的文章</p>
          <p>请修正</p>
        </>,
      style: {marginTop: "2rem", fontWeight: "bold"}
    });

    return false;
  }
  return true;
}
