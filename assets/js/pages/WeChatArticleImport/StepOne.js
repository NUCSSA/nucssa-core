import { Input } from "rsuite";
export default (props) => {
  const {url, setURL } = props;
  return <div className="step-1">
    <label>微信文章链接
      <Input value={url} onChange={ url => setURL(url)} />
    </label>
  </div>;
};
